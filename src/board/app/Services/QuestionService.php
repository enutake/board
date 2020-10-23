<?php

namespace App\Services;

use App\Models\Question;

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
}