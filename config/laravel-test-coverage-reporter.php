<?php

return [
    //IMPORTANT: The name of the generated Clover Report, be sure to add this one to .gitignore
    //Otherwise it's possible to accidentally add this file to the repository
    'clover_report_filename' => env('TEST_COVERAGE_REPORTER_FILENAME', 'clover-report'),

    //URL of the dedicated service
    'service_url' => env('TEST_COVERAGE_REPORTER_SERVICE_URL', ''),

    // Having this, request body will look like this:
    // [
    //    'repo' => 'test/TestGithubRepository',
    //    'coverage' => 87
    // ]
    // Change these keys if needed
    'payload_repository_key' => 'repo',
    'payload_coverage_percentage_key' => 'coverage'
];
