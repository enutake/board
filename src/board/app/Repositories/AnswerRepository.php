<?php

namespace App\Repositories;

use App\Models\Answer;
use Illuminate\Support\Facades\DB;

class AnswerRepository
{
    public function getAnswerListByQuestion($questionId)
    {
        return Answer::where('question_id', $questionId)->get();
    }

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
