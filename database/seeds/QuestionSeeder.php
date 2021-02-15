<?php

use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('questions')->insert([
            [
                'title'      => '最近買ってよかったものを教えてください！',
                'content'    => 'ボーナスで何か買おうと思います！ここ数か月で買ってよかったもの教えてください！',
                'user_id'    => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => '都内のおいしいラーメン屋教えてください',
                'content'    => '東京の有名・無名関係なくあなたが思う一番おいしいラーメン屋さんを教えて！',
                'user_id'    => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'フォートナイトの上達方法教えてください',
                'content'    => '建築全然できないから勝てません！うまくなる方法、これやってうまくなったという練習方法教えてください！！',
                'user_id'    => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
