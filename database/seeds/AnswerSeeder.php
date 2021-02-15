<?php

use Illuminate\Database\Seeder;

class AnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('answers')->insert([
            [
                'content'     => 'AmazonのタブレットのFireHD8をプライムデーで買いましたがめっちゃよかったですよ',
                'user_id'     => '2',
                'question_id' => '1',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }
}
