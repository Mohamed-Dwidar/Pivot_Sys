<?php

namespace Modules\JobModule\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AccountModule\app\Models\Account;

class Job extends Model
{
    use SoftDeletes;

    public $table = 'job_positions';

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

    // name in the current locale, falls back to the Arabic name
    public function getNameAttribute()
    {
        $lang = config('app.locale') == 'ar' ? '_ar' : '_en';
        return $this->attributes['name' . $lang] ?: $this->attributes['name_ar'];
    }
}
