<?php

namespace Tests;

class TestConfig
{
    /**
     * Common test configuration values
     */
    const DEFAULT_PAGINATION_LIMIT = 10;
    const TEST_PASSWORD = 'password123';
    const TEST_EMAIL_DOMAIN = '@test.example.com';
    
    /**
     * Test database configurations
     */
    const TEST_DB_CONNECTION = 'mysql_testing';
    const TEST_DB_NAME = 'board_testing';
    
    /**
     * Factory defaults
     */
    const FACTORY_DEFAULTS = [
        'users_count' => 5,
        'questions_count' => 10,
        'answers_count' => 20,
    ];

    /**
     * Get test user credentials
     */
    public static function getTestUserCredentials(): array
    {
        return [
            'email' => 'test' . self::TEST_EMAIL_DOMAIN,
            'password' => self::TEST_PASSWORD,
        ];
    }

    /**
     * Get admin user credentials
     */
    public static function getAdminCredentials(): array
    {
        return [
            'email' => 'admin' . self::TEST_EMAIL_DOMAIN,
            'password' => self::TEST_PASSWORD,
        ];
    }

    /**
     * Get test environment settings
     */
    public static function getTestEnvironment(): array
    {
        return [
            'APP_ENV' => 'testing',
            'DB_CONNECTION' => self::TEST_DB_CONNECTION,
            'DB_DATABASE' => self::TEST_DB_NAME,
            'CACHE_DRIVER' => 'array',
            'SESSION_DRIVER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'MAIL_MAILER' => 'array',
        ];
    }

    /**
     * Common assertions for response testing
     */
    public static function getCommonAssertions(): array
    {
        return [
            'success_status' => 200,
            'created_status' => 201,
            'not_found_status' => 404,
            'unauthorized_status' => 401,
            'forbidden_status' => 403,
            'validation_error_status' => 422,
        ];
    }
}