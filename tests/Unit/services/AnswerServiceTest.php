<?php

namespace Tests\Unit\services;

use App\Models\Answer;
use App\Repositories\AnswerRepository;
use App\Services\AnswerService;
use Mockery;
use PHPUnit\Framework\TestCase;

class AnswerServiceTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function getAnswerListForQuestionPageのテスト()
    {
        $answerList = [1,1,1];
        $AnswerRepositoryMock = Mockery::mock(AnswerRepository::class)->makePartial();
        $AnswerRepositoryMock->shouldReceive('getAnswerListByQuestion')->andReturn($answerList);
        app()->instance(AnswerRepository::class, $AnswerRepositoryMock);
        $AnswerService = app(AnswerService::class);

        $questionId = 1;
        $expected = [1,1,1];
        $actual = $AnswerService->getAnswerListForQuestionPage($questionId);
        $this->assertEquals($expected, $actual);
    }
}
