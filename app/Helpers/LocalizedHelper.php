<?php

namespace App\Helpers;

/**
 * Fields saved twice ({field}_ar / {field}_en) are read in the active language: $model->name, $model->description ...
 * If the active language value is empty, the other language is used.
 *
 * In the model:
 *     use LocalizedHelper;
 *     protected array $localizedFields = ['name', 'description'];
 */
trait LocalizedHelper
{
    // the active language: ar or en (any other locale is treated as en)
    public static function activeLang(): string
    {
        return app()->getLocale() === 'ar' ? 'ar' : 'en';
    }

    // e.g. name -> name_en (to order / search by the active language column)
    public static function localizedColumn(string $field): string
    {
        return $field . '_' . static::activeLang();
    }

    // $model->name => name_en (or name_ar when name_en is empty)
    public function getAttribute($key)
    {
        if (in_array($key, $this->localizedFields ?? [], true)) {
            return $this->localized($key);
        }

        return parent::getAttribute($key);
    }

    // value of $field in $lang (default: the active language), falls back to the other language
    public function localized(string $field, ?string $lang = null): ?string
    {
        $lang = $lang === 'ar' || $lang === 'en' ? $lang : static::activeLang();
        $value = parent::getAttribute($field . '_' . $lang);

        return filled($value) ? $value : parent::getAttribute($field . '_' . ($lang === 'ar' ? 'en' : 'ar'));
    }

    // orders by the shown value (the active language, or the other language when it is empty), case insensitive
    public function scopeOrderByLocalized($query, string $field = 'name', string $direction = 'asc')
    {
        $lang = static::activeLang();
        $other = $lang === 'ar' ? 'en' : 'ar';
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $query->orderByRaw("LOWER(COALESCE(NULLIF({$field}_{$lang}, ''), {$field}_{$other})) {$direction}");
    }
}
