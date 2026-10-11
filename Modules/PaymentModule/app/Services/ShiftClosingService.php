<?php

namespace Modules\PaymentModule\app\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\AccountModule\app\Models\Account;
use Modules\EmployeeModule\app\Models\Employee;
use Modules\PaymentModule\app\Models\Payment;
use Modules\PaymentModule\app\Models\PaymentMethod;
use Modules\PaymentModule\app\Models\ShiftClosing;
use Modules\PaymentModule\app\Models\ShiftEvent;
use Modules\UserModule\app\Models\User;

/**
 * Employee shift closing (docs/payments-plan.md §2b): the payments are linked to a closing (payments.shift_closing_id),
 * not taken by a time window (paid_at is a date): an employee's OPEN PERIOD = their payments with shift_closing_id 0.
 * - the employee closes it at any time (a shift over midnight is one closing); the account can close it on their behalf
 * - a closed shift locks its payments (PaymentService::assertPaymentOpen()); only the account reopens it (reason),
 *   its payments stay in it, and the account closes it again (totals recalculated)
 * - the account's own payments are not in a shift: the final day closing locks them
 * - every close / reopen / re-close and the reminder answers are logged (shift_events)
 * - closing sends the highest payment id the employee saw: a payment added meanwhile → refused (review again)
 */
class ShiftClosingService
{
    const IMPORTED_NOTE = 'Imported from the old system';

    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    // an employee login (their payments go into shifts); the account's own payments don't
    public function isCollector(?Model $user): bool
    {
        return $user?->userable instanceof Employee;
    }

    // the users.id of the account's employees (a subquery)
    public function employeeUserIds($accountId)
    {
        return User::where('userable_type', (new Employee)->getMorphClass())
            ->whereIn('userable_id', Employee::withTrashed()->where('account_id', $accountId)->select('id'))
            ->select('id');
    }

    // the payments of an employee login (not deleted)
    private function paymentsOf($accountId, $userId): Builder
    {
        return Payment::where('account_id', $accountId)
            ->where('creatable_type', (new User)->getMorphClass())->where('creatable_id', $userId);
    }

    // the employee's open period: their payments not in a closing yet
    public function openPayments($accountId, $userId): Builder
    {
        return $this->paymentsOf($accountId, $userId)->where('shift_closing_id', 0);
    }

    /**
     * The employee's open period: ['payments' (oldest first), 'summary', 'since' (created_at of the oldest payment, null = none),
     * 'last_closing' (their last closing, null = never)]
     */
    public function openPeriod($accountId, $userId): array
    {
        $payments = $this->openPayments($accountId, $userId)
            ->with('payable', 'member', 'paymentMethod', 'incomeCategory')->orderBy('created_at')->orderBy('id')->get();

        return [
            'payments' => $payments,
            'summary' => $this->summary($accountId, $payments),
            'since' => $payments->first()?->created_at,
            'last_closing' => ShiftClosing::where('account_id', $accountId)->where('user_id', $userId)->latest('closed_at')->first(),
        ];
    }

