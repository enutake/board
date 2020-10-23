<?php

namespace App\Services;

use App\Models\Answer;

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

}