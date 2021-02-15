<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name'  => 'たけゆき',
                'email' => 'takeyuki@gmail.com',
                'password' => 'pass',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'  => 'テストさん',
                'email' => 'test@gmail.com',
                'password' => 'word',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
