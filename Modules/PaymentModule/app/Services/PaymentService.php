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
use Modules\PaymentModule\app\Repositories\PaymentRepository;
use Modules\ReservationModule\app\Models\Reservation;

/**
 * Payments of an account: every method takes the owner account id, so an account can only reach its own payments.
 * - amount always positive, type gives the sign (Payment::PAID_SQL); paid_amount / remaining of the payable kept by refresh()
 * - a payment dated on a closed day can't be added (assertDayOpen())
 * - payment_no: per account, MAX + 1 inside the transaction (locked)
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
        if (!$payable && empty($data['income_category_id'])) {
            throw ValidationException::withMessages(['income_category_id' => 'Choose the income category of this payment.']);
        }
        if (!$payable && !IncomeCategory::where('account_id', $accountId)->whereKey((int) $data['income_category_id'])->exists()) {
            throw ValidationException::withMessages(['income_category_id' => 'This income category is not one of yours.']);
        }
        if (!$payable && !empty($data['member_id']) && !Member::where('account_id', $accountId)->whereKey((int) $data['member_id'])->exists()) {
            throw ValidationException::withMessages(['member_id' => 'This member is not one of yours.']);
        }

        $amount = round((float) ($data['amount'] ?? 0), 2);
        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'The amount must be more than 0.']);
        }
        if ($type === 'payment' && empty($data['payment_method_id'])) {
            throw ValidationException::withMessages(['payment_method_id' => 'Choose the payment method.']);
        }
        if ($type === 'payment' && !PaymentMethod::where('account_id', $accountId)->whereKey((int) $data['payment_method_id'])->exists()) {
            throw ValidationException::withMessages(['payment_method_id' => 'This payment method is not one of yours.']);
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
                // the reservation status gives its due (payableAmount())
                ->when($class === Reservation::class, fn ($query) => $query->with('status'))
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

    // the next payment number of the account (call inside a transaction: the rows read are locked until it ends)
    public function nextNumber($accountId): int
    {
        return (int) Payment::withTrashed()->where('account_id', $accountId)->lockForUpdate()->max('payment_no') + 1;
    }
}
