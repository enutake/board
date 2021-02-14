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
}
