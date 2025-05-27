<?php

namespace App\Repositories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class QuestionRepository
{
    public function getQuestionList(int $questionCount): Collection
    {
        return Question::take($questionCount)->get();
    }

    public function getQuestionDetailById(int $questionId): ?Question
    {
        return Question::find($questionId);
    }

    public function storeQuestion(string $title, string $content, int $userId): Question
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
