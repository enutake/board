<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class SqliteConnectionTest extends TestCase
{
    public function test_database_connection_is_sqlite()
    {
        $connection = config('database.default');
        $this->assertEquals('sqlite', $connection);
    }

    public function test_database_is_in_memory()
    {
        $database = config('database.connections.sqlite.database');
        $this->assertEquals(':memory:', $database);
    }

    public function test_can_create_and_query_table()
    {
        // Create a test table
        DB::statement('CREATE TABLE test_table (id INTEGER PRIMARY KEY, name TEXT)');
        
        // Insert data
        DB::table('test_table')->insert(['name' => 'Test']);
        
        // Query data
        $result = DB::table('test_table')->where('name', 'Test')->first();
        
        $this->assertNotNull($result);
        $this->assertEquals('Test', $result->name);
    }

    public function test_migrations_can_run()
    {
        // This will test if migrations can run on SQLite
        $this->artisan('migrate:fresh');
        
        // Check if users table exists
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('users'));
    }
}