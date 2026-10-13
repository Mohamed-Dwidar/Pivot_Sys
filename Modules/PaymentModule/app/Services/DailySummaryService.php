<?php

namespace Modules\PaymentModule\app\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\PaymentModule\app\Models\Payment;
use Modules\PaymentModule\app\Models\PaymentMethod;
use Modules\UserModule\app\Models\User;

/**
 * The Daily Summary (account only, read only; docs/payments-plan.md §2b, 2026-10-13): what was collected on a date (paid_at),
 * from the payments now — no day closing, no lock (the shift is the only closing level). Payments, refunds and withholding
 * of every collector (the employees and the account itself).
 */
class DailySummaryService
{
    /**
     * The totals of a day: ['collectors' => [user_id => ['name', 'methods' => [method_id => ['payments', 'refunds', 'net']], 'net']],
     *  'methods' => [method_id => ['name', 'payments', 'refunds', 'net']], 'refunds' => ['count', 'total'],
     *  'withholding' => ['count', 'total'], 'cash_total' (payments - refunds, withholding apart), 'count']
     */
    public function summary($accountId, $date): array
    {
        $rows = Payment::where('account_id', $accountId)->where('paid_at', Carbon::parse($date)->toDateString())
            ->groupBy('creatable_type', 'creatable_id', 'payment_method_id', 'type')
            ->selectRaw('creatable_type, creatable_id, payment_method_id, type, COUNT(*) n, SUM(amount) total')
            ->get();

        $methodNames = PaymentMethod::withTrashed()->where('account_id', $accountId)->get()->pluck('name', 'id');
        $users = User::with('userable')->whereIn('id', $rows->pluck('creatable_id')->unique())->get()->keyBy('id');
        $summary = ['collectors' => [], 'methods' => [], 'refunds' => ['count' => 0, 'total' => 0.0],
            'withholding' => ['count' => 0, 'total' => 0.0], 'cash_total' => 0.0, 'count' => 0];

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
            $key = $row->type === 'refund' ? 'refunds' : 'payments';
            $signed = $row->type === 'refund' ? -$row->total : $row->total;

            $collector = &$summary['collectors'][$userId];
            $collector['name'] ??= $user?->userable?->displayName() ?? $user?->email ?? 'User #' . $userId;
            $collector['methods'][$methodId] ??= ['payments' => 0.0, 'refunds' => 0.0, 'net' => 0.0];
            $line = &$collector['methods'][$methodId];
            $line[$key] = round($line[$key] + $row->total, 2);
            $line['net'] = round($line['net'] + $signed, 2);
            $collector['net'] = round(($collector['net'] ?? 0) + $signed, 2);

            $method = &$summary['methods'][$methodId];
            $method ??= ['name' => $methodNames[$methodId] ?? 'Method #' . $methodId, 'payments' => 0.0, 'refunds' => 0.0, 'net' => 0.0];
            $method[$key] = round($method[$key] + $row->total, 2);
            $method['net'] = round($method['net'] + $signed, 2);

            if ($row->type === 'refund') {
                $summary['refunds']['count'] += $row->n;
                $summary['refunds']['total'] = round($summary['refunds']['total'] + $row->total, 2);
            }
            $summary['cash_total'] = round($summary['cash_total'] + $signed, 2);
            unset($collector, $line, $method);
        }

        return $summary;
    }

    // the refunds dated that day (who, amount, the original payment, reason), as they were added
    public function refunds($accountId, $date): Collection
    {
        return Payment::with('creatable.userable', 'refundOf', 'paymentMethod', 'member')
            ->where('account_id', $accountId)->where('type', 'refund')->where('paid_at', Carbon::parse($date)->toDateString())
            ->orderBy('created_at')->get();
    }
}
