<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // drop menu options text: "day_use" / "day use" => "Day Use" (Arabic and the existing capitals are kept)
        Str::macro('humanize', function (?string $text): string {
            return ucwords(trim(preg_replace('/\s+/u', ' ', str_replace('_', ' ', (string) $text))));
        });
    }
}
