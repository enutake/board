<?php

// Suppress PHP 8.4 deprecation warnings for Laravel 7.x compatibility
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

// Disable deprecation error handler - more aggressive approach
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // Ignore deprecation warnings from vendor directory
    if (strpos($errfile, '/vendor/') !== false) {
        return true;
    }
    
    // Ignore all deprecation warnings
    if ($errno === E_DEPRECATED || $errno === E_USER_DEPRECATED) {
        return true;
    }
    
    // Check if the error message contains deprecation-related text
    if (stripos($errstr, 'deprecated') !== false || stripos($errstr, 'implicitly marking parameter') !== false) {
        return true;
    }
    
    // Let PHP handle other errors normally
    return false;
}, E_ALL);

// Also set ini directives to suppress deprecation warnings
ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

require __DIR__.'/../vendor/autoload.php';