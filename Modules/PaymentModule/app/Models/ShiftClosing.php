<?php

namespace Modules\PaymentModule\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\AccountModule\app\Models\Account;
use Modules\UserModule\app\Models\User;

/**
 * A closed shift: the payments one login (an employee, or the account itself) added since their last closing
 * (payments.shift_closing_id), at any time of day. The only closing level (docs/payments-plan.md §2b).
 * Its payments stay in it for good; the account may still edit / delete one (reason required): the totals are recalculated,
 * the change is logged (shift_events payment_*) and edited_at / edit_count mark it "Edited after closing". ShiftClosingService.
 */
class ShiftClosing extends Model
{
    protected $fillable = [
        'account_id', 'user_id', 'first_payment_at', 'last_payment_at', 'count', 'cash_total', 'withholding_total', 'totals', 'notes',
        'closed_by', 'closed_at', 'edited_at', 'edit_count',
    ];

    protected $casts = [
        'first_payment_at' => 'datetime',
        'last_payment_at' => 'datetime',
        'count' => 'integer',
        'cash_total' => 'float',
        'withholding_total' => 'float',
        'totals' => 'array',
        'closed_at' => 'datetime',
        'edited_at' => 'datetime',
        'edit_count' => 'integer',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    // who collected the payments (an employee, or the account)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // the close + every change after it (payment edited / deleted / refunded), newest first
    public function events(): HasMany
    {
        return $this->hasMany(ShiftEvent::class)->latest('id');
    }

    // a payment of it was edited / deleted / refunded after closing (the "Edited after closing" badge)
    public function isEdited(): bool
    {
        return $this->edit_count > 0;
    }
}
