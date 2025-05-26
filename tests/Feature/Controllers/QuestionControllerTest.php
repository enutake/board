<?php

namespace Tests\Feature\Controllers;

use Tests\FeatureTestCase;

class QuestionControllerTest extends FeatureTestCase
{
    public function testShowDisplaysQuestionWithAnswers()
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

    public function testShowDisplaysQuestionWithoutAnswers()
    {
        $question = $this->createQuestion();

        $response = $this->get("/questions/{$question->id}");

        $response->assertStatus(200);
        $response->assertViewIs('question.index');
        $response->assertViewHas('data');
    }

    public function testCreateRequiresAuthentication()
    {
        $response = $this->get('/questions/new');

        $response->assertRedirect('/login');
    }

    public function testCreateDisplaysFormWhenAuthenticated()
    {
        $this->actingAsUser();

        $response = $this->get('/questions/new');

        $response->assertStatus(200);
        $response->assertViewIs('question.create');
    }

    public function testStoreRequiresAuthentication()
    {
        $response = $this->post('/questions', [
            'title' => 'Test Question',
            'content' => 'Test content for the question'
        ]);

        $response->assertRedirect('/login');
    }

    public function testStoreCreatesQuestionWhenAuthenticated()
    {
        $user = $this->actingAsUser();

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

    public function testStoreRedirectsToQuestionShow()
    {
        $this->actingAsUser();

        $response = $this->post('/questions', [
            'title' => 'Test Question',
            'content' => 'Test content'
        ]);

        $response->assertStatus(302);
        $this->assertStringContains('/questions/', $response->headers->get('location'));
    }

    public function testStoreHandlesEmptyData()
    {
        $this->actingAsUser();

        $response = $this->post('/questions', []);

        $response->assertStatus(302);
    }
}