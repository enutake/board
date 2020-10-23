<?php

namespace App\Services;

use App\Models\Answer;
use Illuminate\Support\Facades\DB;

class AnswerService
{
    /**
     * 質問ページに紐づく回答一覧を取得する
     */
    public function getAnswerListForQuestionPage($questionId)
    {
        $AnswerList = Answer::where('question_id', $questionId)->get();
        return $AnswerList;
    }

    /**
     * 回答投稿データを保存する
     */
    public function storeAnswer($content, $userId, $questionId)
    {
        DB::transaction(function () use ($content, $userId, $questionId){            
            Answer::create(
                [
                    'content'     => $content,
                    'user_id'     => $userId,
                    'question_id' => $questionId,
                ],
            );
        });
    }

}