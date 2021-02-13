<?php

namespace App\Repositories;

use App\Models\Answer;

class AnswerRepository
{
    public function getAnswerListByQuestion($questionId)
    {
        return Answer::where('question_id', $questionId)->get();
    }
}
