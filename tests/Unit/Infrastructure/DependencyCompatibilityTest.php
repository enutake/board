<?php

namespace Tests\Unit\Infrastructure;

use Tests\TestCase;
use Tests\TestHelpers;
use Illuminate\Foundation\Application;

class DependencyCompatibilityTest extends TestCase
{
    use TestHelpers;

    public function test_laravel_framework_version()
    {
        $version = app()->version();
        
        $this->assertStringStartsWith('Laravel Framework', $version);
        
        $numericVersion = $this->extractNumericVersion($version);
        $this->assertGreaterThanOrEqual(7.0, $numericVersion);
    }

    public function test_php_version_compatibility()
    {
        $phpVersion = phpversion();
        
        $this->assertGreaterThanOrEqual('8.0.0', $phpVersion, 
            'PHP version should be 8.0 or higher for Laravel upgrade compatibility');
    }

    public function test_required_php_extensions()
    {
        $requiredExtensions = [
            'bcmath',
            'ctype',
            'fileinfo',
            'json',
            'mbstring',
            'openssl',
            'pdo',
            'tokenizer',
            'xml'
        ];

        foreach ($requiredExtensions as $extension) {
            $this->assertTrue(
                extension_loaded($extension),
                "Required PHP extension '{$extension}' is not loaded"
            );
        }
    }

    public function test_composer_dependencies_compatibility()
    {
        $composerPath = base_path('composer.json');
        $this->assertFileExists($composerPath);
        
        $composer = json_decode(file_get_contents($composerPath), true);
        
        $this->assertArrayHasKey('require', $composer);
        
        $requiredPackages = [
            'laravel/framework',
            'guzzlehttp/guzzle',
        ];

        foreach ($requiredPackages as $package) {
            $this->assertArrayHasKey($package, $composer['require'], 
                "Required package '{$package}' not found in composer.json");
        }
    }

    public function test_database_driver_compatibility()
    {
        $connection = config('database.default');
        $this->assertNotEmpty($connection);
        
        $driver = config("database.connections.{$connection}.driver");
        
        $supportedDrivers = ['mysql', 'pgsql', 'sqlite', 'sqlsrv'];
        $this->assertContains($driver, $supportedDrivers, 
            "Database driver '{$driver}' may not be compatible with Laravel upgrade");
    }

    public function test_cache_driver_compatibility()
    {
        $cacheDriver = config('cache.default');
        
        $supportedDrivers = ['array', 'file', 'database', 'redis', 'memcached'];
        $this->assertContains($cacheDriver, $supportedDrivers,
            "Cache driver '{$cacheDriver}' may not be compatible");
    }

    public function test_session_driver_compatibility()
    {
        $sessionDriver = config('session.driver');
        
        $supportedDrivers = ['file', 'cookie', 'database', 'redis', 'array'];
        $this->assertContains($sessionDriver, $supportedDrivers,
            "Session driver '{$sessionDriver}' may not be compatible");
    }

    public function test_queue_driver_compatibility()
    {
        $queueDriver = config('queue.default');
        
        $supportedDrivers = ['sync', 'database', 'redis', 'sqs'];
        $this->assertContains($queueDriver, $supportedDrivers,
            "Queue driver '{$queueDriver}' may not be compatible");
    }

    public function test_middleware_compatibility()
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        
        $this->assertInstanceOf(\App\Http\Kernel::class, $kernel);
        
        $middleware = $kernel->getMiddleware();
        $this->assertIsArray($middleware);
    }

    public function test_service_provider_compatibility()
    {
        $providers = config('app.providers');
        
        $coreProviders = [
            'Illuminate\Auth\AuthServiceProvider',
            'Illuminate\Broadcasting\BroadcastServiceProvider',
            'Illuminate\Bus\BusServiceProvider',
            'Illuminate\Cache\CacheServiceProvider',
            'Illuminate\Database\DatabaseServiceProvider',
        ];

        foreach ($coreProviders as $provider) {
            $this->assertContains($provider, $providers,
                "Core service provider '{$provider}' not found");
        }
    }

    public function test_artisan_commands_availability()
    {
        $commands = [
            'migrate',
            'migrate:rollback',
            'migrate:refresh',
            'migrate:status',
            'cache:clear',
            'config:cache',
            'route:cache'
        ];

        foreach ($commands as $command) {
            $exitCode = $this->artisan($command, ['--help' => true])->run();
            $this->assertEquals(0, $exitCode, 
                "Artisan command '{$command}' is not available or has issues");
        }
    }

    public function test_environment_configuration()
    {
        $this->assertNotEmpty(config('app.key'), 'Application key must be set');
        $this->assertNotEmpty(config('app.name'), 'Application name must be set');
        
        $environment = config('app.env');
        $this->assertContains($environment, ['local', 'production', 'testing', 'staging']);
    }

    private function extractNumericVersion(string $versionString): float
    {
        preg_match('/(\d+\.\d+)/', $versionString, $matches);
        return isset($matches[1]) ? (float) $matches[1] : 0.0;
    }

    public function test_deprecated_functions_usage()
    {
        $deprecatedFunctions = [
            'each',
            'create_function',
            'mysql_connect',
            'split',
            'ereg'
        ];

        foreach ($deprecatedFunctions as $function) {
            $this->assertFalse(
                function_exists($function) && !extension_loaded('standard'),
                "Deprecated function '{$function}' should not be used"
            );
        }
    }

    public function test_memory_limit_adequacy()
    {
        $memoryLimit = ini_get('memory_limit');
        
        if ($memoryLimit !== '-1') {
            $memoryBytes = $this->convertToBytes($memoryLimit);
            $minimumRequired = 128 * 1024 * 1024; // 128MB
            
            $this->assertGreaterThanOrEqual($minimumRequired, $memoryBytes,
                'Memory limit should be at least 128MB for Laravel operations');
        }
    }

    private function convertToBytes(string $memoryLimit): int
    {
        $unit = strtolower(substr($memoryLimit, -1));
        $value = (int) substr($memoryLimit, 0, -1);
        
        switch ($unit) {
            case 'g': return $value * 1024 * 1024 * 1024;
            case 'm': return $value * 1024 * 1024;
            case 'k': return $value * 1024;
            default: return (int) $memoryLimit;
        }
    }
}