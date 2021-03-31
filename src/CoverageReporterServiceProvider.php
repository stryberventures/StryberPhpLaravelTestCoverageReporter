<?php

declare(strict_types=1);

namespace Stryber\CoverageReporter;

use Illuminate\Support\ServiceProvider;

class CoverageReporterServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/laravel-test-coverage-reporter.php' => config_path('laravel-test-coverage-reporter.php')
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-test-coverage-reporter.php', 'laravel-test-coverage-reporter.php');
    }
}
