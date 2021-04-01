<?php

declare(strict_types=1);

namespace Stryber\CoverageReporter;

use Illuminate\Support\ServiceProvider;
use Stryber\CoverageReporter\Commands\GenerateCoverageReportCommand;

class CoverageReporterServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/laravel-test-coverage-reporter.php' => $this->app->configPath('laravel-test-coverage-reporter.php')
        ], 'stryber-coverage-reporter');

        if ($this->app->runningInConsole()) {
            $this->commands([GenerateCoverageReportCommand::class]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-test-coverage-reporter.php', 'laravel-test-coverage-reporter.php');
    }
}
