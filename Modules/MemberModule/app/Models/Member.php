<?php

namespace Modules\MemberModule\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\AccountModule\app\Models\Account;
use Modules\CompanyModule\app\Models\Company;
use Modules\JobModule\app\Models\Job;

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

    // who added the member (users / admins)
    public function creatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeFilter($query, array $filters = [])
    {
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', $search)
                    ->orWhere('phone', 'like', $search)
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
