<?php

namespace App\Providers;

use App\Services\AI\ClaudeService;
use App\Services\ExportService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ClaudeService::class);
        $this->app->singleton(ExportService::class);
    }

    public function boot(): void
    {
        //
    }
}
