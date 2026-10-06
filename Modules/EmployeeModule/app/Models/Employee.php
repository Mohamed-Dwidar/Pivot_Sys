<?php

namespace Modules\EmployeeModule\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AccountModule\app\Models\Account;
use Modules\UserModule\app\Contracts\Userable;
use Modules\UserModule\app\Models\User;

// an employee of an account; he logs in with the users table (morph "userable"), the account manages his profile
class Employee extends Model implements Userable
{
    use SoftDeletes;

    const GENDERS = [
        'male' => 'Male',
        'female' => 'Female',
    ];

    protected $fillable = [
        'account_id', 'name', 'position_id', 'shift_id', 'phone', 'another_phone', 'gender', 'birth_date', 'address', 'image',
        'salary', 'educational_qualification', 'join_date', 'leave_date',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'join_date' => 'date',
        'leave_date' => 'date',
        'salary' => 'float',
    ];

    // the employee login (email, password)
    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'userable');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(EmployeePosition::class, 'position_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(EmployeeShift::class, 'shift_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(EmployeeAttachment::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('uploads/employees/' . $this->id . '/' . $this->image) : null;
    }

    public function getGenderLabelAttribute(): string
    {
        return self::GENDERS[$this->gender] ?? '-';
    }

    // left = has a leave date that has come
    public function hasLeft(): bool
    {
        return $this->leave_date !== null && $this->leave_date->lte(today());
    }

    public function scopeFilter($query, array $filters = [])
    {
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', $search)
                    ->orWhere('phone', 'like', str_replace(' ', '', $search))
                    ->orWhere('another_phone', 'like', str_replace(' ', '', $search))
                    ->orWhereHas('user', fn ($query) => $query->where('email', 'like', $search));
            });
        }

        if (!empty($filters['position_id'])) {
            $query->where('position_id', $filters['position_id']);
        }

        if (!empty($filters['shift_id'])) {
            $query->where('shift_id', $filters['shift_id']);
        }

        if (($filters['status'] ?? '') === 'working') {
            $query->where(fn ($query) => $query->whereNull('leave_date')->orWhereDate('leave_date', '>', today()));
        } elseif (($filters['status'] ?? '') === 'left') {
            $query->whereDate('leave_date', '<=', today());
        }

        return $query;
    }

    // Userable: the employee can log in while his company account is active and he has not left
    public function loginError(): ?string
    {
        if (!$this->account || $this->account->status !== Account::STATUS_ACTIVE) {
            return 'Your company account is not active, please contact your company.';
        }
        if ($this->hasLeft()) {
            return 'Your account is closed, please contact your company.';
        }
        return null;
    }

    public function homeRoute(): string
    {
        return 'employee.dashboard';
    }

    public function layout(): string
    {
        return 'layoutmodule::employee.main';
    }

    public function displayName(): string
    {
        return $this->name;
    }

    // the email is managed by the account, the employee changes his password only
    public function canChangeEmail(): bool
    {
        return false;
    }
}
