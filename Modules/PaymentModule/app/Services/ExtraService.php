<?php

namespace Modules\PaymentModule\app\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\PackageModule\app\Models\Package;
use Modules\PaymentModule\app\Models\Extra;
use Modules\PaymentModule\app\Models\IncomeCategory;

/**
 * Extras of an account's packages (only the account manages them). Every method takes the owner account id.
 * extra = a sale: needs an income category (revenue, invoiceable in phase 2); adjustment = a balance move: no category, any sign.
 * amount = quantity × unit_price; after every change the package paid / remaining are refreshed (due = net + Σ extras).
 * Rule errors = ValidationException (also safe for imports / commands).
 */
class ExtraService
{
    // the payables that can have extras => their models (a reservation later)
    const EXTRABLES = [
        'package' => Package::class,
    ];

    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    // the package of the account (404 otherwise)
    public function findExtrable($accountId, string $kind, $id): Model
    {
        $class = self::EXTRABLES[$kind] ?? abort(404);
        return $class::where('account_id', $accountId)->findOrFail($id);
    }

    // 404 when it belongs to another account
    public function findOne($accountId, $id): Extra
    {
        return Extra::with('extrable', 'incomeCategory')->where('account_id', $accountId)->findOrFail($id);
    }

    // $data: type, income_category_id, item, quantity, unit_price, date, notes
    public function create($accountId, Model $extrable, array $data, Model $creator): Extra
    {
        $values = $this->extraData($accountId, $data);

        return DB::transaction(function () use ($accountId, $extrable, $values, $creator) {
            $extra = new Extra($values + ['account_id' => $accountId]);
            $extra->extrable()->associate($extrable);
            $extra->creatable()->associate($creator);
            $extra->save();
            $this->paymentService->refresh($extrable);

            return $extra;
        });
    }

    public function update($accountId, $id, array $data): Extra
    {
        $extra = $this->findOne($accountId, $id);
        $values = $this->extraData($accountId, $data);

        return DB::transaction(function () use ($extra, $values) {
            $extra->update($values);
            $this->paymentService->refresh($extra->extrable);

            return $extra;
        });
    }

    // soft delete
    public function deleteOne($accountId, $id): Extra
    {
        $extra = $this->findOne($accountId, $id);

        return DB::transaction(function () use ($extra) {
            $extra->delete();
            $this->paymentService->refresh($extra->extrable);

            return $extra;
        });
    }

    private function extraData($accountId, array $data): array
    {
        $type = $data['type'] ?? 'extra';
        if (!isset(Extra::TYPES[$type])) {
            throw ValidationException::withMessages(['type' => 'Choose extra or adjustment.']);
        }
        $categoryId = $type === 'extra' ? (int) ($data['income_category_id'] ?? 0) : 0;
        if ($type === 'extra' && !IncomeCategory::where('account_id', $accountId)->whereKey($categoryId)->exists()) {
            throw ValidationException::withMessages(['income_category_id' => 'Choose the income category of this extra.']);
        }

        $quantity = round((float) ($data['quantity'] ?? 1), 2);
        $unitPrice = round((float) ($data['unit_price'] ?? 0), 2);
        $amount = round($quantity * $unitPrice, 2);
        if ($quantity <= 0) {
            throw ValidationException::withMessages(['quantity' => 'The quantity must be more than 0.']);
        }
        if ($amount == 0) {
            throw ValidationException::withMessages(['unit_price' => 'The amount can\'t be 0.']);
        }

        return [
            'type' => $type,
            'income_category_id' => $categoryId,
            'item' => trim($data['item']),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'amount' => $amount,
            'date' => Carbon::parse($data['date'] ?? today())->toDateString(),
            'notes' => $data['notes'] ?? null,
        ];
    }
}
