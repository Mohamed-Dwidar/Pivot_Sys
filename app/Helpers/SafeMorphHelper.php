<?php

namespace App\Helpers;

use App\Models\Relations\SafeMorphTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * The model's morphTo relations ($this->morphTo()) return null instead of failing when the stored type
 * is empty or a class that doesn't exist (imported data: "Modules\UserModule\Entities\User", "", ...).
 *
 * In the model:
 *     use SafeMorphHelper;
 */
trait SafeMorphHelper
{
    // lazy loading: an unknown class => a relation that finds nothing
    protected function morphInstanceTo($target, $name, $type, $id, $ownerKey)
    {
        if (class_exists(static::getActualClassNameForMorph($target))) {
            return parent::morphInstanceTo($target, $name, $type, $id, $ownerKey);
        }

        return $this->emptyMorphTo($name, $type, $id);
    }

    // lazy loading of an empty type ('') => a relation that finds nothing (null = eager loading, kept as is)
    protected function morphEagerTo($name, $type, $id, $ownerKey)
    {
        if ($this->getAttributeFromArray($type) === '') {
            return $this->emptyMorphTo($name, $type, $id);
        }

        return parent::morphEagerTo($name, $type, $id, $ownerKey);
    }

    protected function newMorphTo(Builder $query, Model $parent, $foreignKey, $ownerKey, $type, $relation)
    {
        return new SafeMorphTo($query, $parent, $foreignKey, $ownerKey, $type, $relation);
    }

    private function emptyMorphTo($name, $type, $id)
    {
        return $this->newMorphTo(
            $this->newQuery()->setEagerLoads([])->whereRaw('0 = 1'), $this, $id, $this->getKeyName(), $type, $name
        );
    }
}
