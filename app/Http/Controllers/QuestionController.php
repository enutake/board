<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Services\AnswerService;
use App\Services\QuestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use stdClass;

class QuestionController extends Controller
{
    private QuestionService $QuestionService;
    private AnswerService $AnswerService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(QuestionService $QuestionService, AnswerService $AnswerService)
    {
        $this->QuestionService = $QuestionService;
        $this->AnswerService   = $AnswerService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): void
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): \Illuminate\Contracts\View\View
    {
        return view('question.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);
        
        $title   = $request->input('title');
        $content = $request->input('content');
        $userId  = Auth::id();
        if ($userId === null) {
            abort(401, 'Unauthorized');
        }
        $result = $this->QuestionService->storeQuestion($title, $content, $userId);

        return redirect()->route('question.show', $result->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(\App\Models\Question $question): \Illuminate\Contracts\View\View
    {
        $data = new stdClass;
        $data->question = $this->QuestionService->getQuestionDetail($question->id);
        $data->answers  = $this->AnswerService->getAnswerListForQuestionPage($question->id);

        return view('question.index', ['data' => $data]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id): void
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id): void
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id): void
    {
        //
    }
}
