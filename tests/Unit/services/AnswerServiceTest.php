<?php

namespace Tests\Unit\services;

use App\Models\Answer;
use App\Repositories\AnswerRepository;
use App\Services\AnswerService;
use Mockery;
use Tests\TestCase;
use Tests\TestHelpers;

class AnswerServiceTest extends TestCase
{
    use TestHelpers;

    protected AnswerService $answerService;
    protected AnswerRepository $answerRepositoryMock;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->answerRepositoryMock = Mockery::mock(AnswerRepository::class);
        app()->instance(AnswerRepository::class, $this->answerRepositoryMock);
        $this->answerService = app(AnswerService::class);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getAnswerListForQuestionPageのテスト()
    {
        $questionId = 1;
        $expectedAnswerList = collect([
            (object) ['id' => 1, 'content' => '回答1', 'question_id' => $questionId],
            (object) ['id' => 2, 'content' => '回答2', 'question_id' => $questionId],
        ]);
        
        $this->answerRepositoryMock
            ->shouldReceive('getAnswerListByQuestion')
            ->once()
            ->with($questionId)
            ->andReturn($expectedAnswerList);

        $actual = $this->answerService->getAnswerListForQuestionPage($questionId);
        
        $this->assertEquals($expectedAnswerList, $actual);
        $this->assertCount(2, $actual);
    }

    /**
     * @test
     */
    public function getAnswerListForQuestionPageで空のコレクションが返されても正常に処理されること()
    {
        $questionId = 999;
        $expectedAnswerList = collect([]);
        
        $this->answerRepositoryMock
            ->shouldReceive('getAnswerListByQuestion')
            ->once()
            ->with($questionId)
            ->andReturn($expectedAnswerList);

        $actual = $this->answerService->getAnswerListForQuestionPage($questionId);
        
        $this->assertEquals($expectedAnswerList, $actual);
        $this->assertTrue($actual->isEmpty());
    }

    /**
     * @test
     */
    public function getAnswerListForQuestionPageで異なる質問IDに対して異なる結果を返すこと()
    {
        $questionId1 = 1;
        $questionId2 = 2;
        
        $answerList1 = collect([(object) ['id' => 1, 'question_id' => $questionId1]]);
        $answerList2 = collect([(object) ['id' => 2, 'question_id' => $questionId2]]);
        
        $this->answerRepositoryMock
            ->shouldReceive('getAnswerListByQuestion')
            ->with($questionId1)
            ->andReturn($answerList1);
            
        $this->answerRepositoryMock
            ->shouldReceive('getAnswerListByQuestion')
            ->with($questionId2)
            ->andReturn($answerList2);

        $actual1 = $this->answerService->getAnswerListForQuestionPage($questionId1);
        $actual2 = $this->answerService->getAnswerListForQuestionPage($questionId2);

        $this->assertEquals($answerList1, $actual1);
        $this->assertEquals($answerList2, $actual2);
        $this->assertNotEquals($actual1->first()->question_id, $actual2->first()->question_id);
    }

    /**
     * @test
     */
    public function storeAnswerで回答データを保存できること()
    {
        $content = 'テスト回答内容';
        $userId = 123;
        $questionId = 456;
        
        $this->answerRepositoryMock
            ->shouldReceive('storeAnswer')
            ->once()
            ->with($content, $userId, $questionId)
            ->andReturnNull(); // void メソッドなので null を返す

        $this->answerService->storeAnswer($content, $userId, $questionId);

        // Mockeryの検証は自動的に行われる（shouldReceive で once() を指定しているため）
        $this->assertTrue(true); // テストが完了したことを示すダミーアサーション
    }

    /**
     * @test
     */
    public function storeAnswerで空文字のcontentでも保存できること()
    {
        $content = '';
        $userId = 123;
        $questionId = 456;
        
        $this->answerRepositoryMock
            ->shouldReceive('storeAnswer')
            ->once()
            ->with($content, $userId, $questionId)
            ->andReturnNull();

        $this->answerService->storeAnswer($content, $userId, $questionId);

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function storeAnswerで長いcontentでも保存できること()
    {
        $content = str_repeat('あ', 2000);
        $userId = 123;
        $questionId = 456;
        
        $this->answerRepositoryMock
            ->shouldReceive('storeAnswer')
            ->once()
            ->with($content, $userId, $questionId)
            ->andReturnNull();

        $this->answerService->storeAnswer($content, $userId, $questionId);

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function storeAnswerでRepositoryのメソッドが適切な引数で呼ばれることを確認()
    {
        $content = 'テスト回答';
        $userId = 789;
        $questionId = 12;
        
        $this->answerRepositoryMock
            ->shouldReceive('storeAnswer')
            ->once()
            ->with($content, $userId, $questionId)
            ->andReturnNull();

        $this->answerService->storeAnswer($content, $userId, $questionId);

        // Mockeryの検証により、メソッドが正確な引数で呼ばれたことが確認される
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function getAnswerListForQuestionPageでRepositoryから返された値をそのまま返すこと()
    {
        $questionId = 100;
        $repositoryResult = collect([
            (object) ['id' => 10, 'content' => 'リポジトリからの回答', 'question_id' => $questionId],
        ]);
        
        $this->answerRepositoryMock
            ->shouldReceive('getAnswerListByQuestion')
            ->once()
            ->with($questionId)
            ->andReturn($repositoryResult);

        $actual = $this->answerService->getAnswerListForQuestionPage($questionId);
        
        // Serviceはリポジトリの結果をそのまま返すだけなので、完全に同じオブジェクトである必要がある
        $this->assertSame($repositoryResult, $actual);
    }
}
