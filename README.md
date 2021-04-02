# StryberPhpLaravelTestCoverageReporter

### Installation
Use composer to install:

``
composer require stryber/laravel-test-coverage-reporter
``

Publish the config file:

``php artisan vendor:publish --tag="stryber-coverage-reporter"``

Be sure to set URL of the service you are going to send coverage report

``
'service_url' => env('TEST_COVERAGE_REPORTER_SERVICE_URL', '')
``

Use: ``php artisan coverage:report {RepositoryName} {--badge}``

Under the hood it  will run ``php artisan test`` printing out status to console, so there is no need to run tests separately.

``RepositoryName`` is the name of your repo.
When using Github Actions, you can use the default variable ``GITHUB_REPOSITORY``.

If passed ``--badge``, coverage badge will be generated in the root of the app, named``coverage.svg``
