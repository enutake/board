<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Auth::routes();

Route::get('/', 'HomeController@index')->name('home');
Route::get('/questions/new', 'QuestionController@create')->middleware('auth')->name('question.create');
Route::get('/questions/{question}', 'QuestionController@show')->name('question.show');
Route::get('/questions/{question}/answers/new', 'AnswerController@create')->middleware('auth')->name('answer.create');
Route::post('/answers', 'AnswerController@store')->middleware('auth')->name('answer.store');
Route::post('/questions', 'QuestionController@store')->middleware('auth')->name('question.store');
Route::post('/answers/{answer}/like', 'AnswerController@like')->middleware('auth')->name('answer.like');
Route::delete('/answers/{answer}/like', 'AnswerController@unlike')->middleware('auth')->name('answer.unlike');
