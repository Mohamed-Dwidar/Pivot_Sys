<?php

namespace Modules\UnitModule\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// shared by all accounts, managed by the admin
class Color extends Model
{
    protected $fillable = ['name', 'value'];

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function scopeFilter($query, array $filters = [])
    {
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query;
    }
}
