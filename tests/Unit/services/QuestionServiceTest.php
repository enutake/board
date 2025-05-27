<?php

namespace Tests\Unit\services;

use App\Models\Question;
use App\Repositories\QuestionRepository;
use App\Services\QuestionService;
use Mockery;
use Tests\TestCase;
use Tests\TestHelpers;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;

class QuestionServiceTest extends TestCase
{
    use TestHelpers;
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected QuestionService $questionService;
    protected QuestionRepository $questionRepositoryMock;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->questionRepositoryMock = Mockery::mock(QuestionRepository::class);
        app()->instance(QuestionRepository::class, $this->questionRepositoryMock);
        $this->questionService = app(QuestionService::class);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getQuestionListForTopでトップページの質問一覧を取得できること()
    {
        // モックオブジェクトを作成（データベースアクセスなし）
        $question1 = new Question(['id' => 1, 'title' => 'テスト質問1', 'content' => 'コンテンツ1', 'user_id' => 1]);
        $question2 = new Question(['id' => 2, 'title' => 'テスト質問2', 'content' => 'コンテンツ2', 'user_id' => 1]);
        $expectedQuestions = new Collection([$question1, $question2]);
        
        // モックの設定 - 設定ファイルから取得する値をテストで固定
        config(['page.toppage.questions.count' => 10]);
        
        $this->questionRepositoryMock
            ->shouldReceive('getQuestionList')
            ->once()
            ->with(10)
            ->andReturn($expectedQuestions);

        $actual = $this->questionService->getQuestionListForTop();

        $this->assertEquals($expectedQuestions, $actual);
        $this->assertCount(2, $actual);
    }

    /**
     * @test
     */
    public function getQuestionListForTopで設定ファイルのデフォルト値が使用されること()
    {
        // 設定をクリアしてデフォルト値をテスト
        // configヘルパーは設定値が存在しない場合にデフォルト値を返す
        config()->offsetUnset('page.toppage.questions.count');
        
        $expectedQuestions = new Collection([]);
        
        // getQuestionListForTopメソッドはconfig値が存在しない場合10を使用する
        $this->questionRepositoryMock
            ->shouldReceive('getQuestionList')
            ->once()
            ->with(10)
            ->andReturn($expectedQuestions);

        $actual = $this->questionService->getQuestionListForTop();

        $this->assertEquals($expectedQuestions, $actual);
    }

    /**
     * @test
     */
    public function getQuestionListForTopで空のコレクションが返されても正常に処理されること()
    {
        config(['page.toppage.questions.count' => 5]);
        
        $expectedQuestions = new Collection([]);
        
        $this->questionRepositoryMock
            ->shouldReceive('getQuestionList')
            ->once()
            ->with(5)
            ->andReturn($expectedQuestions);

        $actual = $this->questionService->getQuestionListForTop();

        $this->assertEquals($expectedQuestions, $actual);
        $this->assertTrue($actual->isEmpty());
    }

    /**
     * @test
     */
    public function getQuestionDetailで指定したIDの質問詳細を取得できること()
    {
        $questionId = 123;
        // モックオブジェクトを作成（データベースアクセスなし）
        $expectedQuestion = new Question();
        $expectedQuestion->id = $questionId;
        $expectedQuestion->title = 'テスト質問';
        $expectedQuestion->content = 'テスト内容';
        $expectedQuestion->user_id = 1;
        
        $this->questionRepositoryMock
            ->shouldReceive('getQuestionDetailById')
            ->once()
            ->with($questionId)
            ->andReturn($expectedQuestion);

        $actual = $this->questionService->getQuestionDetail($questionId);

        $this->assertEquals($expectedQuestion, $actual);
        $this->assertEquals($questionId, $actual->id);
    }

    /**
     * @test
     */
    public function getQuestionDetailで存在しないIDを指定した場合nullを返すこと()
    {
        $nonExistentId = 99999;
        
        $this->questionRepositoryMock
            ->shouldReceive('getQuestionDetailById')
            ->once()
            ->with($nonExistentId)
            ->andReturn(null);

        $actual = $this->questionService->getQuestionDetail($nonExistentId);

        $this->assertNull($actual);
    }

