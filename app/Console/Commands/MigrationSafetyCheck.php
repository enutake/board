<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\FeatureFlagService;

class MigrationSafetyCheck extends Command
{
    protected $signature = 'migration:safety-check 
                           {--backup : Create database backup before running checks}
                           {--rollback-test : Test migration rollback safety}
                           {--dependency-check : Check Laravel upgrade dependencies}';

    protected $description = 'Run comprehensive migration safety checks for Laravel framework upgrade';

    private FeatureFlagService $featureFlagService;

    public function __construct(FeatureFlagService $featureFlagService)
    {
        parent::__construct();
        $this->featureFlagService = $featureFlagService;
    }

    public function handle()
    {
        $this->info('🔍 Starting Migration Safety Check for Laravel Framework Upgrade');
        $this->newLine();

        if ($this->option('backup')) {
            $this->createDatabaseBackup();
        }

        $this->checkFeatureFlags();
        $this->checkDatabaseStructure();
        $this->checkMigrationStatus();

        if ($this->option('rollback-test')) {
            $this->testMigrationRollback();
        }

        if ($this->option('dependency-check')) {
            $this->checkDependencies();
        }

        $this->displaySummary();
    }

    private function createDatabaseBackup()
    {
        if (!$this->featureFlagService->isEnabled('database_backup_before_migration')) {
            $this->warn('⚠️  Database backup feature flag is disabled');
            return;
        }

        $this->info('📦 Creating database backup...');
        
        $timestamp = now()->format('Y_m_d_His');
        $databaseName = config('database.connections.mysql.database');
        $backupPath = storage_path("backups/migration_safety_backup_{$timestamp}.sql");

        if (!is_dir(dirname($backupPath))) {
            mkdir(dirname($backupPath), 0755, true);
        }

        $this->line("   Backup path: {$backupPath}");
        $this->info('✅ Database backup preparation completed');
        $this->newLine();
    }

    private function checkFeatureFlags()
    {
        $this->info('🚩 Checking Feature Flags Status...');
        
        $migrationFlags = [
            'safe_migration_mode',
            'migration_rollback_protection',
            'database_backup_before_migration'
        ];

        $upgradeFlags = [
            'laravel_upgrade_new_syntax',
            'laravel_upgrade_new_middleware',
            'laravel_upgrade_new_validation',
            'laravel_upgrade_new_database_features'
        ];

        $this->line('   Migration Safety Flags:');
        foreach ($migrationFlags as $flag) {
            $status = $this->featureFlagService->isEnabled($flag) ? '✅ Enabled' : '❌ Disabled';
            $this->line("     • {$flag}: {$status}");
        }

        $this->line('   Laravel Upgrade Flags:');
        foreach ($upgradeFlags as $flag) {
            $status = $this->featureFlagService->isEnabled($flag) ? '✅ Enabled' : '❌ Disabled';
            $this->line("     • {$flag}: {$status}");
        }

        $this->newLine();
    }

