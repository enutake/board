<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        // Suppress PHP 8.4 deprecation warnings for Laravel 7.x compatibility
        error_reporting(E_ALL & ~E_DEPRECATED);
        
        parent::setUp();
        
        // Ensure we're using the test database
        // Use SQLite for testing to avoid MySQL connection issues
        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite.database', ':memory:');
        $this->app['config']->set('database.connections.sqlite.foreign_key_constraints', true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
