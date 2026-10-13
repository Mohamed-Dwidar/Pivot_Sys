<?php

namespace Modules\PaymentModule\app\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\AccountModule\app\Models\Account;
use Modules\MemberModule\app\Models\Member;
use Modules\PackageModule\app\Models\Package;
use Modules\PaymentModule\app\Contracts\Payable;
use Modules\PaymentModule\app\Models\DayClosing;
use Modules\PaymentModule\app\Models\IncomeCategory;
use Modules\PaymentModule\app\Models\Payment;
use Modules\PaymentModule\app\Models\PaymentMethod;
use Modules\PaymentModule\app\Models\ShiftClosing;
use Modules\PaymentModule\app\Repositories\PaymentRepository;
use Modules\ReservationModule\app\Models\Reservation;

/**
 * Payments of an account: every method takes the owner account id, so an account can only reach its own payments.
 * - amount always positive, type gives the sign (Payment::PAID_SQL); paid_amount / remaining of the payable kept by refresh()
 * - a payment dated on a closed day can't be added (assertDayOpen()); a payment in a closed shift or on a closed day
 *   can't be changed / deleted (assertPaymentOpen()) — two levels, docs/payments-plan.md §2b
 * - payment_no: per account, MAX + 1 inside the transaction (locked), from config paymentmodule.number_start
 * The rules errors are thrown as ValidationException (422, shown under the fields by custom.js).
 * The service checks the account ownership itself (method, category, member, payable): it is also called by imports / invoices.
 */
class PaymentService
{
    // the payable kinds of the payment form => their models
    const PAYABLES = Payment::SOURCES;

    private $paymentRepository;

