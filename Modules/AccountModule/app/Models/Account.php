<?php

namespace Modules\AccountModule\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\UserModule\app\Contracts\Userable;
use Modules\UserModule\app\Models\User;

class Account extends Model implements Userable
{
    use SoftDeletes;

    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_REJECTED = 'rejected';

    // filter value for the soft deleted accounts list
    const FILTER_DELETED = 'deleted';

    const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_INACTIVE => 'Inactive',
        self::STATUS_REJECTED => 'Rejected',
    ];

    // optional leading +, then 8-15 digits (e.g. 01012345678 or +201012345678)
    const PHONE_REGEX = '/^\+?[0-9]{8,15}$/';

    // fields the account owner fills after approval to complete his profile
    const PROFILE_FIELDS = ['name_en', 'address', 'description_ar', 'description_en', 'logo'];

    protected $fillable = [
        'name_ar', 'name_en', 'phone', 'address', 'description_ar', 'description_en', 'logo', 'status', 'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    // the account login (email, password)
    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'userable');
    }

    public function scopeFilter($query, array $filters = [])
    {
        if (($filters['status'] ?? null) == self::FILTER_DELETED) {
            $query->onlyTrashed();
        } elseif (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($query) use ($search) {
                $query->where('name_ar', 'like', $search)
                    ->orWhere('name_en', 'like', $search)
                    ->orWhere('phone', 'like', $search)
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('email', 'like', $search);
                    });
            });
        }

        return $query;
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('uploads/accounts/' . $this->id . '/' . $this->logo) : null;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    // percentage of the profile fields that are filled
    public function profileCompletion(): int
    {
        $filled = collect(self::PROFILE_FIELDS)->filter(fn ($field) => filled($this->$field))->count();
        return (int) round($filled / count(self::PROFILE_FIELDS) * 100);
    }

    /* ---------- Userable ---------- */

    public function loginError(): ?string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => null,
            self::STATUS_PENDING => 'Your account is waiting for the admin approval.',
            self::STATUS_REJECTED => 'Your registration request has been rejected.',
            default => 'Your account is inactive, please contact the administrator.',
        };
    }

    public function homeRoute(): string
    {
        return 'account.dashboard';
    }

    public function layout(): string
    {
        return 'layoutmodule::account.main';
    }

    public function displayName(): string
    {
        return $this->name_ar;
    }
}
