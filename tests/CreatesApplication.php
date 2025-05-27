<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication(): \Illuminate\Foundation\Application
    {
        // Use custom bootstrap for tests to handle PHP 8.4 compatibility
        $app = require __DIR__.'/bootstrap-test.php';

        // Temporarily suppress errors during bootstrap
        $originalErrorReporting = error_reporting();
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
        
        // Set a custom error handler for the bootstrap process
        $previousHandler = set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            // Ignore all deprecation warnings during bootstrap
            if ($errno === E_DEPRECATED || $errno === E_USER_DEPRECATED ||
                strpos($errstr, 'deprecated') !== false ||
                strpos($errstr, 'Implicitly marking parameter') !== false) {
                return true;
            }
            return false;
        }, E_ALL);

        try {
            $app->make(Kernel::class)->bootstrap();
        } finally {
            // Restore original error reporting and handler
            error_reporting($originalErrorReporting);
            if ($previousHandler) {
                set_error_handler($previousHandler);
            } else {
                restore_error_handler();
            }
        }

        return $app;
    }
}
