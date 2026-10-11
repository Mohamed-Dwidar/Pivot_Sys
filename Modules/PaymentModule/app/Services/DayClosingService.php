<?php

namespace Modules\PaymentModule\app\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\PaymentModule\app\Models\DayClosing;
use Modules\PaymentModule\app\Models\DayClosingLog;
use Modules\PaymentModule\app\Models\Payment;
use Modules\PaymentModule\app\Models\PaymentMethod;
use Modules\ReservationModule\app\Models\Reservation;
use Modules\UserModule\app\Models\User;

/**
 * Day closing of an account: one row per account per day; a closed day locks its payments (PaymentService::assertDayOpen()).
 * The totals: by the user who added the payment (cash handover) × payment method: payments - refunds = net;
 * withholding is not cash: its own line, not in the cash total.
 * The account and its employees close a day; only the owner reopens it, with a reason.
 * Two levels (docs/payments-plan.md §2b): a day can't be closed while an employee's payment dated that day is still open
 * (their open period or a reopened shift, ShiftClosingService::blockingForDate()); the account's own payments are locked by it.
 * Every close / reopen is logged (day_closing_logs: who, why, the totals); the row keeps the current status + the last reopen.
 */
class DayClosingService
{
    private PaymentService $paymentService;
    private ShiftClosingService $shiftClosingService;

    public function __construct(PaymentService $paymentService, ShiftClosingService $shiftClosingService)
    {
        $this->paymentService = $paymentService;
        $this->shiftClosingService = $shiftClosingService;
    }

    // the closings list (paged / ordered by DataTables)
    public function listQuery($accountId)
    {
        return DayClosing::with('closedBy.userable', 'reopenedBy.userable')->where('account_id', $accountId);
    }

    // 404 when it belongs to another account
    public function findOne($accountId, $id): DayClosing
    {
        return $this->listQuery($accountId)->with('logs.user.userable')->findOrFail($id);
    }

    // the closing of a day (null = never closed)
    public function forDate($accountId, $date): ?DayClosing
    {
        return $this->listQuery($accountId)->whereDate('date', Carbon::parse($date))->first();
    }

    /**
     * The totals of a day from its payments now (the close page; saved on the closing):
     * ['employees' => [user_id => ['name', 'methods' => [method_id => ['payments', 'refunds', 'net']], 'net']],
     *  'methods' => [method_id => ['name', 'net']], 'withholding' => ['count', 'total'], 'cash_total', 'count']
     */
    public function summary($accountId, $date): array
    {
        $rows = Payment::where('account_id', $accountId)->whereDate('paid_at', Carbon::parse($date))
            ->groupBy('creatable_type', 'creatable_id', 'payment_method_id', 'type')
            ->selectRaw('creatable_type, creatable_id, payment_method_id, type, COUNT(*) n, SUM(amount) total')
            ->get();

        $methodNames = PaymentMethod::withTrashed()->where('account_id', $accountId)->get()->pluck('name', 'id');
        $users = User::with('userable')->whereIn('id', $rows->pluck('creatable_id')->unique())->get()->keyBy('id');
        $summary = ['employees' => [], 'methods' => [], 'withholding' => ['count' => 0, 'total' => 0.0], 'cash_total' => 0.0, 'count' => 0];

        foreach ($rows as $row) {
            $summary['count'] += $row->n;
            if ($row->type === 'withholding') {
                $summary['withholding']['count'] += $row->n;
                $summary['withholding']['total'] = round($summary['withholding']['total'] + $row->total, 2);
                continue;
            }

            $userId = (int) $row->creatable_id;
            $methodId = (int) $row->payment_method_id;
            $user = $users[$userId] ?? null;
            $employee = &$summary['employees'][$userId];
            $employee['name'] ??= $user?->userable?->displayName() ?? $user?->email ?? 'User #' . $userId;
            $employee['methods'][$methodId] ??= ['payments' => 0.0, 'refunds' => 0.0, 'net' => 0.0];
            $method = &$employee['methods'][$methodId];

            $signed = $row->type === 'refund' ? -$row->total : $row->total;
            $method[$row->type === 'refund' ? 'refunds' : 'payments'] = round($method[$row->type === 'refund' ? 'refunds' : 'payments'] + $row->total, 2);
            $method['net'] = round($method['net'] + $signed, 2);
            $employee['net'] = round(($employee['net'] ?? 0) + $signed, 2);

            $summary['methods'][$methodId] ??= ['name' => $methodNames[$methodId] ?? 'Method #' . $methodId, 'net' => 0.0];
            $summary['methods'][$methodId]['net'] = round($summary['methods'][$methodId]['net'] + $signed, 2);
            $summary['cash_total'] = round($summary['cash_total'] + $signed, 2);
            unset($employee, $method);
        }

        return $summary;
    }

