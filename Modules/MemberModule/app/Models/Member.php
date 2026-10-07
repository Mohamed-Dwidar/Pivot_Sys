<?php

namespace Modules\MemberModule\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\AccountModule\app\Models\Account;
use Modules\CompanyModule\app\Models\Company;
use Modules\JobModule\app\Models\Job;
use Modules\PackageModule\app\Models\Package;
use Modules\ReservationModule\app\Models\Reservation;

class Member extends Model
{
    protected $fillable = [
        'account_id', 'name', 'company_id', 'job_id', 'phone', 'email', 'national_number', 'notes', 'from_where', 'created_by',
    ];

    protected $casts = [
        'national_number' => 'integer',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    // still shows the name when the company / job was deleted later
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class)->withTrashed();
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    // who added the member (users / admins)
    public function creatable(): MorphTo
    {
        return $this->morphTo();
    }

    // "0128 898 9336" => "01288989336" (used when saving and when searching)
    public static function cleanPhone(?string $phone): ?string
    {
        return $phone === null ? null : preg_replace('/\s+/u', '', $phone);
    }

    public function scopeFilter($query, array $filters = [])
    {
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            // phones are saved without spaces, so search them without spaces too
            $phoneSearch = '%' . self::cleanPhone($filters['search']) . '%';
            $query->where(function ($query) use ($search, $phoneSearch) {
                $query->where('name', 'like', $search)
                    ->orWhere('phone', 'like', $phoneSearch)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('national_number', 'like', $search);
            });
        }

        if (!empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }

        if (!empty($filters['job_id'])) {
            $query->where('job_id', $filters['job_id']);
        }

        return $query;
    }
}
