<?php

namespace Tests\Feature\Controllers;

use Tests\FeatureTestCase;

class AnswerControllerTest extends FeatureTestCase
{
    public function testCreateRequiresAuthentication(): void
    {
        $question = $this->createQuestion();

        $response = $this->get("/questions/{$question->id}/answers/new");

        $response->assertRedirect('/login');
    }

    public function testCreateDisplaysFormWhenAuthenticated(): void
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

    public function testCreateSetsSessionData(): void
    {
        $question = $this->createQuestion();
        $user = $this->actingAsUser();

        $response = $this->get("/questions/{$question->id}/answers/new");

        $response->assertStatus(200);
        $response->assertSessionHas('userId', $user->id);
        $response->assertSessionHas('questionId', $question->id);
    }

    public function testStoreRequiresAuthentication(): void
    {
        $response = $this->post('/answers', [
            'content' => 'This is a test answer content that meets minimum length requirements'
        ]);

        $response->assertRedirect('/login');
    }

    public function testStoreCreatesAnswerWhenAuthenticated(): void
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

    public function testStoreValidatesAnswerContent(): void
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

    public function testStoreValidatesAnswerContentTooLong(): void
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

    public function testStoreRequiresAnswerContent(): void
    {
        $question = $this->createQuestion();
        $user = $this->actingAsUser();

        $response = $this->withSession([
            'userId' => $user->id,
            'questionId' => $question->id
        ])->post('/answers', []);

        $response->assertSessionHasErrors(['content']);
    }

    public function testStoreClearsSessionAfterSubmission(): void
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