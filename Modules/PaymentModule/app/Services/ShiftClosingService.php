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
 * Shift closing, the ONE closing level (docs/payments-plan.md §2b, simplified 2026-10-13): the payments are linked to a closing
 * (payments.shift_closing_id), not taken by a time window (paid_at is a date): a login's OPEN SHIFT = their payments with
 * shift_closing_id 0. Every login collects into its own shift: the employees AND the account itself (no reminders for the account).
 * - the collector closes it at any time (a shift over midnight is one closing); the account can close an employee's on their behalf
 * - a closed shift keeps its payments; only the account edits / deletes one of them (reason required): paymentChanged()
 *   recalculates the totals, logs the change (before / after) and marks the shift "Edited after closing"; a refund of one
 *   of them goes into the refunder's open shift, the closed shift only gets the log entry + the mark
 * - every close, payment change and reminder answer is logged (shift_events)
 * - closing sends the highest payment id the collector saw: a payment added meanwhile → refused (review again)
 * - reminders (employees only): config paymentmodule.shift_reminder_hours / shift_snooze_hours
 */
class ShiftClosingService
{
    const IMPORTED_NOTE = 'Imported from the old system';

    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    // an employee or an account login: both collect into their own shift
    public function isCollector(?Model $user): bool
    {
        return $user?->userable instanceof Employee || $user?->userable instanceof Account;
    }

    // an employee login (the reminders, still working answers and logout popup are for employees only)
    public function isEmployee(?Model $user): bool
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

    // the users.id of every login that collects for the account: its employees + the account itself (a subquery)
    public function collectorUserIds($accountId)
    {
        return User::where(fn ($query) => $query
            ->where(fn ($query) => $query->where('userable_type', (new Employee)->getMorphClass())
                ->whereIn('userable_id', Employee::withTrashed()->where('account_id', $accountId)->select('id')))
            ->orWhere(fn ($query) => $query->where('userable_type', (new Account)->getMorphClass())->where('userable_id', $accountId)))
            ->select('id');
    }

    // the payments of a login (not deleted)
    private function paymentsOf($accountId, $userId): Builder
    {
        return Payment::where('account_id', $accountId)
            ->where('creatable_type', (new User)->getMorphClass())->where('creatable_id', $userId);
    }

    // the collector's open shift: their payments not in a closing yet
    public function openPayments($accountId, $userId): Builder
    {
        return $this->paymentsOf($accountId, $userId)->where('shift_closing_id', 0);
    }

    /**
     * The collector's open shift: ['payments' (oldest first), 'summary', 'since' (created_at of the oldest payment, null = none),
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
     * The totals of some payments (an open shift / a closing; saved on the closing):
     * ['methods' => [method_id => ['name', 'payments', 'refunds', 'net']], 'refunds' => ['count', 'total'] (all methods, its own
     *  minus line in the close popup), 'withholding' => ['count', 'total'],
     *  'cash_total' (payments - refunds, withholding apart), 'count', 'max_id', 'first_payment_at', 'last_payment_at']
     */
    public function summary($accountId, Collection $payments): array
    {
        $methodNames = PaymentMethod::withTrashed()->where('account_id', $accountId)->get()->pluck('name', 'id');
        $summary = ['methods' => [], 'refunds' => ['count' => 0, 'total' => 0.0], 'withholding' => ['count' => 0, 'total' => 0.0], 'cash_total' => 0.0, 'count' => 0,
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
            if ($payment->type === 'refund') {
                $summary['refunds']['count']++;
                $summary['refunds']['total'] = round($summary['refunds']['total'] + $payment->amount, 2);
            }
            $summary['cash_total'] = round($summary['cash_total'] + $payment->signed_amount, 2);
            unset($method);
        }

        return $summary;
    }

