<?php

namespace Tests\Unit\Repositories;

use App\Models\Question;
use App\Models\User;
use App\Repositories\QuestionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionRepositoryTest extends TestCase
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
    }

    /**
     * @test
     */
    public function getQuestionListの正常系テスト()
    {
        $QuestionRepository = app(QuestionRepository::class);
        $actual = $QuestionRepository->getQuestionList(1);

        $this->assertEquals(1, count($actual));
    }

    /**
     * @test
     */
    public function getQuestionListの準正常系テスト()
    {
        $QuestionRepository = app(QuestionRepository::class);
        $actual = $QuestionRepository->getQuestionList(100000000);

        $this->assertNotEquals(100000000, count($actual));
    }

    /**
     * @test
     */
    public function getQuestionDetailByIdの正常系テスト()
    {
        $QuestionRepository = app(QuestionRepository::class);
        $actual = $QuestionRepository->getQuestionDetailById(1);

        $this->assertEquals(1, $actual->id);
    }

    /**
     * @test
     */
    public function storeQuestionの正常系テスト()
    {
        $QuestionRepository = app(QuestionRepository::class);
        $QuestionRepository->storeQuestion('titletitle', 'contentcontent', 1);

        $this->assertDatabaseHas('questions', [
            'id'      => 2,
            'title'   => 'titletitle',
            'content' => 'contentcontent',
            'user_id' => 1,
        ]);
    }
}