    /**
     * The totals of some payments (an open period / a closing; saved on the closing):
     * ['methods' => [method_id => ['name', 'payments', 'refunds', 'net']], 'withholding' => ['count', 'total'],
     *  'cash_total' (payments - refunds, withholding apart), 'count', 'max_id', 'first_payment_at', 'last_payment_at']
     */
    public function summary($accountId, Collection $payments): array
    {
        $methodNames = PaymentMethod::withTrashed()->where('account_id', $accountId)->get()->pluck('name', 'id');
        $summary = ['methods' => [], 'withholding' => ['count' => 0, 'total' => 0.0], 'cash_total' => 0.0, 'count' => 0,
            'max_id' => (int) $payments->max('id'),
            'first_payment_at' => $payments->min('created_at')?->format('Y-m-d H:i:s'),
            'last_payment_at' => $payments->max('created_at')?->format('Y-m-d H:i:s')];

        foreach ($payments as $payment) {
            $summary['count']++;
            if ($payment->type === 'withholding') {
                $summary['withholding']['count']++;
                $summary['withholding']['total'] = round($summary['withholding']['total'] + $payment->amount, 2);
                continue;
            }

            $methodId = (int) $payment->payment_method_id;
            $summary['methods'][$methodId] ??= ['name' => $methodNames[$methodId] ?? 'Method #' . $methodId, 'payments' => 0.0, 'refunds' => 0.0, 'net' => 0.0];
            $method = &$summary['methods'][$methodId];
            $key = $payment->type === 'refund' ? 'refunds' : 'payments';
            $method[$key] = round($method[$key] + $payment->amount, 2);
            $method['net'] = round($method['net'] + $payment->signed_amount, 2);
            $summary['cash_total'] = round($summary['cash_total'] + $payment->signed_amount, 2);
            unset($method);
        }

        return $summary;
    }

    /**
     * Close an employee's open period. $upToId = the highest payment id shown in the confirmation (a newer one = refused).
     * $by: the employee (close / logout_closed) or the account (close_on_behalf).
     */
    public function close($accountId, User $employee, int $upToId, Model $by, string $action = 'close'): ShiftClosing
    {
        $this->assertEmployeeOf($accountId, $employee);
        $onBehalf = $by->getKey() !== $employee->getKey();
        if ($onBehalf && !$this->paymentService->isOwner($by)) {
            abort(403, 'Only the account can close another employee\'s shift.');
        }
        $action = $onBehalf ? 'close_on_behalf' : (in_array($action, ['close', 'logout_closed'], true) ? $action : 'close');

        return DB::transaction(function () use ($accountId, $employee, $upToId, $by, $action) {
            // locked until the end: a payment can't join / leave the period while it is closed
            $payments = $this->openPayments($accountId, $employee->getKey())->lockForUpdate()->get();
            if ($payments->isEmpty()) {
                throw ValidationException::withMessages(['shift' => 'There are no payments to close.']);
            }
            if ((int) $payments->max('id') !== $upToId) {
                throw ValidationException::withMessages(['shift' => 'Payments were added or changed since you opened this window. Review the totals again.']);
            }

            $closing = $this->saveTotals(new ShiftClosing([
                'account_id' => $accountId,
                'user_id' => $employee->getKey(),
                'status' => 'closed',
                'closed_by' => $by->getKey(),
                'closed_at' => now(),
            ]), $accountId, $payments);
            Payment::whereKey($payments->modelKeys())->update(['shift_closing_id' => $closing->id]);
            $this->log($closing, $action, $by);

            return $closing;
        });
    }

    // the account opens a closed shift again (its payments can be changed); they stay in it
    public function reopen($accountId, $id, string $reason, Model $by): ShiftClosing
    {
        $this->assertOwner($by);
        $closing = $this->findOne($accountId, $id);
        if ($closing->status !== 'closed') {
            throw ValidationException::withMessages(['reopen_reason' => 'This shift is not closed.']);
        }
        if (trim($reason) === '') {
            throw ValidationException::withMessages(['reopen_reason' => 'Write why the shift is opened again.']);
        }

        return DB::transaction(function () use ($closing, $reason, $by) {
            $closing->update(['status' => 'reopened', 'reopened_by' => $by->getKey(), 'reopened_at' => now(), 'reopen_reason' => trim($reason)]);
            $this->log($closing, 'reopen', $by, trim($reason));

            return $closing;
        });
    }

