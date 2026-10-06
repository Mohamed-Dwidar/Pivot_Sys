<?php

namespace Modules\SpaceModule\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpaceImage extends Model
{
    protected $fillable = ['space_id', 'name'];

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    // public/uploads/spaces/{space_id}/{name}
    public function getUrlAttribute(): string
    {
        return asset('uploads/spaces/' . $this->space_id . '/' . $this->name);
    }
}
