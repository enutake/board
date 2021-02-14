<?php

namespace App\Repositories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class QuestionRepository
{
    public function getQuestionList($questionCount): Collection
    {
        return Question::take($questionCount)->get();
    }

    public function getQuestionDetailById($questionId): ?Question
    {
        return Question::find($questionId);
    }

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
