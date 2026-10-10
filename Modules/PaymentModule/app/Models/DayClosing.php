<?php

namespace Modules\PaymentModule\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AccountModule\app\Models\Account;
use Modules\UserModule\app\Models\User;

/**
 * The closing of one day of an account: locks the payments dated that day (DayClosingService).
 * Totals by employee x payment method; withholding not in the cash totals.
 */
class DayClosing extends Model
{
    const STATUSES = [
        'closed' => 'Closed',
        'reopened' => 'Reopened',
    ];

    protected $fillable = [
        'account_id', 'date', 'status', 'cash_total', 'withholding_total', 'totals',
        'closed_by', 'closed_at', 'reopened_by', 'reopened_at', 'reopen_reason',
    ];

    protected $casts = [
        'date' => 'date',
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

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }

    // the closings that lock their day
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }
}
