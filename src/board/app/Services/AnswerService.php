<?php

namespace App\Services;

use App\Models\Answer;
use App\Repositories\AnswerRepository;
use Illuminate\Support\Facades\DB;

class AnswerService
{
    public function __construct(AnswerRepository $AnswerRepository)
    {
        $this->AnswerRepository = $AnswerRepository;
    }

    /**
     * 質問ページに紐づく回答一覧を取得する
     */
    public function getAnswerListForQuestionPage($questionId)
    {
        $AnswerList = $this->AnswerRepository->getAnswerListByQuestion($questionId);
        return $AnswerList;
    }

    /**
     * 回答投稿データを保存する
     */
    public function storeAnswer($content, $userId, $questionId)
    {
        DB::transaction(function () use ($content, $userId, $questionId) {
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
