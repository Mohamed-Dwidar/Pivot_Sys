<?php

namespace Modules\UnitModule\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitImage extends Model
{
    protected $fillable = ['unit_id', 'name'];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    // public/uploads/units/{unit_id}/{name}
    public function getUrlAttribute(): string
    {
        return asset('uploads/units/' . $this->unit_id . '/' . $this->name);
    }
}