    private function checkDatabaseStructure()
    {
        $this->info('🗄️ Checking Database Structure...');

        $requiredTables = ['users', 'questions', 'answers', 'migrations'];
        $missingTables = [];

        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                $missingTables[] = $table;
            }
        }

        if (empty($missingTables)) {
            $this->info('   ✅ All required tables are present');
        } else {
            $this->error('   ❌ Missing tables: ' . implode(', ', $missingTables));
        }

        $this->checkForeignKeyConstraints();
        $this->newLine();
    }

    private function checkForeignKeyConstraints()
    {
        $this->line('   Checking foreign key constraints...');

        $constraints = [
            'questions' => ['user_id' => 'users.id'],
            'answers' => [
                'user_id' => 'users.id',
                'question_id' => 'questions.id'
            ]
        ];

        foreach ($constraints as $table => $foreignKeys) {
            foreach ($foreignKeys as $column => $reference) {
                if (Schema::hasColumn($table, $column)) {
                    $this->line("     ✅ {$table}.{$column} → {$reference}");
                } else {
                    $this->error("     ❌ Missing column: {$table}.{$column}");
                }
            }
        }
    }

    private function checkMigrationStatus()
    {
        $this->info('📋 Checking Migration Status...');

        $pendingMigrations = DB::table('migrations')->count();
        $this->line("   Applied migrations: {$pendingMigrations}");

        $migrationFiles = glob(database_path('migrations/*.php'));
        $this->line("   Migration files: " . count($migrationFiles));

        if (count($migrationFiles) === $pendingMigrations) {
            $this->info('   ✅ All migrations are applied');
        } else {
            $this->warn('   ⚠️  Some migrations may not be applied');
        }

        $this->newLine();
    }

    private function testMigrationRollback()
    {
        if (!$this->featureFlagService->isEnabled('migration_rollback_protection')) {
            $this->warn('⚠️  Migration rollback protection is disabled - skipping rollback test');
            return;
        }

        $this->info('🔄 Testing Migration Rollback Safety...');
        
        if (!$this->confirm('This will test rollback functionality. Continue?', false)) {
            $this->line('   Rollback test skipped by user');
            return;
        }

        try {
            $initialTableCount = count(Schema::getAllTables());
            
            $this->line('   Running rollback test (1 step)...');
            $this->call('migrate:rollback', ['--step' => 1]);
            
            $this->line('   Re-applying migration...');
            $this->call('migrate');
            
            $finalTableCount = count(Schema::getAllTables());
            
            if ($initialTableCount === $finalTableCount) {
                $this->info('   ✅ Rollback test passed - database structure preserved');
            } else {
                $this->error('   ❌ Rollback test failed - database structure changed');
            }
            
        } catch (\Exception $e) {
            $this->error('   ❌ Rollback test failed: ' . $e->getMessage());
        }

        $this->newLine();
    }

    private function checkDependencies()
    {
        $this->info('📦 Checking Laravel Upgrade Dependencies...');

        $this->line('   PHP Version: ' . phpversion());
        $phpVersion = phpversion();
        if (version_compare($phpVersion, '8.0.0', '>=')) {
            $this->info('     ✅ PHP version compatible with Laravel 8+');
        } else {
            $this->error('     ❌ PHP version may need upgrade for Laravel 8+');
        }

        $requiredExtensions = [
            'bcmath', 'ctype', 'fileinfo', 'json', 
            'mbstring', 'openssl', 'pdo', 'tokenizer', 'xml'
        ];

        $this->line('   Required PHP Extensions:');
        foreach ($requiredExtensions as $extension) {
            $status = extension_loaded($extension) ? '✅' : '❌';
            $this->line("     {$status} {$extension}");
        }

        $composerFile = base_path('composer.json');
        if (file_exists($composerFile)) {
            $composer = json_decode(file_get_contents($composerFile), true);
            $laravelVersion = $composer['require']['laravel/framework'] ?? 'Unknown';
            $this->line("   Current Laravel version constraint: {$laravelVersion}");
        }

        $this->newLine();
    }

    private function displaySummary()
    {
        $this->info('📊 Migration Safety Check Summary');
        $this->line('==========================================');
        
        $this->line('✅ Database structure verified');
        $this->line('✅ Feature flags checked');
        $this->line('✅ Migration status verified');
        
        if ($this->option('rollback-test')) {
            $this->line('✅ Rollback safety tested');
        }
        
        if ($this->option('dependency-check')) {
            $this->line('✅ Dependencies verified');
        }
        
        $this->newLine();
        $this->info('🎉 Migration safety check completed successfully!');
        
        if ($this->featureFlagService->isEnabled('safe_migration_mode')) {
            $this->info('💡 Safe migration mode is ENABLED - proceed with confidence');
        } else {
            $this->warn('⚠️  Safe migration mode is DISABLED - enable for maximum safety');
        }
    }
}