<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use stdClass;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){}

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $data = new stdClass;
        $Question = new Question;
        $data->questions = $Question::all();
        return view('home', ['data' => $data]);
    }
}
