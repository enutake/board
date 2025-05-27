<?php

namespace Tests\Feature\Controllers;

use Tests\FeatureTestCase;

class QuestionControllerTest extends FeatureTestCase
{
    public function testShowDisplaysQuestionWithAnswers(): void
    {
        $question = $this->createQuestionWithAnswers();

        $response = $this->get("/questions/{$question->id}");

        $response->assertStatus(200);
        $response->assertViewIs('question.index');
        $response->assertViewHas('data');
        
        $viewData = $response->viewData('data');
        $this->assertObjectHasAttribute('question', $viewData);
        $this->assertObjectHasAttribute('answers', $viewData);
    }

    public function testShowDisplaysQuestionWithoutAnswers(): void
    {
        $question = $this->createQuestion();

        $response = $this->get("/questions/{$question->id}");

        $response->assertStatus(200);
        $response->assertViewIs('question.index');
        $response->assertViewHas('data');
    }

    public function testCreateRequiresAuthentication(): void
    {
        $response = $this->get('/questions/new');

        $response->assertRedirect('/login');
    }

    public function testCreateDisplaysFormWhenAuthenticated(): void
    {
        $this->actingAsUser();

        $response = $this->get('/questions/new');

        $response->assertStatus(200);
        $response->assertViewIs('question.create');
    }

    public function testStoreRequiresAuthentication(): void
    {
        $response = $this->post('/questions', [
            'title' => 'Test Question',
            'content' => 'Test content for the question'
        ]);

        $response->assertRedirect('/login');
    }

    public function testStoreCreatesQuestionWhenAuthenticated(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $questionData = [
            'title' => 'Test Question Title',
            'content' => 'This is test content for the question'
        ];

        $response = $this->post('/questions', $questionData);

        $this->assertDatabaseHas('questions', [
            'title' => $questionData['title'],
            'content' => $questionData['content'],
            'user_id' => $user->id
        ]);

        $response->assertStatus(302);
        $response->assertRedirect();
    }

    public function testStoreRedirectsToQuestionShow(): void
    {
        $this->actingAsUser();

        $response = $this->post('/questions', [
            'title' => 'Test Question',
            'content' => 'Test content'
        ]);

        $response->assertStatus(302);
        $this->assertStringContainsString('/questions/', $response->headers->get('location'));
    }

    public function testStoreHandlesEmptyData(): void
    {
        $this->actingAsUser();

        $response = $this->post('/questions', []);

        // バリデーションが実装されたため、302リダイレクトとバリデーションエラーを期待する
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['title', 'content']);
    }
}