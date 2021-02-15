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

    public function setUp(): void
    {
        parent::setUp();
        factory(User::class)->create([
            'id' => 1, //incrementのリセットはされないので作成時は指定する
        ]);
        factory(Question::class)->create([
            'id' => 1, //incrementのリセットはされないので作成時は指定する
        ]);
        factory(Answer::class)->create([
            'id' => 1, //incrementのリセットはされないので作成時は指定する
        ]);
    }

    /**
     * @test
     */
    public function storeAnswerでDBの保存ができること()
    {
        $AnswerRepository = app(AnswerRepository::class);
        $AnswerRepository->storeAnswer("aaa", 1, 1);

        $this->assertDatabaseHas('answers', [
            'question_id' => 1,
            'content'     => "aaa",
            'user_id'     => 1,
        ]);
    }

    /**
     * @test
     */
    public function getAnswerListByQuestionで特定の質問に紐づく回答一覧を取得すること()
    {
        $AnswerRepository = app(AnswerRepository::class);
        $actual = $AnswerRepository->getAnswerListByQuestion(1);

        $this->assertEquals(1, $actual->all()[0]->question_id);
    }
}
