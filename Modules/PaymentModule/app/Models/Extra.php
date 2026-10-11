<?php

namespace Modules\PaymentModule\app\Models;

use App\Helpers\SafeMorphHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * An amount added to what a package (later a reservation) owes: due = net + Σ extras (see Concerns\HasExtras).
 * type extra = a real sale (revenue, income category, invoiceable in phase 2); adjustment = a balance move (not revenue, can be negative).
 * amount = quantity × unit_price (signed).
 */
class Extra extends Model
{
    use SafeMorphHelper, SoftDeletes;

    const TYPES = [
        'extra' => 'Extra',
        'adjustment' => 'Adjustment',
    ];

    protected $fillable = [
        'account_id', 'extrable_type', 'extrable_id', 'type', 'income_category_id', 'item', 'quantity', 'unit_price', 'amount', 'date', 'notes',
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_price' => 'float',
        'amount' => 'float',
        'date' => 'date',
    ];

    // the package (later a reservation) it is added to
    public function extrable(): MorphTo
    {
        return $this->morphTo();
    }

    public function incomeCategory(): BelongsTo
    {
        return $this->belongsTo(IncomeCategory::class)->withTrashed();
    }

    // who added it (users)
    public function creatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    // the type badge (theme palette, custom.css badge-status-*)
    public function getTypeBadgeClassAttribute(): string
    {
        return $this->type === 'adjustment' ? 'badge-status-slate' : 'badge-status-info';
    }
}
