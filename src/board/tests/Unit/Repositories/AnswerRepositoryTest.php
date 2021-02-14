<?php

namespace Tests\Unit\Repositories;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use App\Repositories\AnswerRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnswerRepositoryTest extends TestCase
{
    use RefreshDatabase;

    // public function setUp(): void
    // {
    //     factory(User::class)->create();
    //     factory(Question::class)->create();
    //     factory(Answer::class)->create();
    // }

    /**
     * @test
     */
    public function storeAnswerでDBの保存ができること()
    {
        factory(User::class)->create();
        factory(Question::class)->create();

        $AnswerRepository = app(AnswerRepository::class);
        $AnswerRepository->storeAnswer("aaa", 1, 1);

        $this->assertDatabaseHas('answers', [
            'question_id' => 1,
            'content'     => "aaa",
            'user_id'     => 1,
        ]);
    }

    // /**
    //  * @test
    //  */
    // public function getAnswerListByQuestionで特定の質問に紐づく回答一覧を取得すること()
    // {
    //     factory(User::class)->create();
    //     factory(Question::class)->create();
    //     factory(Answer::class)->create();

    //     $AnswerRepository = app(AnswerRepository::class);
    //     $actual = $AnswerRepository->getAnswerListByQuestion(1);

    //     $this->assertEquals(1, $actual->all()[0]->question_id);
    // }
}
