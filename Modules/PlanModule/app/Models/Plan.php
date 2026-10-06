<?php

namespace Modules\PlanModule\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AccountModule\app\Models\Account;
use Modules\SpaceModule\app\Models\Space;
use Modules\UnitModule\app\Models\Unit;

class Plan extends Model
{
    use SoftDeletes;

    // lease_period values => labels (same as the units)
    const LEASE_PERIODS = Unit::LEASE_PERIODS;

    protected $fillable = ['account_id', 'name', 'capacity', 'lease_period', 'facilities', 'amount', 'is_active'];

    protected $casts = [
        'capacity' => 'integer',
        'amount' => 'float',
        'is_active' => 'boolean',
    ];

    public function getLeasePeriodLabelAttribute(): ?string
    {
        return self::LEASE_PERIODS[$this->lease_period] ?? null;
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    // many to many (pivot: plan_space), chosen from the space form
    public function spaces(): BelongsToMany
    {
        return $this->belongsToMany(Space::class, 'plan_space', 'plan_id', 'space_id')->withTimestamps();
    }

    // many to many (pivot: plan_unit), chosen from the unit form (only plans of the unit's space)
    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'plan_unit', 'plan_id', 'unit_id')->withTimestamps();
    }

    public function scopeFilter($query, array $filters = [])
    {
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['space_id'])) {
            $query->whereHas('spaces', fn ($query) => $query->where('spaces.id', $filters['space_id']));
        }

        return $query;
    }
}
