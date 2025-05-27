<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnswerRequest;
use App\Models\Answer;
use App\Services\AnswerService;
use App\Services\QuestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use stdClass;

class AnswerController extends Controller
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
    public function create(Request $request, \App\Models\Question $question): \Illuminate\Contracts\View\View
    {
        $userId = Auth::id();
        if ($userId === null) {
            abort(401, 'Unauthorized');
        }
        session(
            [
                'userId' => $userId,
                'questionId' => $question->id
            ],
        );

        $data = new stdClass;
        $data->question = $this->QuestionService->getQuestionDetail($question->id);
        return view('answer', ['data' => $data]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AnswerRequest $request): \Illuminate\Http\RedirectResponse
    {
        $request->session()->regenerate();

        $questionId = $request->session()->get('questionId');
        $userId = $request->session()->get('userId');
        if ($userId === null) {
            abort(401, 'Unauthorized');
        }
        
        // Ensure proper type casting
        $userIdInt = (int) $userId;
        $questionIdInt = (int) $questionId;
        
        $this->AnswerService->storeAnswer($request->input('content'), $userIdInt, $questionIdInt);

        $request->session()->forget('questionId');
        return redirect()->route('question.show', $questionIdInt);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $id): void
    {
        //
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
