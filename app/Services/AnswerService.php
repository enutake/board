<?php

namespace App\Services;

use App\Models\Answer;
use App\Repositories\AnswerRepository;
use Illuminate\Support\Facades\DB;

class AnswerService
{
    private AnswerRepository $AnswerRepository;

    public function __construct(AnswerRepository $AnswerRepository): void
    {
        $this->AnswerRepository = $AnswerRepository;
    }

    /**
     * 質問ページに紐づく回答一覧を取得する
     */
    public function getAnswerListForQuestionPage(int $questionId): \Illuminate\Database\Eloquent\Collection
    {
        $AnswerList = $this->AnswerRepository->getAnswerListByQuestion($questionId);
        return $AnswerList;
    }

    /**
     * 回答投稿データを保存する
     */
    public function storeAnswer(string $content, int $userId, int $questionId): void
    {
        $this->AnswerRepository->storeAnswer($content, $userId, $questionId);
    }
}
