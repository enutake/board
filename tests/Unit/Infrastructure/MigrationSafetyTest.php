<?php

namespace Tests\Unit\Infrastructure;

use Tests\TestCase;
use Tests\TestHelpers;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

class MigrationSafetyTest extends TestCase
{
    use TestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_migration_rollback_safety()
    {
        $this->artisan('migrate:rollback')->assertExitCode(0);
        
        $this->artisan('migrate')->assertExitCode(0);
        
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('questions'));
        $this->assertTrue(Schema::hasTable('answers'));
    }

    public function test_migration_refresh_preserves_structure()
    {
        $originalTables = $this->getTableList();
        
        $this->artisan('migrate:refresh')->assertExitCode(0);
        
        $refreshedTables = $this->getTableList();
        
        $this->assertEquals($originalTables, $refreshedTables);
    }

    public function test_foreign_key_constraints_integrity()
    {
        $user = $this->createUser();
        $question = $this->createQuestion(['user_id' => $user->id]);
        $answer = $this->createAnswer([
            'user_id' => $user->id,
            'question_id' => $question->id
        ]);

        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'user_id' => $user->id,
            'question_id' => $question->id
        ]);

        try {
            DB::table('answers')->insert([
                'content' => 'Test content',
                'user_id' => 99999,
                'question_id' => $question->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $this->fail('Foreign key constraint should have prevented this insert');
        } catch (\Exception $e) {
            $this->assertStringContainsString('foreign key constraint', strtolower($e->getMessage()));
        }
    }

    public function test_safe_column_addition()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->integer('view_count')->default(0)->after('content');
        });

        $this->assertTrue(Schema::hasColumn('questions', 'view_count'));

        $question = $this->createQuestion();
        $this->assertEquals(0, $question->fresh()->view_count);

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('view_count');
        });
    }

    public function test_migration_data_preservation()
    {
        $user = $this->createUser(['name' => 'Test User']);
        $question = $this->createQuestion([
            'title' => 'Test Question',
            'content' => 'Test content',
            'user_id' => $user->id
        ]);

        $this->artisan('migrate:rollback', ['--step' => 1])->assertExitCode(0);
        $this->artisan('migrate')->assertExitCode(0);

        $this->assertDatabaseHas('users', ['name' => 'Test User']);
        $this->assertDatabaseHas('questions', ['title' => 'Test Question']);
    }

    private function getTableList(): array
    {
        $tables = [];
        $query = "SHOW TABLES";
        $result = DB::select($query);
        
        foreach ($result as $table) {
            $tableArray = (array) $table;
            $tables[] = array_values($tableArray)[0];
        }
        
        sort($tables);
        return $tables;
    }
}