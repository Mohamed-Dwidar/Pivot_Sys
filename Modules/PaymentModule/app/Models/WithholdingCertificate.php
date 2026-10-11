<?php

namespace Modules\PaymentModule\app\Models;

use App\Helpers\SafeMorphHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AccountModule\app\Models\Account;
use Modules\CompanyModule\app\Models\Company;
use Modules\MemberModule\app\Models\Member;

/**
 * A withholding tax certificate received from a customer (company / member).
 * It covers withholding payments (payments.withholding_certificate_id); its amount should equal their sum.
 */
class WithholdingCertificate extends Model
{
    use SafeMorphHelper, SoftDeletes;

    protected $fillable = [
        'account_id', 'certificate_no', 'issued_at', 'company_id', 'member_id', 'payer_name', 'payer_tax_id',
        'period_from', 'period_to', 'amount', 'attachment', 'notes',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'period_from' => 'date',
        'period_to' => 'date',
        'amount' => 'float',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    // the withholding payments it covers
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->where('type', 'withholding');
    }

    // who added the certificate (users)
    public function creatable(): MorphTo
    {
        return $this->morphTo();
    }

    // certificate amount - its payments (0 = matches); the list loads the sum (withSum: payments_sum_amount)
    public function difference(): float
    {
        $paid = array_key_exists('payments_sum_amount', $this->attributes) ? $this->payments_sum_amount : $this->payments()->sum('amount');
        return round($this->amount - (float) $paid, 2);
    }

    public function scopeFilter($query, array $filters = [])
    {
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(fn ($query) => $query->where('certificate_no', 'like', $search)->orWhere('payer_name', 'like', $search)->orWhere('payer_tax_id', 'like', $search));
        }

        foreach (['company_id', 'member_id'] as $field) {
            if (!empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('issued_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('issued_at', '<=', $filters['date_to']);
        }

        // matches: the payments total = the amount; difference: not equal
        if (($filters['match'] ?? '') !== '') {
            $sum = '(SELECT COALESCE(SUM(p.amount), 0) FROM payments p WHERE p.withholding_certificate_id = withholding_certificates.id AND p.type = \'withholding\' AND p.deleted_at IS NULL)';
            $query->whereRaw('ROUND(withholding_certificates.amount - ' . $sum . ', 2) ' . ($filters['match'] ? '= 0' : '<> 0'));
        }

        return $query;
    }
}