    /**
     * @test
     */
    public function getQuestionDetailで異なるIDに対して異なる結果を返すこと()
    {
        $questionId1 = 1;
        $questionId2 = 2;
        
        // モックオブジェクトを作成（データベースアクセスなし）
        $expectedQuestion1 = new Question();
        $expectedQuestion1->id = $questionId1;
        $expectedQuestion1->title = '質問1';
        $expectedQuestion1->content = 'コンテンツ1';
        $expectedQuestion1->user_id = 1;
        
        $expectedQuestion2 = new Question();
        $expectedQuestion2->id = $questionId2;
        $expectedQuestion2->title = '質問2';
        $expectedQuestion2->content = 'コンテンツ2';
        $expectedQuestion2->user_id = 1;
        
        $this->questionRepositoryMock
            ->shouldReceive('getQuestionDetailById')
            ->with($questionId1)
            ->andReturn($expectedQuestion1);
            
        $this->questionRepositoryMock
            ->shouldReceive('getQuestionDetailById')
            ->with($questionId2)
            ->andReturn($expectedQuestion2);

        $actual1 = $this->questionService->getQuestionDetail($questionId1);
        $actual2 = $this->questionService->getQuestionDetail($questionId2);

        $this->assertEquals($expectedQuestion1, $actual1);
        $this->assertEquals($expectedQuestion2, $actual2);
        $this->assertNotEquals($actual1->id, $actual2->id);
    }

    /**
     * @test
     */
    public function storeQuestionで質問データを保存できること()
    {
        $title = 'テストタイトル';
        $content = 'テストコンテンツ';
        $userId = 456;
        
        // モックオブジェクトを作成（データベースアクセスなし）
        $expectedResult = new Question();
        $expectedResult->id = 789;
        $expectedResult->title = $title;
        $expectedResult->content = $content;
        $expectedResult->user_id = $userId;
        
        $this->questionRepositoryMock
            ->shouldReceive('storeQuestion')
            ->once()
            ->with($title, $content, $userId)
            ->andReturn($expectedResult);

        $actual = $this->questionService->storeQuestion($title, $content, $userId);

        $this->assertEquals($expectedResult, $actual);
        $this->assertEquals($title, $actual->title);
        $this->assertEquals($content, $actual->content);
        $this->assertEquals($userId, $actual->user_id);
    }

    /**
     * @test
     */
    public function storeQuestionで空文字のタイトルでも保存できること()
    {
        $title = '';
        $content = 'テストコンテンツ';
        $userId = 456;
        
        // モックオブジェクトを作成（データベースアクセスなし）
        $expectedResult = new Question();
        $expectedResult->id = 789;
        $expectedResult->title = $title;
        $expectedResult->content = $content;
        $expectedResult->user_id = $userId;
        
        $this->questionRepositoryMock
            ->shouldReceive('storeQuestion')
            ->once()
            ->with($title, $content, $userId)
            ->andReturn($expectedResult);

        $actual = $this->questionService->storeQuestion($title, $content, $userId);

        $this->assertEquals($expectedResult, $actual);
        $this->assertEquals('', $actual->title);
    }

    /**
     * @test
     */
    public function storeQuestionで長いタイトルとコンテンツでも保存できること()
    {
        $title = str_repeat('あ', 85); // 255文字制限に合わせる
        $content = str_repeat('い', 2000);
        $userId = 456;
        
        // モックオブジェクトを作成（データベースアクセスなし）
        $expectedResult = new Question();
        $expectedResult->id = 789;
        $expectedResult->title = $title;
        $expectedResult->content = $content;
        $expectedResult->user_id = $userId;
        
        $this->questionRepositoryMock
            ->shouldReceive('storeQuestion')
            ->once()
            ->with($title, $content, $userId)
            ->andReturn($expectedResult);

        $actual = $this->questionService->storeQuestion($title, $content, $userId);

        $this->assertEquals($expectedResult, $actual);
        $this->assertEquals($title, $actual->title);
        $this->assertEquals($content, $actual->content);
    }

    /**
     * @test
     */
    public function storeQuestionでRepositoryのメソッドが適切な引数で呼ばれることを確認()
    {
        $title = 'テストタイトル';
        $content = 'テストコンテンツ';
        $userId = 123;
        
        // モックオブジェクトを作成（データベースアクセスなし）
        $expectedResult = new Question();
        $expectedResult->id = 1;
        $expectedResult->title = $title;
        $expectedResult->content = $content;
        $expectedResult->user_id = $userId;
        
        $this->questionRepositoryMock
            ->shouldReceive('storeQuestion')
            ->once()
            ->with($title, $content, $userId)
            ->andReturn($expectedResult);

        $this->questionService->storeQuestion($title, $content, $userId);

        // Mockeryの検証は自動的に行われる（shouldReceive で once() を指定しているため）
        $this->assertTrue(true); // テストが完了したことを示すダミーアサーション
    }
}