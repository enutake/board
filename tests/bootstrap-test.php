<?php

// Suppress PHP 8.4 deprecation warnings for Laravel 7.x compatibility
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', '1');

// Override error handler to ignore deprecation warnings
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // Ignore deprecation warnings
    if ($errno === E_DEPRECATED || $errno === E_USER_DEPRECATED) {
        return true;
    }
    
    // Check if the error message contains deprecation-related text
    if (strpos($errstr, 'Implicitly marking parameter') !== false ||
        strpos($errstr, 'should either be compatible with') !== false ||
        strpos($errstr, 'ReturnTypeWillChange') !== false) {
        return true;
    }
    
    // Let PHP handle other errors normally
    return false;
}, E_ALL);

// Load the application
$app = require __DIR__.'/../bootstrap/app.php';

// Override the exception handler for tests
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    function ($app) {
        return new class($app) extends \App\Exceptions\Handler {
            public function report(\Throwable $e)
            {
                // Ignore deprecation errors in tests
                if ($e instanceof \ErrorException &&
                    (strpos($e->getMessage(), 'Implicitly marking parameter') !== false ||
                     strpos($e->getMessage(), 'should either be compatible with') !== false)) {
                    return;
                }
                parent::report($e);
            }
            
            public function shouldReport(\Throwable $e)
            {
                // Don't report deprecation errors
                if ($e instanceof \ErrorException &&
                    (strpos($e->getMessage(), 'Implicitly marking parameter') !== false ||
                     strpos($e->getMessage(), 'should either be compatible with') !== false)) {
                    return false;
                }
                return parent::shouldReport($e);
            }
        };
    }
);

return $app;