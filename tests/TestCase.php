<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        // PHP 8.3 compatibility for Laravel 7.x
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
        
        parent::setUp();
        
        // Ensure we're using the test database
        // Use SQLite for testing to avoid MySQL connection issues
        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite.database', ':memory:');
        $this->app['config']->set('database.connections.sqlite.foreign_key_constraints', true);
        
        // Force Faker locale for consistent testing
        $this->app['config']->set('app.faker_locale', 'en_US');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
