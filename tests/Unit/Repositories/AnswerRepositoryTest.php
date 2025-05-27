<?php

namespace Tests\Unit\Repositories;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use App\Repositories\AnswerRepository;
use Tests\TestCase;
use Tests\TestHelpers;
use Illuminate\Support\Facades\DB;
use Exception;

class AnswerRepositoryTest extends TestCase
{
    use TestHelpers;

    protected AnswerRepository $answerRepository;
    protected User $user;
    protected Question $question;
    protected Answer $answer;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->answerRepository = app(AnswerRepository::class);
        
        // Create test data using helpers
        $this->user = $this->createUser();
        $this->question = $this->createQuestion(['user_id' => $this->user->id]);
        $this->answer = $this->createAnswer([
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
    public function storeAnswerで空文字のcontentでも保存できること()
    {
        $content = "";
        
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
    public function storeAnswerで長いcontentでも保存できること()
    {
        $content = str_repeat("あ", 1000); // 1000文字の長いテキスト
        
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
    public function storeAnswerでトランザクションが正常に動作すること()
    {
        $initialCount = Answer::count();
        $content = "トランザクションテスト";
        
        $this->answerRepository->storeAnswer($content, $this->user->id, $this->question->id);
        
        $this->assertEquals($initialCount + 1, Answer::count());
        $this->assertDatabaseHas('answers', [
            'content' => $content,
            'user_id' => $this->user->id,
            'question_id' => $this->question->id,
        ]);
    }

    /**
     * @test
     */
    public function getAnswerListByQuestionで特定の質問に紐づく回答一覧を取得すること()
    {
        $actual = $this->answerRepository->getAnswerListByQuestion($this->question->id);

        $this->assertEquals($this->question->id, $actual->first()->question_id);
        $this->assertCount(1, $actual);
    }

    /**
     * @test
     */
    public function getAnswerListByQuestionで存在しない質問IDの場合空のコレクションを返すこと()
    {
        $nonExistentQuestionId = 99999;
        
        $actual = $this->answerRepository->getAnswerListByQuestion($nonExistentQuestionId);
        
        $this->assertCount(0, $actual);
        $this->assertTrue($actual->isEmpty());
    }

    /**
     * @test
     */
    public function getAnswerListByQuestionで複数の回答がある場合全て取得できること()
    {
        // 追加の回答を作成
        $this->createAnswer(['user_id' => $this->user->id, 'question_id' => $this->question->id]);
        $this->createAnswer(['user_id' => $this->user->id, 'question_id' => $this->question->id]);
        
        $actual = $this->answerRepository->getAnswerListByQuestion($this->question->id);
        
        $this->assertCount(3, $actual); // 既存の1個 + 新しく作成した2個
        foreach ($actual as $answer) {
            $this->assertEquals($this->question->id, $answer->question_id);
        }
    }

    /**
     * @test
     */
    public function getAnswerListByQuestionで他の質問の回答は含まれないこと()
    {
        // 別の質問を作成
        $anotherQuestion = $this->createQuestion(['user_id' => $this->user->id]);
        $this->createAnswer([
            'user_id' => $this->user->id, 
            'question_id' => $anotherQuestion->id
        ]);
        
        $actual = $this->answerRepository->getAnswerListByQuestion($this->question->id);
        
        $this->assertCount(1, $actual);
        $this->assertEquals($this->question->id, $actual->first()->question_id);
    }

    /**
     * @test
     */
    public function getAnswerListByQuestionで取得される回答にuser_idとquestion_idが含まれること()
    {
        $actual = $this->answerRepository->getAnswerListByQuestion($this->question->id);
        
        $answer = $actual->first();
        $this->assertNotNull($answer->user_id);
        $this->assertNotNull($answer->question_id);
        $this->assertNotNull($answer->content);
        $this->assertEquals($this->user->id, $answer->user_id);
        $this->assertEquals($this->question->id, $answer->question_id);
    }
}
