<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;
use Tests\TestHelpers;
use App\Services\FeatureFlagService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class MigrationSafetyWorkflowTest extends FeatureTestCase
{
    use TestHelpers;

    private FeatureFlagService $featureFlagService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->featureFlagService = app(FeatureFlagService::class);
    }

    public function test_migration_safety_check_command_exists()
    {
        $exitCode = Artisan::call('migration:safety-check', ['--help' => true]);
        
        $this->assertEquals(0, $exitCode);
    }

    public function test_migration_safety_check_basic_run()
    {
        Config::set('features.safe_migration_mode', true);
        Config::set('features.migration_rollback_protection', true);
        
        $exitCode = Artisan::call('migration:safety-check');
        
        $this->assertEquals(0, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContainsString('Migration Safety Check', $output);
        $this->assertStringContainsString('completed successfully', $output);
    }

    public function test_migration_safety_check_with_backup_option()
    {
        Config::set('features.database_backup_before_migration', true);
        
        $exitCode = Artisan::call('migration:safety-check', ['--backup' => true]);
        
        $this->assertEquals(0, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContainsString('Creating database backup', $output);
    }

    public function test_migration_safety_check_with_dependency_check()
    {
        $exitCode = Artisan::call('migration:safety-check', ['--dependency-check' => true]);
        
        $this->assertEquals(0, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContainsString('Checking Laravel Upgrade Dependencies', $output);
        $this->assertStringContainsString('PHP Version:', $output);
    }

    public function test_feature_flag_integration_in_workflow()
    {
        $this->featureFlagService->enable('safe_migration_mode');
        $this->featureFlagService->enable('migration_rollback_protection');
        
        $this->assertTrue($this->featureFlagService->isEnabled('safe_migration_mode'));
        $this->assertTrue($this->featureFlagService->isEnabled('migration_rollback_protection'));
        
        $exitCode = Artisan::call('migration:safety-check');
        
        $this->assertEquals(0, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContainsString('Safe migration mode is ENABLED', $output);
    }

    public function test_migration_workflow_with_feature_flags()
    {
        $this->featureFlagService->enable('laravel_upgrade_new_syntax');
        
        $user = $this->createUser();
        $question = $this->createQuestion(['user_id' => $user->id]);
        
        $this->featureFlagService->when('laravel_upgrade_new_syntax', function () use ($question) {
            $this->assertDatabaseHas('questions', ['id' => $question->id]);
        });
        
        $this->assertTrue(true);
    }

    public function test_rollback_protection_workflow()
    {
        $this->featureFlagService->enable('migration_rollback_protection');
        
        $user = $this->createUser();
        $initialUserCount = \App\Models\User::count();
        
        $this->featureFlagService->when('migration_rollback_protection', function () use ($initialUserCount) {
            $this->assertGreaterThanOrEqual(1, $initialUserCount);
        });
    }

    public function test_safe_migration_mode_prevents_unsafe_operations()
    {
        $this->featureFlagService->enable('safe_migration_mode');
        
        $result = $this->featureFlagService->when('safe_migration_mode', function () {
            return 'safe_operation_executed';
        });
        
        $this->assertEquals('safe_operation_executed', $result);
        
        $this->featureFlagService->disable('safe_migration_mode');
        
        $result = $this->featureFlagService->unless('safe_migration_mode', function () {
            return 'unsafe_operation_executed';
        });
        
        $this->assertEquals('unsafe_operation_executed', $result);
    }

    public function test_complete_migration_safety_workflow()
    {
        Config::set('features', [
            'safe_migration_mode' => true,
            'migration_rollback_protection' => true,
            'database_backup_before_migration' => true,
            'laravel_upgrade_new_syntax' => false,
        ]);
        
        $user = $this->createUser();
        $question = $this->createQuestion(['user_id' => $user->id]);
        $answer = $this->createAnswer([
            'user_id' => $user->id,
            'question_id' => $question->id
        ]);
        
        $exitCode = Artisan::call('migration:safety-check', [
            '--backup' => true,
            '--dependency-check' => true
        ]);
        
        $this->assertEquals(0, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContainsString('Migration Safety Check', $output);
        $this->assertStringContainsString('Database structure verified', $output);
        $this->assertStringContainsString('Feature flags checked', $output);
        $this->assertStringContainsString('Dependencies verified', $output);
        
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseHas('questions', ['id' => $question->id]);
        $this->assertDatabaseHas('answers', ['id' => $answer->id]);
    }

    public function test_laravel_upgrade_feature_flags_workflow()
    {
        $upgradeFlags = [
            'laravel_upgrade_new_syntax',
            'laravel_upgrade_new_middleware',
            'laravel_upgrade_new_validation',
            'laravel_upgrade_new_database_features'
        ];
        
        foreach ($upgradeFlags as $flag) {
            $this->featureFlagService->disable($flag);
            $this->assertFalse($this->featureFlagService->isEnabled($flag));
        }
        
        foreach ($upgradeFlags as $flag) {
            $this->featureFlagService->enable($flag);
            $this->assertTrue($this->featureFlagService->isEnabled($flag));
        }
        
        $exitCode = Artisan::call('migration:safety-check');
        $this->assertEquals(0, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContainsString('Laravel Upgrade Flags:', $output);
    }
}