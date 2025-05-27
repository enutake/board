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

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