    // close a day (today or before): its totals are saved, its payments are locked; a reopened day is closed again
    public function close($accountId, $date, Model $user): DayClosing
    {
        $date = Carbon::parse($date)->startOfDay();
        if ($date->isFuture()) {
            throw ValidationException::withMessages(['date' => 'A day in the future can\'t be closed.']);
        }

        return DB::transaction(function () use ($accountId, $date, $user) {
            $closing = DayClosing::where('account_id', $accountId)->whereDate('date', $date)->lockForUpdate()->first();
            if ($closing?->status === 'closed') {
                throw ValidationException::withMessages(['date' => 'The day ' . $date->format(Reservation::DATE_FORMAT) . ' is already closed.']);
            }
            $this->assertShiftsClosed($accountId, $date);

            $summary = $this->summary($accountId, $date);
            $closing ??= new DayClosing(['account_id' => $accountId, 'date' => $date]);
            $closing->fill([
                'status' => 'closed',
                'cash_total' => $summary['cash_total'],
                'withholding_total' => $summary['withholding']['total'],
                'totals' => $summary,
                'closed_by' => $user->getKey(),
                'closed_at' => now(),
            ])->save();
            $this->log($closing, 'close', $user);

            return $closing;
        });
    }

    // every employee payment dated that day is in a closed shift (else: who is still open)
    private function assertShiftsClosed($accountId, Carbon $date): void
    {
        $blocking = $this->shiftClosingService->blockingForDate($accountId, $date);
        if (!$blocking) {
            return;
        }

        $names = $blocking['open']->map(fn ($period) => ($period['user']?->userable?->displayName() ?? 'User #' . $period['user']?->id) . ' (open shift)')
            ->merge($blocking['reopened']->toBase()->map(fn ($closing) => ($closing->user?->userable?->displayName() ?? 'User #' . $closing->user_id) . ' (reopened shift)'));
        throw ValidationException::withMessages([
            'date' => 'The day ' . $date->format(Reservation::DATE_FORMAT) . ' has payments in shifts not closed yet: ' . $names->implode(', ') . '.',
        ]);
    }

    // the owner opens a closed day again (its payments can be changed), the reason is kept
    public function reopen($accountId, $id, string $reason, Model $user): DayClosing
    {
        if (!$this->paymentService->isOwner($user)) {
            abort(403, 'Only the account owner can reopen a day.');
        }
        $closing = $this->findOne($accountId, $id);
        if ($closing->status !== 'closed') {
            throw ValidationException::withMessages(['reopen_reason' => 'This day is not closed.']);
        }
        if (trim($reason) === '') {
            throw ValidationException::withMessages(['reopen_reason' => 'Write why the day is opened again.']);
        }

        return DB::transaction(function () use ($closing, $reason, $user) {
            $closing->update(['status' => 'reopened', 'reopened_by' => $user->getKey(), 'reopened_at' => now(), 'reopen_reason' => trim($reason)]);
            $this->log($closing, 'reopen', $user, trim($reason));
            return $closing;
        });
    }

    // a history row with the closing's totals now (close: just saved; reopen: the ones that were closed)
    private function log(DayClosing $closing, string $action, Model $user, ?string $reason = null): void
    {
        DayClosingLog::create([
            'account_id' => $closing->account_id,
            'day_closing_id' => $closing->id,
            'action' => $action,
            'user_id' => $user->getKey(),
            'reason' => $reason,
            'cash_total' => $closing->cash_total,
            'withholding_total' => $closing->withholding_total,
            'totals' => $closing->totals,
        ]);
    }
}
