<?php

namespace Modules\PaymentModule\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\AccountModule\app\Models\Account;
use Modules\UserModule\app\Models\User;

/**
 * An employee's closed shift: the payments they added since their last closing (payments.shift_closing_id), at any time of day.
 * Closed = its payments are locked; only the account reopens it (reason), its payments stay in it and it is closed again.
 * The account's own payments are not in a shift (locked by the day closing). ShiftClosingService.
 */
class ShiftClosing extends Model
{
    const STATUSES = [
        'closed' => 'Closed',
        'reopened' => 'Reopened',
    ];

    protected $fillable = [
        'account_id', 'user_id', 'status', 'first_payment_at', 'last_payment_at', 'count', 'cash_total', 'withholding_total', 'totals', 'notes',
        'closed_by', 'closed_at', 'reopened_by', 'reopened_at', 'reopen_reason',
    ];

    protected $casts = [
        'first_payment_at' => 'datetime',
        'last_payment_at' => 'datetime',
        'count' => 'integer',
        'cash_total' => 'float',
        'withholding_total' => 'float',
        'totals' => 'array',
        'closed_at' => 'datetime',
        'reopened_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    // the employee who collected the payments
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // every close / reopen / re-close, newest first
    public function events(): HasMany
    {
        return $this->hasMany(ShiftEvent::class)->latest('id');
    }

    // the closings that lock their payments
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }
}
