<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure we're using the test database
        
        
        // Clear any cached config
        $this->app['config']->set('database.default', 'mysql_testing');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
