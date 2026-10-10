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

    // certificate amount - its payments (0 = matches)
    public function difference(): float
    {
        return round($this->amount - (float) $this->payments()->sum('amount'), 2);
    }
}
