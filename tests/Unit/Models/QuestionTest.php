<?php

namespace Tests\Unit\Models;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use App\Models\TagMaster;
use Tests\TestCase;
use Tests\TestHelpers;

class QuestionTest extends TestCase
{
    use TestHelpers;

    protected $user;
    protected $question;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->user = $this->createUser();
        $this->question = $this->createQuestion(['user_id' => $this->user->id]);
    }

    /**
     * @test
     */
    public function Questionモデルが正常に作成できること()
    {
        $this->assertInstanceOf(Question::class, $this->question);
        $this->assertNotNull($this->question->id);
        $this->assertNotNull($this->question->title);
        $this->assertNotNull($this->question->content);
        $this->assertEquals($this->user->id, $this->question->user_id);
    }

    /**
     * @test
     */
    public function Questionモデルのfillable属性が正しく設定されていること()
    {
        $fillable = $this->question->getFillable();
        
        $expectedFillable = [
            'title', 
            'content', 
            'user_id',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
        
        $this->assertEquals($expectedFillable, $fillable);
    }

    /**
     * @test
     */
    public function Questionモデルでmass_assignmentが正常に動作すること()
    {
        $data = [
            'title' => 'マスアサインメントテストタイトル',
            'content' => 'マスアサインメントテストコンテンツ',
            'user_id' => $this->user->id,
        ];
        
        $question = Question::create($data);
        
        $this->assertInstanceOf(Question::class, $question);
        $this->assertEquals($data['title'], $question->title);
        $this->assertEquals($data['content'], $question->content);
        $this->assertEquals($data['user_id'], $question->user_id);
    }

    /**
     * @test
     */
    public function usersリレーションでUserモデルと正しく関連付けられていること()
    {
        $relatedUser = $this->question->users;
        
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
        $relation = $this->question->users();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getOwnerKeyName());
    }

    /**
     * @test
     */
    public function answersリレーションでAnswerモデルと正しく関連付けられていること()
    {
        // 質問に対する回答を作成
        $answer1 = $this->createAnswer([
            'user_id' => $this->user->id,
            'question_id' => $this->question->id
        ]);
        $answer2 = $this->createAnswer([
            'user_id' => $this->user->id,
            'question_id' => $this->question->id
        ]);
        
        $relatedAnswers = $this->question->answers;
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $relatedAnswers);
        $this->assertCount(2, $relatedAnswers);
        
        foreach ($relatedAnswers as $answer) {
            $this->assertInstanceOf(Answer::class, $answer);
            $this->assertEquals($this->question->id, $answer->question_id);
        }
    }

    /**
     * @test
     */
    public function answersリレーションでhasMany関係が正しく設定されていること()
    {
        $relation = $this->question->answers();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
        $this->assertEquals('question_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * @test
     */
    public function answersリレーションで回答がない場合空のコレクションを返すこと()
    {
        $newQuestion = $this->createQuestion(['user_id' => $this->user->id]);
        
        $answers = $newQuestion->answers;
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $answers);
        $this->assertTrue($answers->isEmpty());
        $this->assertCount(0, $answers);
    }

    /**
     * @test
     */
    public function tagMastersリレーションでTagMasterモデルと正しく関連付けられていること()
    {
        $relation = $this->question->tagMasters();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $relation);
        $this->assertEquals('tag_masters', $relation->getTable());
    }

    /**
     * @test
     */
    public function Questionモデルでタイムスタンプが自動的に設定されること()
    {
        $newQuestion = Question::create([
            'title' => 'タイムスタンプテストタイトル',
            'content' => 'タイムスタンプテストコンテンツ',
            'user_id' => $this->user->id,
        ]);
        
        $this->assertNotNull($newQuestion->created_at);
        $this->assertNotNull($newQuestion->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $newQuestion->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $newQuestion->updated_at);
    }

    /**
     * @test
     */
    public function Questionモデルの更新でupdated_atが変更されること()
    {
        $originalUpdatedAt = $this->question->updated_at;
        
        // 少し待ってから更新
        sleep(1);
        $this->question->update(['title' => '更新されたタイトル']);
        
        $this->assertNotEquals($originalUpdatedAt, $this->question->updated_at);
        $this->assertEquals('更新されたタイトル', $this->question->title);
    }

    /**
     * @test
     */
    public function Questionモデルで異なるユーザーとの関連付けが正しく動作すること()
    {
        $anotherUser = $this->createUser();
        $anotherQuestion = $this->createQuestion(['user_id' => $anotherUser->id]);
        
        $this->assertNotEquals($this->question->user_id, $anotherQuestion->user_id);
        $this->assertEquals($anotherUser->id, $anotherQuestion->users->id);
        $this->assertEquals($this->user->id, $this->question->users->id);
    }

    /**
     * @test
     */
    public function Questionモデルの削除が正常に動作すること()
    {
        $questionId = $this->question->id;
        
        $this->question->delete();
        
        $this->assertNull(Question::find($questionId));
    }

    /**
     * @test
     */
    public function Questionモデルで質問に紐づく複数の回答が正しく取得できること()
    {
        // 複数のユーザーを作成し、それぞれが回答を投稿
        $user2 = $this->createUser();
        $user3 = $this->createUser();
        
        $this->createAnswer([
            'user_id' => $this->user->id,
            'question_id' => $this->question->id,
            'content' => '回答1'
        ]);
        $this->createAnswer([
            'user_id' => $user2->id,
            'question_id' => $this->question->id,
            'content' => '回答2'
        ]);
        $this->createAnswer([
            'user_id' => $user3->id,
            'question_id' => $this->question->id,
            'content' => '回答3'
        ]);
        
        $answers = $this->question->answers;
        
        $this->assertCount(3, $answers);
        $userIds = $answers->pluck('user_id')->toArray();
        $this->assertContains($this->user->id, $userIds);
        $this->assertContains($user2->id, $userIds);
        $this->assertContains($user3->id, $userIds);
    }

    /**
     * @test
     */
    public function Questionモデルで他の質問の回答は含まれないこと()
    {
        // 別の質問を作成
        $anotherQuestion = $this->createQuestion(['user_id' => $this->user->id]);
        
        // それぞれの質問に回答を作成
        $this->createAnswer([
            'user_id' => $this->user->id,
            'question_id' => $this->question->id,
            'content' => 'オリジナル質問への回答'
        ]);
        $this->createAnswer([
            'user_id' => $this->user->id,
            'question_id' => $anotherQuestion->id,
            'content' => '別の質問への回答'
        ]);
        
        $originalQuestionAnswers = $this->question->answers;
        $anotherQuestionAnswers = $anotherQuestion->answers;
        
        $this->assertCount(1, $originalQuestionAnswers);
        $this->assertCount(1, $anotherQuestionAnswers);
        $this->assertEquals($this->question->id, $originalQuestionAnswers->first()->question_id);
        $this->assertEquals($anotherQuestion->id, $anotherQuestionAnswers->first()->question_id);
    }
}