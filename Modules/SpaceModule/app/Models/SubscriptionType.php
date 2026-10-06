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
        'is_repeat' => 'Repeat',
        'is_continue' => 'Continuous',
        'full_day' => 'Full Day',
    ];

    protected $fillable = ['account_id', 'name', 'show_home', 'is_repeat', 'is_continue', 'full_day'];

    protected $casts = [
        'show_home' => 'boolean',
        'is_repeat' => 'boolean',
        'is_continue' => 'boolean',
        'full_day' => 'boolean',
    ];

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
