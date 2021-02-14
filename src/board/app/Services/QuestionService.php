<?php

namespace App\Services;

use App\Repositories\QuestionRepository;

class QuestionService
{
    public function __construct(QuestionRepository $QuestionRepository)
    {
        $this->QuestionRepository = $QuestionRepository;
    }
    
    /**
     * トップページの質問一覧を取得する
     */
    public function getQuestionListForTop()
    {
        $toppageQuestionCount = config('page.toppage.questions.count', 10);
        return $this->QuestionRepository->getQuestionList($toppageQuestionCount);
    }

    /**
     * 質問詳細を取得する
     */
    public function getQuestionDetail($questionId)
    {
        return $this->QuestionRepository->getQuestionDetailById($questionId);
    }

    /**
     * 質問投稿データを保存する
     */
    public function storeQuestion($title, $content, $userId)
    {
        return $this->QuestionRepository->storeQuestion($title, $content, $userId);
    }
}
