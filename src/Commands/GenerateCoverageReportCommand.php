<?php

declare(strict_types=1);

namespace Stryber\CoverageReporter\Commands;

use Illuminate\Support\Facades\App;
use InvalidArgumentException;
use SimpleXMLElement;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Process\Process;

class GenerateCoverageReportCommand extends Command
{
    protected $signature = 'coverage:report {repository} {--badge}';

    protected $description = 'Generates test coverage report and sends it to the dedicated service';

    public function handle(): void
    {
        $serviceURL = Config::get('laravel-test-coverage-reporter.service_url');

        if (trim($serviceURL) === '') {
            $this->error('Service URL was not specified, check .env and/or laravel-test-coverage-reporter.php config file');
            return;
        }

        // Generate PHPUnit clover report first
        $cloverReportFilename = Config::get('laravel-test-coverage-reporter.clover_report_filename');

        $process = new Process(['php', 'artisan', 'test', '--coverage-clover', $cloverReportFilename]);
        $process->run();

        $this->info($process->getOutput());

        $repositoryName = $this->argument('repository');

        $coverage = $this->parseCloverCoverageReport();

        $httpClient = new Client();

        try {
            $response = $httpClient->request('POST', $serviceURL, [
                'json' => $this->composePayload($repositoryName, $coverage)
            ]);
            $this->info('Response Status: ' . $response->getStatusCode());
            $this->info('Response Body: ' . $response->getBody());
        } catch (GuzzleException $e) {
            $this->error('Failed to send POST request: ' . $e->getMessage());
        }

        if ($this->option('--badge')) {
            try {
                $this->generateCoverageBadge($coverage);
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
        }
    }

    protected function composePayload(string $repositoryName, int $coverage): array
    {
        $repositoryNameKey = Config::get('laravel-test-coverage-reporter.payload_repository_key');
        $coverageKey = Config::get('laravel-test-coverage-reporter.payload_coverage_percentage_key');

        return [
            $repositoryNameKey => $repositoryName,
            $coverageKey => $coverage
        ];
    }

    private function parseCloverCoverageReport(): int
    {
        $cloverPath = App::basePath() . '/' . Config::get('laravel-test-coverage-reporter.clover_report_filename');

        if (!file_exists($cloverPath)) {
            throw new InvalidArgumentException('No Clover Coverage was generated');
        }

        $xml = new SimpleXMLElement(file_get_contents($cloverPath));
        $metrics = $xml->xpath('//metrics');
        $totalElements = 0;
        $checkedElements = 0;

        foreach ($metrics as $metric) {
            $totalElements += (int)$metric['elements'];
            $checkedElements += (int)$metric['coveredelements'];
        }

        return (int)(($totalElements === 0) ? 0 : ($checkedElements / $totalElements) * 100);
    }

    private function generateCoverageBadge(int $coverage): void
    {
        $template = file_get_contents(__DIR__ . '/template/flat.svg');

        $color = '#a4a61d';      // Default Gray

        if ($coverage < 40) {
            $color = '#e05d44';  // Red
        } elseif ($coverage < 60) {
            $color = '#fe7d37';  // Orange
        } elseif ($coverage < 75) {
            $color = '#dfb317';  // Yellow
        } elseif ($coverage < 90) {
            $color = '#a4a61d';  // Yellow-Green
        } elseif ($coverage < 95) {
            $color = '#97CA00';  // Green
        } elseif ($coverage <= 100) {
            $color = '#4c1';     // Bright Green
        }

        $template = str_replace('{{ total }}', $coverage, $template);
        $template = str_replace('{{ color }}', $color, $template);

        file_put_contents(App::basePath() . '/coverage.svg', $template);

        $this->info('Badge generated: coverage.svg');
    }
}
