<?php

namespace Modules\SpaceModule\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\AccountModule\app\Models\Account;

class SubscriptionType extends Model
{
    // yes / no options => labels (form checkboxes, list badges)
    const OPTIONS = [
        'show_home' => 'Show on Home',
        // the reservations of this type can repeat / renew automatically (continue) (the reservation form shows these options only then)
        'can_repeat' => 'Can Repeat',
        'auto_renew' => 'Auto Renew',
        // a contract (monthly subscription, one reservation per period): the reservations list collapses it on the daily
        // tabs and lists it on its Subscriptions tab (docs/reservations-list-plan.md)
        'is_contract' => 'Contract',
    ];

    /**
     * How the type is booked (replaces the old full_day flag) = its Today page section: hourly = a timeline of its units,
     * full_day = the day's list (booked by date), period = running counts + renewals (subscriptions / contracts: one
     * reservation per period, renewed by a new period with renewal_of)
     */
    const BOOKING_MODES = [
        'hourly' => 'Hourly',
        'full_day' => 'Full Day',
        'period' => 'Period',
    ];

    protected $fillable = ['account_id', 'name', 'show_home', 'booking_mode', 'home_order', 'can_repeat', 'auto_renew', 'is_contract'];

    protected $casts = [
        'show_home' => 'boolean',
        'home_order' => 'integer',
        'can_repeat' => 'boolean',
        'auto_renew' => 'boolean',
        'is_contract' => 'boolean',
    ];

    public function isHourly(): bool
    {
        return $this->booking_mode === 'hourly';
    }

    public function isFullDay(): bool
    {
        return $this->booking_mode === 'full_day';
    }

    public function isPeriod(): bool
    {
        return $this->booking_mode === 'period';
    }

    // the ids of the types of a booking mode (all accounts: the ids are global), once per request
    public static function idsOf(string $mode): array
    {
        $modes = once(fn () => static::pluck('booking_mode', 'id')->all());
        return array_keys(array_filter($modes, fn ($typeMode) => $typeMode === $mode));
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    // many to many (pivot: space_subscription_type)
    public function spaces(): BelongsToMany
    {
        return $this->belongsToMany(Space::class, 'space_subscription_type', 'subscription_type_id', 'space_id')->withTimestamps();
    }

    public function scopeFilter($query, array $filters = [])
    {
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query;
    }
}
