<?php

namespace App\Repositories;

use App\Models\Question;
use Illuminate\Support\Facades\DB;

class QuestionRepository
{
    public function getQuestionList($questionCount)
    {
        return Question::take($questionCount)->get();
    }
}
