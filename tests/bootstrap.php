<?php

// Suppress PHP 8.4 deprecation warnings for Laravel 7.x compatibility
error_reporting(E_ALL & ~E_DEPRECATED);

// Disable deprecation error handler
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // Ignore deprecation warnings
    if ($errno === E_DEPRECATED || $errno === E_USER_DEPRECATED) {
        return true;
    }
    
    // Let PHP handle other errors normally
    return false;
});

require __DIR__.'/../vendor/autoload.php';