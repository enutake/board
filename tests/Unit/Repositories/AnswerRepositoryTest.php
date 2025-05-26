<?php

namespace Tests\Unit\Repositories;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use App\Repositories\AnswerRepository;
use Tests\TestCase;
use Tests\TestHelpers;

class AnswerRepositoryTest extends TestCase
{
    use TestHelpers;

    protected $answerRepository;
    protected $user;
    protected $question;
    protected $answer;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->answerRepository = app(AnswerRepository::class);
        
        // Create test data using helpers
        $this->user = $this->createUser(['id' => 1]);
        $this->question = $this->createQuestion(['id' => 1, 'user_id' => $this->user->id]);
        $this->answer = $this->createAnswer([
            'id' => 1, 
            'user_id' => $this->user->id, 
            'question_id' => $this->question->id
        ]);
    }

    /**
     * @test
     */
    public function storeAnswerでDBの保存ができること()
    {
        $content = "テスト回答内容";
        
        $this->answerRepository->storeAnswer($content, $this->user->id, $this->question->id);

        $this->assertDatabaseHas('answers', [
            'question_id' => $this->question->id,
            'content'     => $content,
            'user_id'     => $this->user->id,
        ]);
    }

    /**
     * @test
     */
    public function getAnswerListByQuestionで特定の質問に紐づく回答一覧を取得すること()
    {
        $actual = $this->answerRepository->getAnswerListByQuestion($this->question->id);

        $this->assertEquals($this->question->id, $actual->all()[0]->question_id);
        $this->assertCount(1, $actual);
    }
}
