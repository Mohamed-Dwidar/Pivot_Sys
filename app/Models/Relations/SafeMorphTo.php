<?php

namespace App\Models\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * MorphTo that skips types whose class doesn't exist (e.g. imported old class names) when eager loading:
 * those rows get null instead of a "Class not found" error. Used through App\Helpers\SafeMorphHelper.
 */
class SafeMorphTo extends MorphTo
{
    protected function getResultsByType($type)
    {
        if (! class_exists(static::getMorphedModel($type) ?? $type)) {
            return new Collection;
        }

        return parent::getResultsByType($type);
    }
}
