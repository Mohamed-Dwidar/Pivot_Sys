<?php

namespace Modules\PaymentModule\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\UserModule\app\Models\User;

// one step in an employee's shift history: a closing / reopen / re-close, or a reminder answer (shift_closing_id 0 = the open period)
class ShiftEvent extends Model
{
    const ACTIONS = [
        'close' => 'Closed',
        'close_on_behalf' => 'Closed by the account',
        'reopen' => 'Reopened',
        'reclose' => 'Closed again',
        'logout_closed' => 'Closed at logout',
        'logout_still_working' => 'Logged out, still working',
        'still_working' => 'Still working',
    ];

    // only created_at (a log row is never changed)
    const UPDATED_AT = null;

    protected $fillable = ['account_id', 'user_id', 'shift_closing_id', 'by_user_id', 'action', 'reason', 'totals'];

    protected $casts = [
        'totals' => 'array',
        'created_at' => 'datetime',
    ];

    public function shiftClosing(): BelongsTo
    {
        return $this->belongsTo(ShiftClosing::class);
    }

    // the employee whose shift it is
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // who did it (the employee or the account)
    public function byUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'by_user_id');
    }

    public function getActionLabelAttribute(): string
    {
        return self::ACTIONS[$this->action] ?? $this->action;
    }
}
