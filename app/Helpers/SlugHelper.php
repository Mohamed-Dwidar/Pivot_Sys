<?php

namespace App\Helpers;

use Illuminate\Http\Request;

trait SlugHelper
{
    public function generateSlug($string, $separator = '-')
    {
        // Convert to lowercase
        $slug = strtolower($string);
        // Replace non-alphanumeric characters with the separator
        $slug = preg_replace('/[^a-z0-9]+/', $separator, $slug);
        // Trim the separator from the beginning and end
        $slug = trim($slug, $separator);
        return $slug;
    }
}