    // the account closes a reopened shift again: its totals from its payments now
    public function reclose($accountId, $id, Model $by): ShiftClosing
    {
        $this->assertOwner($by);
        $closing = $this->findOne($accountId, $id);
        if ($closing->status !== 'reopened') {
            throw ValidationException::withMessages(['shift' => 'This shift is not reopened.']);
        }

        return DB::transaction(function () use ($accountId, $closing, $by) {
            $payments = $closing->payments()->lockForUpdate()->get();
            $closing->fill(['status' => 'closed', 'closed_by' => $by->getKey(), 'closed_at' => now()]);
            $this->saveTotals($closing, $accountId, $payments);
            $this->log($closing, 'reclose', $by);

            return $closing;
        });
    }

    // a reminder answer of the employee: still_working (reason required) | logout_still_working
    public function stillWorking($accountId, User $employee, string $action, ?string $reason = null): ShiftEvent
    {
        $this->assertEmployeeOf($accountId, $employee);
        if (!in_array($action, ['still_working', 'logout_still_working'], true)) {
            abort(404);
        }
        if ($action === 'still_working' && trim((string) $reason) === '') {
            throw ValidationException::withMessages(['reason' => 'Write why you are still working (e.g. covering a colleague\'s shift).']);
        }

        return ShiftEvent::create([
            'account_id' => $accountId,
            'user_id' => $employee->getKey(),
            'shift_closing_id' => 0,
            'by_user_id' => $employee->getKey(),
            'action' => $action,
            'reason' => trim((string) $reason) ?: null,
        ]);
    }

    /**
     * The "close your shift" popup is due: the oldest unclosed payment is older than the account's shift_reminder_hours,
     * and no "still working" answer in the last shift_snooze_hours. No payments → never.
     */
    public function reminderDue(Account $account, User $employee): bool
    {
        $since = $this->openPayments($account->id, $employee->getKey())->min('created_at');
        if (!$since || Carbon::parse($since)->gt(now()->subHours($account->shift_reminder_hours ?: 12))) {
            return false;
        }

        return !ShiftEvent::where('account_id', $account->id)->where('user_id', $employee->getKey())
            ->where('action', 'still_working')->where('created_at', '>', now()->subHours($account->shift_snooze_hours ?: 4))->exists();
    }

