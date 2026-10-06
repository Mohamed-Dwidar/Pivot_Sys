<?php

namespace Modules\SpaceModule\app\Models;

use App\Helpers\LocalizedHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AccountModule\app\Models\Account;
use Modules\PlanModule\app\Models\Plan;
use Modules\UnitModule\app\Models\Unit;

class Space extends Model {
    use LocalizedHelper, SoftDeletes;

    // read in the active language: $model->name (see App\Helpers\LocalizedHelper)
    protected array $localizedFields = ['name'];

    protected $fillable = ['account_id', 'name_ar', 'name_en', 'is_active', 'icon_id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function account(): BelongsTo {
        return $this->belongsTo(Account::class);
    }

    public function images(): HasMany {
        return $this->hasMany(SpaceImage::class);
    }

    public function units(): HasMany {
        return $this->hasMany(Unit::class);
    }

    // many to many (pivot: space_subscription_type)
    public function subscriptionTypes(): BelongsToMany {
        return $this->belongsToMany(SubscriptionType::class, 'space_subscription_type', 'space_id', 'subscription_type_id')->withTimestamps();
    }

    // many to many (pivot: plan_space), the units choose from these plans
    public function plans(): BelongsToMany {
        return $this->belongsToMany(Plan::class, 'plan_space', 'space_id', 'plan_id')->withTimestamps();
    }

    public function scopeFilter($query, array $filters = []) {
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($query) use ($search) {
                $query->where('name_ar', 'like', $search)->orWhere('name_en', 'like', $search);
            });
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['subscription_type_id'])) {
            $query->whereHas('subscriptionTypes', fn($query) => $query->where('subscription_types.id', $filters['subscription_type_id']));
        }

        return $query;
    }
}
