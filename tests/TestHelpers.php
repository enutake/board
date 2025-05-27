<?php

namespace Tests;

use App\Models\User;
use App\Models\Question;
use App\Models\Answer;
use App\Models\TagMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

trait TestHelpers
{
    use RefreshDatabase, WithFaker;

    protected function createUser($attributes = []): User
    {
        return factory(User::class)->create($attributes);
    }

    protected function createUsers($count = 3, $attributes = []): \Illuminate\Database\Eloquent\Collection
    {
        return factory(User::class, $count)->create($attributes);
    }

    protected function createQuestion($attributes = []): Question
    {
        return factory(Question::class)->create($attributes);
    }

    protected function createQuestions($count = 3, $attributes = []): \Illuminate\Database\Eloquent\Collection
    {
        return factory(Question::class, $count)->create($attributes);
    }

    protected function createAnswer($attributes = []): Answer
    {
        return factory(Answer::class)->create($attributes);
    }

    protected function createAnswers($count = 3, $attributes = []): \Illuminate\Database\Eloquent\Collection
    {
        return factory(Answer::class, $count)->create($attributes);
    }

    protected function createTagMaster($attributes = []): TagMaster
    {
        return factory(TagMaster::class)->create($attributes);
    }

    protected function createQuestionWithAnswers($questionAttributes = [], $answerCount = 2): Question
    {
        $question = $this->createQuestion($questionAttributes);
        
        $answers = factory(Answer::class, $answerCount)->create([
            'question_id' => $question->id,
        ]);

        return $question->load('answers');
    }

    protected function actingAsUser($user = null): \Tests\TestCase
    {
        $user = $user ?: $this->createUser();
        $this->actingAs($user);
        return $this;
    }

    protected function actingAsAdmin(): \Tests\TestCase
    {
        $admin = factory(User::class)->states('admin')->create();
        $this->actingAs($admin);
        return $this;
    }

    protected function assertDatabaseHasModel($model, $attributes = []): void
    {
        $this->assertDatabaseHas($model->getTable(), array_merge([
            'id' => $model->id,
        ], $attributes));
    }

    protected function assertDatabaseMissingModel($model): void
    {
        $this->assertDatabaseMissing($model->getTable(), [
            'id' => $model->id,
        ]);
    }

    protected function assertResponseContainsText($text): void
    {
        $this->assertStringContainsString($text, $this->response->getContent());
    }

    protected function assertResponseDoesNotContainText($text): void
    {
        $this->assertStringNotContainsString($text, $this->response->getContent());
    }

    protected function refreshTestDatabase(): void
    {
        $this->artisan('migrate:fresh');
    }

    protected function seedTestData(): void
    {
        $this->createUsers(5);
        $this->createQuestions(10);
        $this->createAnswers(20);
    }
}