<?php

namespace Tests\Unit\Models;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use Tests\TestCase;
use Tests\TestHelpers;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use TestHelpers;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->user = $this->createUser();
    }

    /**
     * @test
     */
    public function Userモデルが正常に作成できること()
    {
        $this->assertInstanceOf(User::class, $this->user);
        $this->assertNotNull($this->user->id);
        $this->assertNotNull($this->user->name);
        $this->assertNotNull($this->user->email);
    }

    /**
     * @test
     */
    public function Userモデルのfillable属性が正しく設定されていること()
    {
        $fillable = $this->user->getFillable();
        
        $expectedFillable = [
            'name', 
            'email', 
            'password',
        ];
        
        $this->assertEquals($expectedFillable, $fillable);
    }

    /**
     * @test
     */
    public function Userモデルのhidden属性が正しく設定されていること()
    {
        $hidden = $this->user->getHidden();
        
        $expectedHidden = [
            'password', 
            'remember_token',
        ];
        
        $this->assertEquals($expectedHidden, $hidden);
    }

    /**
     * @test
     */
    public function Userモデルのcasts属性が正しく設定されていること()
    {
        $casts = $this->user->getCasts();
        
        $this->assertArrayHasKey('email_verified_at', $casts);
        $this->assertEquals('datetime', $casts['email_verified_at']);
    }

    /**
     * @test
     */
    public function Userモデルでmass_assignmentが正常に動作すること()
    {
        $data = [
            'name' => 'マスアサインメントテストユーザー',
            'email' => 'mass-assignment@test.com',
            'password' => Hash::make('password'),
        ];
        
        $user = User::create($data);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($data['name'], $user->name);
        $this->assertEquals($data['email'], $user->email);
        $this->assertTrue(Hash::check('password', $user->password));
    }

    /**
     * @test
     */
    public function questionsリレーションでQuestionモデルと正しく関連付けられていること()
    {
        // ユーザーが投稿した質問を作成
        $question1 = $this->createQuestion(['user_id' => $this->user->id]);
        $question2 = $this->createQuestion(['user_id' => $this->user->id]);
        
        $relatedQuestions = $this->user->questions;
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $relatedQuestions);
        $this->assertCount(2, $relatedQuestions);
        
        foreach ($relatedQuestions as $question) {
            $this->assertInstanceOf(Question::class, $question);
            $this->assertEquals($this->user->id, $question->user_id);
        }
    }

    /**
     * @test
     */
    public function questionsリレーションでhasMany関係が正しく設定されていること()
    {
        $relation = $this->user->questions();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * @test
     */
    public function questionsリレーションで質問がない場合空のコレクションを返すこと()
    {
        $newUser = $this->createUser();
        
        $questions = $newUser->questions;
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $questions);
        $this->assertTrue($questions->isEmpty());
        $this->assertCount(0, $questions);
    }

    /**
     * @test
     */
    public function answersリレーションでAnswerモデルと正しく関連付けられていること()
    {
        // 他のユーザーが投稿した質問を作成
        $anotherUser = $this->createUser();
        $question1 = $this->createQuestion(['user_id' => $anotherUser->id]);
        $question2 = $this->createQuestion(['user_id' => $anotherUser->id]);
        
        // 現在のユーザーがそれらの質問に回答
        $answer1 = $this->createAnswer([
            'user_id' => $this->user->id,
            'question_id' => $question1->id
        ]);
        $answer2 = $this->createAnswer([
            'user_id' => $this->user->id,
            'question_id' => $question2->id
        ]);
        
        $relatedAnswers = $this->user->answers;
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $relatedAnswers);
        $this->assertCount(2, $relatedAnswers);
        
        foreach ($relatedAnswers as $answer) {
            $this->assertInstanceOf(Answer::class, $answer);
            $this->assertEquals($this->user->id, $answer->user_id);
        }
    }

    /**
     * @test
     */
    public function answersリレーションでhasMany関係が正しく設定されていること()
    {
        $relation = $this->user->answers();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * @test
     */
    public function answersリレーションで回答がない場合空のコレクションを返すこと()
    {
        $newUser = $this->createUser();
        
        $answers = $newUser->answers;
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $answers);
        $this->assertTrue($answers->isEmpty());
        $this->assertCount(0, $answers);
    }

    /**
     * @test
     */
    public function Userモデルでタイムスタンプが自動的に設定されること()
    {
        $newUser = User::create([
            'name' => 'タイムスタンプテストユーザー',
            'email' => 'timestamp@test.com',
            'password' => Hash::make('password'),
        ]);
        
        $this->assertNotNull($newUser->created_at);
        $this->assertNotNull($newUser->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $newUser->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $newUser->updated_at);
    }

    /**
     * @test
     */
    public function Userモデルの更新でupdated_atが変更されること()
    {
        $originalUpdatedAt = $this->user->updated_at;
        
        // 少し待ってから更新
        sleep(1);
        $this->user->update(['name' => '更新されたユーザー名']);
        
        $this->assertNotEquals($originalUpdatedAt, $this->user->updated_at);
        $this->assertEquals('更新されたユーザー名', $this->user->name);
    }

    /**
     * @test
     */
    public function Userモデルでpasswordがhiddenされていること()
    {
        $userArray = $this->user->toArray();
        
        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
        $this->assertArrayHasKey('name', $userArray);
        $this->assertArrayHasKey('email', $userArray);
    }

    /**
     * @test
     */
    public function Userモデルの削除が正常に動作すること()
    {
        $userId = $this->user->id;
        
        $this->user->delete();
        
        $this->assertNull(User::find($userId));
    }

    /**
     * @test
     */
    public function Userモデルで複数の質問と回答を持つユーザーの関連データが正しく取得できること()
    {
        $anotherUser = $this->createUser();
        
        // 現在のユーザーが投稿した質問
        $question1 = $this->createQuestion(['user_id' => $this->user->id]);
        $question2 = $this->createQuestion(['user_id' => $this->user->id]);
        
        // 他のユーザーが投稿した質問に対する現在のユーザーの回答
        $otherUserQuestion = $this->createQuestion(['user_id' => $anotherUser->id]);
        $answer1 = $this->createAnswer([
            'user_id' => $this->user->id,
            'question_id' => $otherUserQuestion->id
        ]);
        $answer2 = $this->createAnswer([
            'user_id' => $this->user->id,
            'question_id' => $otherUserQuestion->id
        ]);
        
        $userQuestions = $this->user->questions;
        $userAnswers = $this->user->answers;
        
        $this->assertCount(2, $userQuestions);
        $this->assertCount(2, $userAnswers);
        
        // 質問は自分が投稿したもの
        foreach ($userQuestions as $question) {
            $this->assertEquals($this->user->id, $question->user_id);
        }
        
        // 回答は自分が投稿したもの
        foreach ($userAnswers as $answer) {
            $this->assertEquals($this->user->id, $answer->user_id);
        }
    }

    /**
     * @test
     */
    public function Userモデルで他のユーザーの質問と回答は含まれないこと()
    {
        $user2 = $this->createUser();
        
        // それぞれのユーザーが質問を投稿
        $this->createQuestion(['user_id' => $this->user->id]);
        $this->createQuestion(['user_id' => $user2->id]);
        
        // 共通の質問に対してそれぞれが回答
        $commonQuestion = $this->createQuestion(['user_id' => $this->createUser()->id]);
        $this->createAnswer([
            'user_id' => $this->user->id,
            'question_id' => $commonQuestion->id
        ]);
        $this->createAnswer([
            'user_id' => $user2->id,
            'question_id' => $commonQuestion->id
        ]);
        
        $user1Questions = $this->user->questions;
        $user1Answers = $this->user->answers;
        $user2Questions = $user2->questions;
        $user2Answers = $user2->answers;
        
        $this->assertCount(1, $user1Questions);
        $this->assertCount(1, $user1Answers);
        $this->assertCount(1, $user2Questions);
        $this->assertCount(1, $user2Answers);
        
        $this->assertEquals($this->user->id, $user1Questions->first()->user_id);
        $this->assertEquals($this->user->id, $user1Answers->first()->user_id);
        $this->assertEquals($user2->id, $user2Questions->first()->user_id);
        $this->assertEquals($user2->id, $user2Answers->first()->user_id);
    }
}