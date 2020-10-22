<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Services\QuestionService;
use Illuminate\Http\Request;
use stdClass;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(QuestionService $QuestionService)
    {
        $this->QuestionService = $QuestionService;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $data = new stdClass;
        $data->questions = $this->QuestionService->getQuestionListForTop();
        return view('home', ['data' => $data]);
    }
}
