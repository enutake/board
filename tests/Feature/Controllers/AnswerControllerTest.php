<?php

namespace Tests\Feature\Controllers;

use Tests\FeatureTestCase;

class AnswerControllerTest extends FeatureTestCase
{
    public function testCreateRequiresAuthentication()
    {
        $question = $this->createQuestion();

        $response = $this->get("/questions/{$question->id}/answers/new");

        $response->assertRedirect('/login');
    }

    public function testCreateDisplaysFormWhenAuthenticated()
    {
        $question = $this->createQuestion();
        $this->actingAsUser();

        $response = $this->get("/questions/{$question->id}/answers/new");

        $response->assertStatus(200);
        $response->assertViewIs('answer');
        $response->assertViewHas('data');
        
        $viewData = $response->viewData('data');
        $this->assertObjectHasAttribute('question', $viewData);
    }

    public function testCreateSetsSessionData()
    {
        $question = $this->createQuestion();
        $user = $this->actingAsUser();

        $response = $this->get("/questions/{$question->id}/answers/new");

        $response->assertStatus(200);
        $response->assertSessionHas('userId', $user->id);
        $response->assertSessionHas('questionId', $question->id);
    }

    public function testStoreRequiresAuthentication()
    {
        $response = $this->post('/answers', [
            'content' => 'This is a test answer content that meets minimum length requirements'
        ]);

        $response->assertRedirect('/login');
    }

    public function testStoreCreatesAnswerWhenAuthenticated()
    {
        $question = $this->createQuestion();
        $user = $this->actingAsUser();

        $answerContent = 'This is a test answer content that meets minimum length requirements';

        $response = $this->withSession([
            'userId' => $user->id,
            'questionId' => $question->id
        ])->post('/answers', [
            'content' => $answerContent
        ]);

        $this->assertDatabaseHas('answers', [
            'content' => $answerContent,
            'user_id' => $user->id,
            'question_id' => $question->id
        ]);

        $response->assertRedirect("/questions/{$question->id}");
    }

    public function testStoreValidatesAnswerContent()
    {
        $question = $this->createQuestion();
        $user = $this->actingAsUser();

        $response = $this->withSession([
            'userId' => $user->id,
            'questionId' => $question->id
        ])->post('/answers', [
            'content' => 'short'
        ]);

        $response->assertSessionHasErrors(['content']);
        $this->assertDatabaseMissing('answers', [
            'content' => 'short'
        ]);
    }

    public function testStoreValidatesAnswerContentTooLong()
    {
        $question = $this->createQuestion();
        $user = $this->actingAsUser();

        $longContent = str_repeat('a', 301);

        $response = $this->withSession([
            'userId' => $user->id,
            'questionId' => $question->id
        ])->post('/answers', [
            'content' => $longContent
        ]);

        $response->assertSessionHasErrors(['content']);
        $this->assertDatabaseMissing('answers', [
            'content' => $longContent
        ]);
    }

    public function testStoreRequiresAnswerContent()
    {
        $question = $this->createQuestion();
        $user = $this->actingAsUser();

        $response = $this->withSession([
            'userId' => $user->id,
            'questionId' => $question->id
        ])->post('/answers', []);

        $response->assertSessionHasErrors(['content']);
    }

    public function testStoreClearsSessionAfterSubmission()
    {
        $question = $this->createQuestion();
        $user = $this->actingAsUser();

        $response = $this->withSession([
            'userId' => $user->id,
            'questionId' => $question->id
        ])->post('/answers', [
            'content' => 'This is a valid answer content that meets length requirements'
        ]);

        $response->assertSessionMissing('questionId');
    }
}