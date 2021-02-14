<?php

namespace App\Services;

use App\Models\Question;
use App\Repositories\QuestionRepository;
use Illuminate\Support\Facades\DB;

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
        return DB::transaction(function () use ($title, $content, $userId) {
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
