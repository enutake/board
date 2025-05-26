<?php

namespace App\Repositories;

use App\Models\Answer;
use Illuminate\Support\Facades\DB;

class AnswerRepository
{
    public function getAnswerListByQuestion(int $questionId): \Illuminate\Support\Collection
    {
        return Answer::where('question_id', $questionId)->get();
    }

    public function storeAnswer(string $content, int $userId, int $questionId): void
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
