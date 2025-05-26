<?php

namespace Tests\Unit\Models;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use App\Models\TagMaster;
use Tests\TestCase;
use Tests\TestHelpers;

class AnswerTest extends TestCase
{
    use TestHelpers;

    protected $user;
    protected $question;
    protected $answer;

    public function setUp(): void
    {
        parent::setUp();
        
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
    public function Answerモデルが正常に作成できること()
    {
        $this->assertInstanceOf(Answer::class, $this->answer);
        $this->assertNotNull($this->answer->id);
        $this->assertNotNull($this->answer->content);
        $this->assertEquals($this->user->id, $this->answer->user_id);
        $this->assertEquals($this->question->id, $this->answer->question_id);
    }

    /**
     * @test
     */
    public function Answerモデルのfillable属性が正しく設定されていること()
    {
        $fillable = $this->answer->getFillable();
        
        $expectedFillable = [
            'content', 
            'user_id',
            'question_id',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
        
        $this->assertEquals($expectedFillable, $fillable);
    }

    /**
     * @test
     */
    public function Answerモデルでmass assignmentが正常に動作すること()
    {
        $data = [
            'content' => 'マスアサインメントテスト',
            'user_id' => $this->user->id,
            'question_id' => $this->question->id,
        ];
        
        $answer = Answer::create($data);
        
        $this->assertInstanceOf(Answer::class, $answer);
        $this->assertEquals($data['content'], $answer->content);
        $this->assertEquals($data['user_id'], $answer->user_id);
        $this->assertEquals($data['question_id'], $answer->question_id);
    }

    /**
     * @test
     */
    public function usersリレーションでUserモデルと正しく関連付けられていること()
    {
        $relatedUser = $this->answer->users;
        
        $this->assertInstanceOf(User::class, $relatedUser);
        $this->assertEquals($this->user->id, $relatedUser->id);
        $this->assertEquals($this->user->name, $relatedUser->name);
        $this->assertEquals($this->user->email, $relatedUser->email);
    }

    /**
     * @test
     */
    public function usersリレーションでbelongsTo関係が正しく設定されていること()
    {
        $relation = $this->answer->users();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getOwnerKeyName());
    }

    /**
     * @test
     */
    public function questionsリレーションでQuestionモデルと正しく関連付けられていること()
    {
        $relatedQuestion = $this->answer->questions;
        
        $this->assertInstanceOf(Question::class, $relatedQuestion);
        $this->assertEquals($this->question->id, $relatedQuestion->id);
        $this->assertEquals($this->question->title, $relatedQuestion->title);
        $this->assertEquals($this->question->content, $relatedQuestion->content);
    }

    /**
     * @test
     */
    public function questionsリレーションでbelongsTo関係が正しく設定されていること()
    {
        $relation = $this->answer->questions();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals('question_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getOwnerKeyName());
    }

    /**
     * @test
     */
    public function tagMastersリレーションでTagMasterモデルと正しく関連付けられていること()
    {
        // TagMasterを作成（実際の中間テーブルの構造に基づいて調整が必要な場合がある）
        $tagMaster = $this->createTagMaster();
        
        $relation = $this->answer->tagMasters();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $relation);
        $this->assertEquals('tag_masters', $relation->getTable());
    }

    /**
     * @test
     */
    public function Answerモデルでタイムスタンプが自動的に設定されること()
    {
        $newAnswer = Answer::create([
            'content' => 'タイムスタンプテスト',
            'user_id' => $this->user->id,
            'question_id' => $this->question->id,
        ]);
        
        $this->assertNotNull($newAnswer->created_at);
        $this->assertNotNull($newAnswer->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $newAnswer->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $newAnswer->updated_at);
    }

    /**
     * @test
     */
    public function Answerモデルの更新でupdated_atが変更されること()
    {
        $originalUpdatedAt = $this->answer->updated_at;
        
        // 少し待ってから更新
        sleep(1);
        $this->answer->update(['content' => '更新されたコンテンツ']);
        
        $this->assertNotEquals($originalUpdatedAt, $this->answer->updated_at);
        $this->assertEquals('更新されたコンテンツ', $this->answer->content);
    }

    /**
     * @test
     */
    public function Answerモデルで異なるユーザーとの関連付けが正しく動作すること()
    {
        $anotherUser = $this->createUser();
        $anotherAnswer = $this->createAnswer([
            'user_id' => $anotherUser->id,
            'question_id' => $this->question->id
        ]);
        
        $this->assertNotEquals($this->answer->user_id, $anotherAnswer->user_id);
        $this->assertEquals($anotherUser->id, $anotherAnswer->users->id);
        $this->assertEquals($this->user->id, $this->answer->users->id);
    }

    /**
     * @test
     */
    public function Answerモデルで異なる質問との関連付けが正しく動作すること()
    {
        $anotherQuestion = $this->createQuestion(['user_id' => $this->user->id]);
        $anotherAnswer = $this->createAnswer([
            'user_id' => $this->user->id,
            'question_id' => $anotherQuestion->id
        ]);
        
        $this->assertNotEquals($this->answer->question_id, $anotherAnswer->question_id);
        $this->assertEquals($anotherQuestion->id, $anotherAnswer->questions->id);
        $this->assertEquals($this->question->id, $this->answer->questions->id);
    }

    /**
     * @test
     */
    public function Answerモデルの削除が正常に動作すること()
    {
        $answerId = $this->answer->id;
        
        $this->answer->delete();
        
        $this->assertNull(Answer::find($answerId));
    }
}