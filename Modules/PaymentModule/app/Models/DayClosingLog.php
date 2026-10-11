<?php

namespace Modules\PaymentModule\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\UserModule\app\Models\User;

// one close / reopen of a day closing (the full history; day_closings keeps the current status)
class DayClosingLog extends Model
{
    const ACTIONS = [
        'close' => 'Closed',
        'reopen' => 'Reopened',
    ];

    // only created_at (a log row is never changed)
    const UPDATED_AT = null;

    protected $fillable = ['account_id', 'day_closing_id', 'action', 'user_id', 'reason', 'cash_total', 'withholding_total', 'totals'];

    protected $casts = [
        'cash_total' => 'float',
        'withholding_total' => 'float',
        'totals' => 'array',
        'created_at' => 'datetime',
    ];

    public function dayClosing(): BelongsTo
    {
        return $this->belongsTo(DayClosing::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
