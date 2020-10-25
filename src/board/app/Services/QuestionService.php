<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Support\Facades\DB;

class QuestionService
{
    /**
     * トップページの質問一覧を取得する
     */
    public function getQuestionListForTop()
    {
        $toppageQuestionCount = config('page.toppage.questions.count', 10);
        $questionList = Question::take($toppageQuestionCount)->get();
        return $questionList;
    }

    /**
     * 質問詳細を取得する
     */
    public function getQuestionDetail($questionId)
    {
        $questionDetail = Question::find($questionId);
        return $questionDetail;
    }

    /**
     * 質問投稿データを保存する
     */
    public function storeQuestion($title, $content, $userId)
    {
        return DB::transaction(function () use ($title, $content, $userId){            
            $result = Question::create(
                [
                    'title'       => $title,
                    'content'     => $content,
                    'user_id'     => $userId,
                ],
            );
            return $result;
        });
    }
}