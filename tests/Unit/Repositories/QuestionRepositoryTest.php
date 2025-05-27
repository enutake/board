<?php

namespace Tests\Unit\Repositories;

use App\Models\Question;
use App\Models\User;
use App\Repositories\QuestionRepository;
use Tests\TestCase;
use Tests\TestHelpers;
use Illuminate\Support\Facades\DB;

class QuestionRepositoryTest extends TestCase
{
    use TestHelpers;
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected QuestionRepository $questionRepository;
    protected User $user;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->questionRepository = app(QuestionRepository::class);
        $this->user = $this->createUser();
    }

    /**
     * @test
     */
    public function getQuestionListの正常系テスト()
    {
        $this->createQuestion(['user_id' => $this->user->id]);
        
        $actual = $this->questionRepository->getQuestionList(1);

        $this->assertEquals(1, count($actual));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $actual);
    }

    /**
     * @test
     */
    public function getQuestionListで指定した件数分の質問を取得できること()
    {
        // 複数の質問を作成
        $this->createQuestion(['user_id' => $this->user->id]);
        $this->createQuestion(['user_id' => $this->user->id]);
        $this->createQuestion(['user_id' => $this->user->id]);
        
        $actual = $this->questionRepository->getQuestionList(2);
        
        $this->assertEquals(2, count($actual));
    }

    /**
     * @test
     */
    public function getQuestionListで存在する質問数より多い件数を指定した場合全件取得すること()
    {
        $this->createQuestion(['user_id' => $this->user->id]);
        
        $actual = $this->questionRepository->getQuestionList(100000000);

        $this->assertEquals(1, count($actual));
        $this->assertNotEquals(100000000, count($actual));
    }

    /**
     * @test
     */
    public function getQuestionListで0件指定した場合空のコレクションを返すこと()
    {
        $this->createQuestion(['user_id' => $this->user->id]);
        
        $actual = $this->questionRepository->getQuestionList(0);
        
        $this->assertEquals(0, count($actual));
        $this->assertTrue($actual->isEmpty());
    }

    /**
     * @test
     */
    public function getQuestionListで質問が存在しない場合空のコレクションを返すこと()
    {
        $actual = $this->questionRepository->getQuestionList(10);
        
        $this->assertEquals(0, count($actual));
        $this->assertTrue($actual->isEmpty());
    }

    /**
     * @test
     */
    public function getQuestionDetailByIdの正常系テスト()
    {
        $question = $this->createQuestion(['user_id' => $this->user->id]);
        
        $actual = $this->questionRepository->getQuestionDetailById($question->id);

        $this->assertEquals($question->id, $actual->id);
        $this->assertEquals($question->title, $actual->title);
        $this->assertEquals($question->content, $actual->content);
        $this->assertEquals($question->user_id, $actual->user_id);
    }

    /**
     * @test
     */
    public function getQuestionDetailByIdで存在しないIDを指定した場合nullを返すこと()
    {
        $nonExistentId = 99999;
        
        $actual = $this->questionRepository->getQuestionDetailById($nonExistentId);
        
        $this->assertNull($actual);
    }

    /**
     * @test
     */
    public function getQuestionDetailByIdで削除された質問は取得できないこと()
    {
        $question = $this->createQuestion(['user_id' => $this->user->id]);
        $question->delete();
        
        $actual = $this->questionRepository->getQuestionDetailById($question->id);
        
        $this->assertNull($actual);
    }

    /**
     * @test
     */
    public function storeQuestionの正常系テスト()
    {
        $title = 'テストタイトル';
        $content = 'テストコンテンツ';
        
        $result = $this->questionRepository->storeQuestion($title, $content, $this->user->id);

        $this->assertDatabaseHas('questions', [
            'title'   => $title,
            'content' => $content,
            'user_id' => $this->user->id,
        ]);
        $this->assertInstanceOf(Question::class, $result);
        $this->assertEquals($title, $result->title);
        $this->assertEquals($content, $result->content);
        $this->assertEquals($this->user->id, $result->user_id);
    }

    /**
     * @test
     */
    public function storeQuestionで空文字のタイトルでも保存できること()
    {
        $title = '';
        $content = 'テストコンテンツ';
        
        $result = $this->questionRepository->storeQuestion($title, $content, $this->user->id);
        
        $this->assertDatabaseHas('questions', [
            'title'   => $title,
            'content' => $content,
            'user_id' => $this->user->id,
        ]);
        $this->assertInstanceOf(Question::class, $result);
    }

    /**
     * @test
     */
    public function storeQuestionで長いタイトルとコンテンツでも保存できること()
    {
        // titleはstring型なので255文字まで、contentはtext型なので長文OK
        $title = str_repeat('あ', 85); // 日本語は3バイトなので85文字まで
        $content = str_repeat('い', 2000);
        
        $result = $this->questionRepository->storeQuestion($title, $content, $this->user->id);
        
        $this->assertDatabaseHas('questions', [
            'title'   => $title,
            'content' => $content,
            'user_id' => $this->user->id,
        ]);
        $this->assertInstanceOf(Question::class, $result);
    }

    /**
     * @test
     */
    public function storeQuestionでトランザクションが正常に動作すること()
    {
        $initialCount = Question::count();
        $title = 'トランザクションテストタイトル';
        $content = 'トランザクションテストコンテンツ';
        
        $result = $this->questionRepository->storeQuestion($title, $content, $this->user->id);
        
        $this->assertEquals($initialCount + 1, Question::count());
        $this->assertInstanceOf(Question::class, $result);
        $this->assertDatabaseHas('questions', [
            'title' => $title,
            'content' => $content,
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * @test
     */
    public function storeQuestionで作成されたQuestionに適切なタイムスタンプが設定されること()
    {
        $title = 'タイムスタンプテスト';
        $content = 'タイムスタンプテストコンテンツ';
        
        $result = $this->questionRepository->storeQuestion($title, $content, $this->user->id);
        
        $this->assertNotNull($result->created_at);
        $this->assertNotNull($result->updated_at);
        $this->assertEquals($result->created_at->format('Y-m-d H:i'), $result->updated_at->format('Y-m-d H:i'));
    }
}
