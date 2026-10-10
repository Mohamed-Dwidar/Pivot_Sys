<?php

namespace Modules\PaymentModule\app\Models;

use App\Helpers\SafeMorphHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Modules\AccountModule\app\Models\Account;
use Modules\MemberModule\app\Models\Member;
use Modules\PackageModule\app\Models\Package;
use Modules\ReservationModule\app\Models\Reservation;
use Modules\UserModule\app\Models\User;

/**
 * A payment of a reservation / a package (payable), or other income (no payable, income_category_id).
 * amount is always positive, type gives the sign: payment / withholding add, refund subtracts.
 */
class Payment extends Model
{
    use SafeMorphHelper, SoftDeletes;

    const TYPES = [
        'payment' => 'Payment',
        'refund' => 'Refund',
        'withholding' => 'Withholding',
    ];

    // the list filter "for": the payable models (+ income = no payable)
    const SOURCES = [
        'reservation' => Reservation::class,
        'package' => Package::class,
    ];

    // the signed amount in SQL: payments + withholding - refunds (paid totals)
    const PAID_SQL = "IF(type = 'refund', -amount, amount)";

    protected $fillable = [
        'account_id', 'payment_no', 'payable_type', 'payable_id', 'income_category_id', 'member_id',
        'type', 'amount', 'payment_method_id', 'withholding_certificate_id', 'paid_at', 'reference', 'notes',
        'delete_reason', 'deleted_by',
    ];

    protected $casts = [
        'payment_no' => 'integer',
        'amount' => 'float',
        'paid_at' => 'date',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    // the reservation / package paid (null for other income)
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function incomeCategory(): BelongsTo
    {
        return $this->belongsTo(IncomeCategory::class)->withTrashed();
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class)->withTrashed();
    }

    public function withholdingCertificate(): BelongsTo
    {
        return $this->belongsTo(WithholdingCertificate::class);
    }

    // who added the payment (users: the account or an employee)
    public function creatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    // + for payments / withholding, - for refunds
    public function getSignedAmountAttribute(): float
    {
        return $this->type === 'refund' ? -$this->amount : $this->amount;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    // the type badge (theme palette, custom.css badge-status-*)
    public function getTypeBadgeClassAttribute(): string
    {
        return ['payment' => 'badge-status-success', 'refund' => 'badge-status-danger', 'withholding' => 'badge-status-info'][$this->type] ?? 'badge-status-slate';
    }

    // paid in total by the payments of a query: payments + withholding - refunds
    public static function paidTotal($query): float
    {
        return round((float) (clone $query)->sum(DB::raw(self::PAID_SQL)), 2);
    }

    // other income (no reservation / package)
    public function scopeIncome($query)
    {
        return $query->whereNull('payable_type');
    }

    public function scopeFilter($query, array $filters = [])
    {
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($query) use ($search) {
                $query->where('payment_no', 'like', $search)
                    ->orWhere('reference', 'like', $search)
                    ->orWhereHas('member', fn ($query) => $query->where('name', 'like', $search)->orWhere('phone', 'like', str_replace(' ', '', $search)));
            });
        }

        // what is paid: reservation | package | income (other income)
        if (($filters['source'] ?? '') === 'income') {
            $query->income();
        } elseif (isset(self::SOURCES[$filters['source'] ?? ''])) {
            $query->where('payable_type', (new (self::SOURCES[$filters['source']]))->getMorphClass());
        }

        foreach (['type', 'payment_method_id', 'income_category_id', 'member_id'] as $field) {
            if (!empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('paid_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('paid_at', '<=', $filters['date_to']);
        }

        return $query;
    }
}