    /**
     * The open periods of the account's employees (all, or the ones with payments dated $date):
     * [user_id => ['user', 'since', 'count', 'cash_total', 'withholding_total', 'dated_count' (payments of $date)]], oldest first
     */
    public function openPeriods($accountId, $date = null): Collection
    {
        $rows = Payment::where('account_id', $accountId)->where('shift_closing_id', 0)
            ->where('creatable_type', (new User)->getMorphClass())->whereIn('creatable_id', $this->employeeUserIds($accountId))
            ->groupBy('creatable_id')
            ->selectRaw("creatable_id, MIN(created_at) since, COUNT(*) n,
                SUM(CASE type WHEN 'payment' THEN amount WHEN 'refund' THEN -amount ELSE 0 END) cash,
                SUM(IF(type = 'withholding', amount, 0)) wh, SUM(paid_at = ?) dated", [$date ? Carbon::parse($date)->toDateString() : null])
            ->when($date, fn ($query) => $query->havingRaw('dated > 0'))
            ->orderBy('since')
            ->get();
        $users = User::with('userable')->whereIn('id', $rows->pluck('creatable_id'))->get()->keyBy('id');

        return $rows->toBase()->mapWithKeys(fn ($row) => [(int) $row->creatable_id => [
            'user' => $users[$row->creatable_id] ?? null,
            'since' => Carbon::parse($row->since),
            'count' => (int) $row->n,
            'cash_total' => round((float) $row->cash, 2),
            'withholding_total' => round((float) $row->wh, 2),
            'dated_count' => (int) $row->dated,
        ]]);
    }

    /**
     * The account's closing screen flags: open periods older than shift_reminder_hours (oldest first), each with
     * 'answers' = the "still working" answers since it opened (popup + logout, newest first, with their reasons).
     */
    public function longOpenPeriods(Account $account): Collection
    {
        $limit = now()->subHours($account->shift_reminder_hours ?: 12);
        $periods = $this->openPeriods($account->id)->filter(fn ($period) => $period['since']->lt($limit));
        $answers = ShiftEvent::where('account_id', $account->id)->where('shift_closing_id', 0)
            ->whereIn('action', ['still_working', 'logout_still_working'])->whereIn('user_id', $periods->keys())
            ->latest('id')->get()->groupBy('user_id');

        return $periods->map(fn ($period, $userId) => $period + [
            'answers' => ($answers[$userId] ?? collect())->filter(fn ($event) => $event->created_at->gte($period['since']))->values(),
        ]);
    }

    // the closings the account reopened and did not close again yet
    public function reopenedClosings($accountId): Collection
    {
        return $this->listQuery($accountId)->where('status', 'reopened')->orderBy('reopened_at')->get();
    }

    /**
     * Why a day can't be closed yet: employees' payments dated that day still open (open period / a reopened shift).
     * [] = nothing blocks. ['open' => openPeriods($date), 'reopened' => the reopened closings with payments of that day]
     */
    public function blockingForDate($accountId, $date): array
    {
        $date = Carbon::parse($date)->toDateString();
        $open = $this->openPeriods($accountId, $date);
        $reopened = ShiftClosing::with('user.userable')->where('account_id', $accountId)->where('status', 'reopened')
            ->whereHas('payments', fn ($query) => $query->whereDate('paid_at', $date))->get();

        return $open->isEmpty() && $reopened->isEmpty() ? [] : ['open' => $open, 'reopened' => $reopened];
    }

    // the closings list (paged / ordered by DataTables); $userId: one employee's closings
    public function listQuery($accountId, $userId = null)
    {
        return ShiftClosing::with('user.userable', 'closedBy.userable')->where('account_id', $accountId)
            ->when($userId, fn ($query) => $query->where('user_id', $userId));
    }

    // 404 when it belongs to another account (or, with $userId, to another employee)
    public function findOne($accountId, $id, $userId = null): ShiftClosing
    {
        return $this->listQuery($accountId, $userId)->with('events.byUser.userable', 'reopenedBy.userable')->findOrFail($id);
    }

    // the closings with payments dated $date (the account's day screen)
    public function closingsForDate($accountId, $date): Collection
    {
        $date = Carbon::parse($date)->toDateString();
        return $this->listQuery($accountId)->whereHas('payments', fn ($query) => $query->whereDate('paid_at', $date))
            ->orderBy('closed_at')->get();
    }

    // the totals of a closing from its payments, saved
    private function saveTotals(ShiftClosing $closing, $accountId, Collection $payments): ShiftClosing
    {
        $summary = $this->summary($accountId, $payments);
        $closing->fill([
            'first_payment_at' => $summary['first_payment_at'],
            'last_payment_at' => $summary['last_payment_at'],
            'count' => $summary['count'],
            'cash_total' => $summary['cash_total'],
            'withholding_total' => $summary['withholding']['total'],
            'totals' => $summary,
        ])->save();

        return $closing;
    }

    private function log(ShiftClosing $closing, string $action, Model $by, ?string $reason = null): void
    {
        ShiftEvent::create([
            'account_id' => $closing->account_id,
            'user_id' => $closing->user_id,
            'shift_closing_id' => $closing->id,
            'by_user_id' => $by->getKey(),
            'action' => $action,
            'reason' => $reason,
            'totals' => $closing->totals,
        ]);
    }

    private function assertOwner(Model $user): void
    {
        if (!$this->paymentService->isOwner($user)) {
            abort(403, 'Only the account can reopen or close a shift again.');
        }
    }

    // an employee login of this account (404 otherwise)
    private function assertEmployeeOf($accountId, User $user): void
    {
        $employee = $user->userable;
        if (!$employee instanceof Employee || (int) $employee->account_id !== (int) $accountId) {
            abort(404);
        }
    }
}
