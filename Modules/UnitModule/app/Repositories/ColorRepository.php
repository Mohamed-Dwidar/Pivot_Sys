<?php

namespace Modules\UnitModule\app\Repositories;

use Modules\UnitModule\app\Models\Color;
use Prettus\Repository\Eloquent\BaseRepository;

class ColorRepository extends BaseRepository
{
    public function model()
    {
        return Color::class;
    }

    public function query()
    {
        return Color::withCount(['units' => fn ($query) => $query->withTrashed()]);
    }
}
