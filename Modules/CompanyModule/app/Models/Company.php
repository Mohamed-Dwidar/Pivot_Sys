<?php

namespace Modules\CompanyModule\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AccountModule\app\Models\Account;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = ['account_id', 'name_ar', 'name_en'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function scopeFilter($query, array $filters = [])
    {
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($query) use ($search) {
                $query->where('name_ar', 'like', $search)->orWhere('name_en', 'like', $search);
            });
        }

        return $query;
    }
}