    /**
     * Close a collector's open shift. $upToId = the highest payment id shown in the confirmation (a newer one = refused).
     * $collector: an employee or the account's own login; $by: the collector (close / logout_closed) or the account (close_on_behalf).
     */
    public function close($accountId, User $collector, int $upToId, Model $by, string $action = 'close'): ShiftClosing
    {
        $this->assertCollectorOf($accountId, $collector);
        $onBehalf = $by->getKey() !== $collector->getKey();
        if ($onBehalf && !$this->paymentService->isOwner($by)) {
            abort(403, 'Only the account can close another employee\'s shift.');
        }
        $action = $onBehalf ? 'close_on_behalf' : (in_array($action, ['close', 'logout_closed'], true) ? $action : 'close');

        return DB::transaction(function () use ($accountId, $collector, $upToId, $by, $action) {
            // locked until the end: a payment can't join / leave the period while it is closed
            $payments = $this->openPayments($accountId, $collector->getKey())->lockForUpdate()->get();
            if ($payments->isEmpty()) {
                throw ValidationException::withMessages(['shift' => 'There are no payments to close.']);
            }
            if ((int) $payments->max('id') !== $upToId) {
                throw ValidationException::withMessages(['shift' => 'Payments were added or changed since you opened this window. Review the totals again.']);
            }

            $closing = $this->saveTotals(new ShiftClosing([
                'account_id' => $accountId,
                'user_id' => $collector->getKey(),
                'closed_by' => $by->getKey(),
                'closed_at' => now(),
            ]), $accountId, $payments);
            Payment::whereKey($payments->modelKeys())->update(['shift_closing_id' => $closing->id]);
            $this->log($closing, $action, $by);

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
     * The "close your shift" popup is due (employees only): the oldest unclosed payment is older than
     * config paymentmodule.shift_reminder_hours, and no "still working" answer in the last shift_snooze_hours. No payments → never.
     */
    public function reminderDue(Account $account, User $employee): bool
    {
        $since = $this->openPayments($account->id, $employee->getKey())->min('created_at');
        if (!$since || Carbon::parse($since)->gt(now()->subHours(self::reminderHours()))) {
            return false;
        }

        return !ShiftEvent::where('account_id', $account->id)->where('user_id', $employee->getKey())
            ->where('action', 'still_working')->where('created_at', '>', now()->subHours(self::snoozeHours()))->exists();
    }

    // the reminder hours (config): after this the "close your shift" popup; then again every snoozeHours() after "still working"
    public static function reminderHours(): int
    {
        return (int) config('paymentmodule.shift_reminder_hours', 12) ?: 12;
    }

    public static function snoozeHours(): int
    {
        return (int) config('paymentmodule.shift_snooze_hours', 4) ?: 4;
    }

    /**
     * The open shifts of the account's collectors, the account included (all, or the ones with payments dated $date):
     * [user_id => ['user', 'since', 'count', 'cash_total', 'withholding_total', 'dated_count' (payments of $date)]], oldest first
     */
    public function openPeriods($accountId, $date = null): Collection
    {
        $rows = Payment::where('account_id', $accountId)->where('shift_closing_id', 0)
            ->where('creatable_type', (new User)->getMorphClass())->whereIn('creatable_id', $this->collectorUserIds($accountId))
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
     * The Daily Summary flags: employees' open shifts older than reminderHours() (oldest first; the account's own has no
     * reminders), each with 'answers' = the "still working" answers since it opened (popup + logout, newest first, with their reasons).
     */
    public function longOpenPeriods(Account $account): Collection
    {
        $limit = now()->subHours(self::reminderHours());
        $periods = $this->openPeriods($account->id)
            ->filter(fn ($period) => $period['since']->lt($limit) && $this->isEmployee($period['user']));
        $answers = ShiftEvent::where('account_id', $account->id)->where('shift_closing_id', 0)
            ->whereIn('action', ['still_working', 'logout_still_working'])->whereIn('user_id', $periods->keys())
            ->latest('id')->get()->groupBy('user_id');

        return $periods->map(fn ($period, $userId) => $period + [
            'answers' => ($answers[$userId] ?? collect())->filter(fn ($event) => $event->created_at->gte($period['since']))->values(),
        ]);
    }

    // the closings list (paged / ordered by DataTables); $userId: one collector's closings
    public function listQuery($accountId, $userId = null)
    {
        return ShiftClosing::with('user.userable', 'closedBy.userable')->where('account_id', $accountId)
            ->when($userId, fn ($query) => $query->where('user_id', $userId));
    }

    // 404 when it belongs to another account (or, with $userId, to another employee)
    public function findOne($accountId, $id, $userId = null): ShiftClosing
    {
        return $this->listQuery($accountId, $userId)->findOrFail($id);
    }

    // the shift popup's payments (Payments list cells): the newest POPUP_LIMIT first (an imported shift holds thousands)
    const POPUP_LIMIT = 200;

    public function popupPayments(ShiftClosing $closing): Collection
    {
        return $closing->payments()->with('member', 'paymentMethod', 'incomeCategory')
            ->with(['payable' => fn ($morph) => $morph->morphWith([\Modules\ReservationModule\app\Models\Reservation::class => ['space', 'unit.color', 'plan']])])
            ->latest('created_at')->latest('id')->limit(self::POPUP_LIMIT)->get();
    }

    // the closings with payments dated $date (the Daily Summary)
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

    /**
     * A payment of a CLOSED shift was changed by the account (PaymentService update / delete), or refunded by anyone (refund):
     * payment_edit / payment_delete → the totals recalculated from its payments now; payment_refund → totals unchanged (the refund
     * row is in the refunder's open shift). Always: the log entry (before / after, who, reason) + "Edited after closing".
     * Call inside the change's transaction.
     */
    public function paymentChanged(ShiftClosing $closing, string $action, Payment $payment, Model $by, string $reason, array $changes): void
    {
        if (!in_array($action, ['payment_edit', 'payment_delete', 'payment_refund'], true)) {
            abort(500, 'Unknown shift change.');
        }
        if ($action !== 'payment_refund') {
            $this->saveTotals($closing, $closing->account_id, $closing->payments()->lockForUpdate()->get());
        }
        $closing->forceFill(['edited_at' => now(), 'edit_count' => $closing->edit_count + 1])->save();
        $this->log($closing, $action, $by, $reason, $payment->getKey(), $changes);
    }

    private function log(ShiftClosing $closing, string $action, Model $by, ?string $reason = null, int $paymentId = 0, ?array $changes = null): void
    {
        ShiftEvent::create([
            'account_id' => $closing->account_id,
            'user_id' => $closing->user_id,
            'shift_closing_id' => $closing->id,
            'payment_id' => $paymentId,
            'by_user_id' => $by->getKey(),
            'action' => $action,
            'reason' => $reason,
            'totals' => $closing->totals,
            'changes' => $changes,
        ]);
    }

    // a login that collects for this account: one of its employees, or the account itself (404 otherwise)
    private function assertCollectorOf($accountId, User $user): void
    {
        if ($user->userable instanceof Account && (int) $user->userable->getKey() === (int) $accountId) {
            return;
        }
        $this->assertEmployeeOf($accountId, $user);
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