    public function __construct(PaymentRepository $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    // list query (paged / ordered by DataTables)
    public function listQuery($accountId, array $filters = [])
    {
        return $this->paymentRepository->forAccount($accountId)->filter($filters);
    }

    /**
     * The summary line of the payments list for a filtered query: the payments by method, refunds, withholding.
     * total = payments − refunds (cash); withholding apart (not cash). Methods in the account's order, only those used.
     * ['total' => float, 'methods' => [name => amount], 'refunds' => float, 'withholding' => float]
     */
    public function listSummary($query): array
    {
        $rows = (clone $query)->reorder()->setEagerLoads([])->toBase()
            ->selectRaw('type, payment_method_id, SUM(amount) AS total')->groupBy('type', 'payment_method_id')->get();

        $payments = $rows->where('type', 'payment');
        $methods = PaymentMethod::withTrashed()->whereIn('id', $payments->pluck('payment_method_id'))->ordered()->get();
        $byMethod = $methods->mapWithKeys(fn ($method) => [$method->name => round((float) $payments->where('payment_method_id', $method->id)->sum('total'), 2)]);
        // a payment whose method is gone / unknown (imported data)
        $other = round((float) $payments->whereNotIn('payment_method_id', $methods->pluck('id'))->sum('total'), 2);
        if ($other) {
            $byMethod['Other'] = $other;
        }

        $refunds = round((float) $rows->where('type', 'refund')->sum('total'), 2);

        return [
            'total' => round((float) $payments->sum('total') - $refunds, 2),
            'methods' => $byMethod->all(),
            'refunds' => $refunds,
            'withholding' => round((float) $rows->where('type', 'withholding')->sum('total'), 2),
        ];
    }

    // 404 when the payment belongs to another account
    public function findOne($accountId, $id): Payment
    {
        return $this->paymentRepository->forAccount($accountId)->with('creatable.userable')->findOrFail($id);
    }

    // the member's reservations (not inside a package) / packages for the payment form:
    // [{id, name}] the ones still owed first, the name with what is left to pay
    public function payableOptions($accountId, string $kind, $memberId): array
    {
        $class = self::PAYABLES[$kind] ?? abort(404);
        $query = $class::where('account_id', $accountId)->where('member_id', (int) $memberId)
            ->orderByRaw('remaining > 0 DESC');

        if ($kind === 'reservation') {
            $query->where('package_id', 0)->with('space', 'unit', 'plan')->orderByDesc('start_at');
        } else {
            $query->orderByDesc('id');
        }

        return $query->get()->map(fn ($payable) => [
            'id' => $payable->id,
            'name' => $this->payableName($payable) . ' — Remaining ' . number_format($payable->remaining, 2),
        ])->all();
    }

    // "#12 Space › Unit · 21-10-2026" / "#5 Package name · 01-10-2026 → 31-12-2026"
    public function payableName(?Model $payable): string
    {
        if ($payable instanceof Reservation) {
            return '#' . $payable->id . ' ' . ($payable->space?->name ?? '-') . ' › ' . ($payable->unit?->name ?? '-') . ' · ' . $payable->period;
        }
        if ($payable instanceof Package) {
            return '#' . $payable->id . ' ' . $payable->name . ' · ' . $payable->period;
        }

        return '-';
    }

    // the account owner (edit / delete / refund); employees only add payments
    public function isOwner(Model $user): bool
    {
        return $user->userable instanceof Account;
    }

    // the reservation / package of the account (404 otherwise); $kind: reservation | package
    public function findPayable($accountId, string $kind, $id): Payable
    {
        $class = self::PAYABLES[$kind] ?? abort(404);
        return $class::where('account_id', $accountId)->findOrFail($id);
    }

    /**
     * A payment (type payment / withholding; refunds: refund()).
     * $data: payable (reservation | package | empty = other income), payable_id, income_category_id, member_id (other income),
     *        type, amount, payment_method_id, paid_at, reference, notes
     */
    public function create($accountId, array $data, Model $creator): Payment
    {
        $type = $data['type'] ?? 'payment';
        if (!in_array($type, ['payment', 'withholding'], true)) {
            throw ValidationException::withMessages(['type' => 'Only a payment or a withholding can be added here.']);
        }

        $payable = !empty($data['payable']) ? $this->findPayable($accountId, $data['payable'], $data['payable_id'] ?? 0) : null;
        if ($payable && !$payable->canTakePayments()) {
            throw ValidationException::withMessages(['payable_id' => 'This reservation is inside a package, add the payment to the package.']);
        }
        if (!$payable) {
            $this->assertCategory($accountId, $data['income_category_id'] ?? 0);
        }
        if (!$payable && !empty($data['member_id']) && !Member::where('account_id', $accountId)->whereKey((int) $data['member_id'])->exists()) {
            throw ValidationException::withMessages(['member_id' => 'This member is not one of yours.']);
        }

        $amount = $this->assertAmount($data['amount'] ?? 0);
        if ($type === 'payment') {
            $this->assertMethod($accountId, $data['payment_method_id'] ?? 0);
        }

        $paidAt = Carbon::parse($data['paid_at'] ?? today())->startOfDay();
        $this->assertDayOpen($accountId, $paidAt);

        return DB::transaction(function () use ($accountId, $data, $creator, $type, $payable, $amount, $paidAt) {
            $payment = new Payment([
                'account_id' => $accountId,
                'payment_no' => $this->nextNumber($accountId),
                'payable_type' => $payable ? $payable->getMorphClass() : null,
                'payable_id' => $payable ? $payable->getKey() : 0,
                // other income only (a reservation / package gives its member)
                'income_category_id' => $payable ? 0 : (int) $data['income_category_id'],
                'member_id' => $payable ? $payable->payableMemberId() : (int) ($data['member_id'] ?? 0),
                'type' => $type,
                'amount' => $amount,
                // withholding: no money received (a certificate instead)
                'payment_method_id' => $type === 'withholding' ? 0 : (int) $data['payment_method_id'],
                'paid_at' => $paidAt,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
            $payment->creatable()->associate($creator);
            $payment->save();

            if ($payable) {
                $this->refresh($payable);
            }

            return $payment;
        });
    }

    /**
     * The owner's edit: amount, date, method, income category (other income), reference, notes.
     * The payable / member / type are kept (a payment for another reservation = delete it and add a new one).
     * Both the old and the new date must be open days.
     */
    public function update($accountId, $id, array $data, Model $user): Payment
    {
        $this->assertOwner($user);
        $payment = $this->findOne($accountId, $id);

        $amount = $this->assertAmount($data['amount'] ?? 0);
        // withholding: no money received, no method
        if ($payment->type !== 'withholding') {
            $this->assertMethod($accountId, $data['payment_method_id'] ?? 0);
        }
        if (!$payment->payable_type) {
            $this->assertCategory($accountId, $data['income_category_id'] ?? 0);
        }
        $paidAt = Carbon::parse($data['paid_at'] ?? $payment->paid_at)->startOfDay();
        $this->assertPaymentOpen($payment);
        $this->assertDayOpen($accountId, $paidAt);
        // a refund of a reservation / package: still not more than the money received (other income refunds are not linked)
        if ($payment->type === 'refund' && $payment->payable_type) {
            $this->assertRefundable($payment, $amount, $payment);
        }

        return DB::transaction(function () use ($payment, $data, $amount, $paidAt) {
            $payment->update([
                'amount' => $amount,
                'paid_at' => $paidAt,
                'payment_method_id' => $payment->type === 'withholding' ? 0 : (int) $data['payment_method_id'],
                'income_category_id' => $payment->payable_type ? 0 : (int) $data['income_category_id'],
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
            $this->refreshPayableOf($payment);

            return $payment;
        });
    }

    // the owner's delete: soft delete with the reason and who deleted it (kept for the audit report)
    public function delete($accountId, $id, string $reason, Model $user): Payment
    {
        $this->assertOwner($user);
        $payment = $this->findOne($accountId, $id);
        $this->assertPaymentOpen($payment, 'delete_reason');
        if (trim($reason) === '') {
            throw ValidationException::withMessages(['delete_reason' => 'Write why this payment is deleted.']);
        }

        return DB::transaction(function () use ($payment, $reason, $user) {
            $payment->update(['delete_reason' => trim($reason), 'deleted_by' => $user->getKey()]);
            $payment->delete();
            $this->refreshPayableOf($payment);

            return $payment;
        });
    }

    /**
     * The owner's refund of a payment: a new refund record (positive amount, type refund) for the same payable / income,
     * paid back with a method. Not more than the money received: a reservation / package = its payments - its refunds
     * (withholding is not money received); other income = this payment - nothing (refunds of income are not linked).
     * $data: amount, payment_method_id, paid_at, reference, notes
     */
    public function refund($accountId, $id, array $data, Model $user): Payment
    {
        $this->assertOwner($user);
        $payment = $this->findOne($accountId, $id);
        if ($payment->type !== 'payment') {
            throw ValidationException::withMessages(['amount' => 'Only a payment can be refunded (not a refund or a withholding).']);
        }

        $amount = $this->assertAmount($data['amount'] ?? 0);
        $this->assertMethod($accountId, $data['payment_method_id'] ?? 0);
        $this->assertRefundable($payment, $amount);
        $paidAt = Carbon::parse($data['paid_at'] ?? today())->startOfDay();
        $this->assertDayOpen($accountId, $paidAt);

        return DB::transaction(function () use ($accountId, $payment, $data, $user, $amount, $paidAt) {
            $refund = new Payment([
                'account_id' => $accountId,
                'payment_no' => $this->nextNumber($accountId),
                'payable_type' => $payment->payable_type,
                'payable_id' => $payment->payable_id,
                'income_category_id' => $payment->income_category_id,
                'member_id' => $payment->member_id,
                'type' => 'refund',
                'amount' => $amount,
                'payment_method_id' => (int) $data['payment_method_id'],
                'paid_at' => $paidAt,
                'reference' => ($data['reference'] ?? null) ?: 'Refund of #' . $payment->payment_no,
                'notes' => $data['notes'] ?? null,
            ]);
            $refund->creatable()->associate($user);
            $refund->save();
            $this->refreshPayableOf($refund);

            return $refund;
        });
    }

    // the most that can be refunded now (the refund form); $except: a refund being edited (its amount is not counted)
    public function refundable(Payment $payment, ?Payment $except = null): float
    {
        $query = Payment::where('account_id', $payment->account_id)->whereIn('type', ['payment', 'refund'])
            ->when($except, fn ($query) => $query->whereKeyNot($except->getKey()));
        $query = $payment->payable_type
            ? $query->where('payable_type', $payment->payable_type)->where('payable_id', $payment->payable_id)
            : null;

        // other income: the payment amount (its refunds are not linked to it)
        return round($query ? Payment::paidTotal($query) : $payment->amount, 2);
    }

    /**
     * A reservation moved into a package is paid through the package: its payments (all types, the deleted ones too)
     * move to the package; their amount, date, method and number don't change, so a closed day's totals stay right.
     * Both are refreshed. Returns the number of payments moved.
     */
    public function movePayments(Payable $from, Payable $to): int
    {
        $moved = Payment::withTrashed()->where('payable_type', $from->getMorphClass())->where('payable_id', $from->getKey())
            ->update(['payable_type' => $to->getMorphClass(), 'payable_id' => $to->getKey()]);

        $this->refresh($from instanceof Reservation ? $from->load('status') : $from);
        $this->refresh($to);

        return $moved;
    }

    /**
     * paid_amount / remaining of a reservation / package from its payments (remaining < 0 = member credit).
     * Not payable on its own (a reservation inside a package): due = 0.
     * $paid: the payments total when already known (refreshAll()), null = calculated here.
     */
    public function refresh(Payable $payable, ?float $paid = null): void
    {
        $paid = round($paid ?? Payment::paidTotal($payable->payments()), 2);
        $due = $payable->canTakePayments() ? $payable->payableAmount() : 0;
        $values = ['paid_amount' => $paid, 'remaining' => round($due - $paid, 2)];

        if (round((float) $payable->paid_amount, 2) === $values['paid_amount'] && round((float) $payable->remaining, 2) === $values['remaining']) {
            return;
        }

        // only these two columns: no updated_at, no model events
        $payable->forceFill($values);
        $payable->newQueryWithoutScopes()->whereKey($payable->getKey())->toBase()->update($values);
    }

    // refresh() of every reservation / package (php artisan payments:refresh); the number of rows changed
    public function refreshAll($accountId = null): int
    {
        $changed = 0;
        foreach (self::PAYABLES as $class) {
            $model = new $class;
            // the payments totals of all of them in one query: [payable_id => paid]
            $paid = Payment::where('payable_type', $model->getMorphClass())
                ->when($accountId, fn ($query) => $query->where('account_id', $accountId))
                ->groupBy('payable_id')
                ->selectRaw('payable_id, SUM(' . Payment::PAID_SQL . ') AS paid')
                ->pluck('paid', 'payable_id');

            $class::query()->when($accountId, fn ($query) => $query->where('account_id', $accountId))
                // the reservation status / the package extras give their due (payableAmount())
                ->when($class === Reservation::class, fn ($query) => $query->with('status'))
                ->when($class === Package::class, fn ($query) => $query->withSum('extras', 'amount'))
                ->chunkById(500, function ($payables) use ($paid, &$changed) {
                    foreach ($payables as $payable) {
                        $before = [$payable->paid_amount, $payable->remaining];
                        $this->refresh($payable, (float) ($paid[$payable->getKey()] ?? 0));
                        $changed += $before !== [$payable->paid_amount, $payable->remaining] ? 1 : 0;
                    }
                });
        }

        return $changed;
    }

    private function assertOwner(Model $user): void
    {
        if (!$this->isOwner($user)) {
            abort(403, 'Only the account owner can change, delete or refund payments.');
        }
    }

    // the amount (> 0, 2 decimals)
    private function assertAmount($amount): float
    {
        $amount = round((float) $amount, 2);
        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'The amount must be more than 0.']);
        }
        return $amount;
    }

    private function assertMethod($accountId, $methodId): void
    {
        if (empty($methodId)) {
            throw ValidationException::withMessages(['payment_method_id' => 'Choose the payment method.']);
        }
        if (!PaymentMethod::where('account_id', $accountId)->whereKey((int) $methodId)->exists()) {
            throw ValidationException::withMessages(['payment_method_id' => 'This payment method is not one of yours.']);
        }
    }

    private function assertCategory($accountId, $categoryId): void
    {
        if (empty($categoryId)) {
            throw ValidationException::withMessages(['income_category_id' => 'Choose the income category of this payment.']);
        }
        if (!IncomeCategory::where('account_id', $accountId)->whereKey((int) $categoryId)->exists()) {
            throw ValidationException::withMessages(['income_category_id' => 'This income category is not one of yours.']);
        }
    }

    private function assertRefundable(Payment $payment, float $amount, ?Payment $except = null): void
    {
        $max = $this->refundable($payment, $except);
        if ($amount > $max) {
            throw ValidationException::withMessages(['amount' => 'Not more than ' . number_format(max(0, $max), 2) . ' can be refunded (the money received).']);
        }
    }

    // the reservation / package of a payment after it changed (other income: nothing)
    private function refreshPayableOf(Payment $payment): void
    {
        $payable = $payment->load('payable')->payable;
        if ($payable instanceof Payable) {
            $this->refresh($payable instanceof Reservation ? $payable->load('status') : $payable);
        }
    }

    // a payment can be changed / deleted: not in a closed shift (the account reopens the shift), not on a closed day
    public function assertPaymentOpen(Payment $payment, string $field = 'paid_at'): void
    {
        if ($payment->shift_closing_id && ShiftClosing::closed()->whereKey($payment->shift_closing_id)->exists()) {
            $closing = ShiftClosing::with('user.userable')->find($payment->shift_closing_id);
            throw ValidationException::withMessages([
                $field => 'This payment is in a closed shift of ' . ($closing->user?->userable?->displayName() ?? 'an employee')
                    . ' (closed ' . $closing->closed_at->format(Reservation::DATE_FORMAT . ' ' . Reservation::TIME_FORMAT) . '). Reopen the shift first.',
            ]);
        }
        $this->assertDayOpen($payment->account_id, $payment->paid_at, $field);
    }

    // a closed day locks its payments: no add / edit / delete dated on it
    public function assertDayOpen($accountId, $date, string $field = 'paid_at'): void
    {
        $date = Carbon::parse($date);
        if (DayClosing::closed()->where('account_id', $accountId)->whereDate('date', $date)->exists()) {
            throw ValidationException::withMessages([
                $field => 'The day ' . $date->format(Reservation::DATE_FORMAT) . ' is closed, its payments can\'t be changed.',
            ]);
        }
    }

    // the next payment number of the account (call inside a transaction: the rows read are locked until it ends);
    // never below config paymentmodule.number_start (the numbers below it are the old imported payments)
    public function nextNumber($accountId): int
    {
        $last = (int) Payment::withTrashed()->where('account_id', $accountId)->lockForUpdate()->max('payment_no');
        return max($last + 1, (int) config('paymentmodule.number_start', 1));
    }
}
