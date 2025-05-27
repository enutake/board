<?php

// PHP 8.3 compatibility for Laravel 7.x
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

// Enhanced error handler for PHP 8.3 compatibility issues
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // Ignore vendor deprecation warnings
    if (strpos($errfile, '/vendor/') !== false) {
        return true;
    }
    
    // Ignore deprecation warnings
    if ($errno === E_DEPRECATED || $errno === E_USER_DEPRECATED) {
        return true;
    }
    
    // Specific PHP 8.3 compatibility patterns to ignore
    $ignorePatterns = [
        'deprecated',
        'implicitly marking parameter',
        'Automatic conversion of false to array is deprecated',
        'Creation of dynamic property',
        'Return type should either be',
        'Using ${var} in strings is deprecated'
    ];
    
    foreach ($ignorePatterns as $pattern) {
        if (stripos($errstr, $pattern) !== false) {
            return true;
        }
    }
    
    return false;
}, E_ALL);

// Set ini directives for PHP 8.3 compatibility
ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', 0);
ini_set('log_errors', 0);

require __DIR__.'/../vendor/autoload.php';