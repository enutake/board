<?php

namespace App\Services;

use App\Repositories\QuestionRepository;

class QuestionService
{
    private QuestionRepository $QuestionRepository;

    public function __construct(QuestionRepository $QuestionRepository)
    {
        $this->QuestionRepository = $QuestionRepository;
    }
    
    /**
     * トップページの質問一覧を取得する
     */
    public function getQuestionListForTop(): \Illuminate\Database\Eloquent\Collection
    {
        $toppageQuestionCount = config('page.toppage.questions.count', 10);
        // nullの場合はデフォルト値を使用
        if (is_null($toppageQuestionCount)) {
            $toppageQuestionCount = 10;
        }
        return $this->QuestionRepository->getQuestionList($toppageQuestionCount);
    }

    /**
     * 質問詳細を取得する
     */
    public function getQuestionDetail(int $questionId): ?\App\Models\Question
    {
        return $this->QuestionRepository->getQuestionDetailById($questionId);
    }

    /**
     * 質問投稿データを保存する
     */
    public function storeQuestion(string $title, string $content, int $userId): \App\Models\Question
    {
        return $this->QuestionRepository->storeQuestion($title, $content, $userId);
    }
}
