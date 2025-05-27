<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;

class AnswerWorkflowTest extends FeatureTestCase
{
    public function testCompleteAnswerCreationWorkflow(): void
    {
        $question = $this->createQuestion([
            'title' => 'Test Question for Answer'
        ]);
        
        $user = $this->createUser([
            'name' => 'Answer User',
            'email' => 'answerer@example.com'
        ]);

        $this->actingAs($user);

        $response = $this->get("/questions/{$question->id}/answers/new");
        $response->assertStatus(200);
        $response->assertViewIs('answer');
        $response->assertSessionHas('userId', $user->id);
        $response->assertSessionHas('questionId', $question->id);

        $answerContent = 'This is a comprehensive answer that provides detailed explanation and meets the minimum character requirements for the application.';

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

        $response = $this->get("/questions/{$question->id}");
        $response->assertStatus(200);
        
        $viewData = $response->viewData('data');
        $this->assertObjectHasAttribute('answers', $viewData);
    }

    public function testAnswerValidationWorkflow(): void
    {
        $question = $this->createQuestion();
        $user = $this->createUser();
        $this->actingAs($user);

        $this->withSession([
            'userId' => $user->id,
            'questionId' => $question->id
        ]);

        $response = $this->post('/answers', [
            'content' => 'short'
        ]);
        $response->assertSessionHasErrors(['content']);

        $longContent = str_repeat('a', 301);
        $response = $this->post('/answers', [
            'content' => $longContent
        ]);
        $response->assertSessionHasErrors(['content']);

        $response = $this->post('/answers', []);
        $response->assertSessionHasErrors(['content']);
    }

    public function testMultipleAnswersWorkflow(): void
    {
        $question = $this->createQuestion();
        $users = $this->createUsers(3);

        foreach ($users as $index => $user) {
            $this->actingAs($user);
            
            $answerContent = "This is answer number " . ($index + 1) . " from user {$user->name}. It provides unique insights and meets the minimum length requirement.";

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
        }

        $response = $this->get("/questions/{$question->id}");
        $response->assertStatus(200);
        
        $viewData = $response->viewData('data');
        $this->assertObjectHasAttribute('answers', $viewData);
    }

    public function testUnauthenticatedUserCannotCreateAnswer(): void
    {
        $question = $this->createQuestion();

        $response = $this->get("/questions/{$question->id}/answers/new");
        $response->assertRedirect('/login');

        $response = $this->post('/answers', [
            'content' => 'This should not be allowed without authentication and proper session data'
        ]);
        $response->assertRedirect('/login');
    }

    public function testAnswerSessionManagementWorkflow(): void
    {
        $question = $this->createQuestion();
        $user = $this->createUser();
        $this->actingAs($user);

        $response = $this->get("/questions/{$question->id}/answers/new");
        $response->assertSessionHas('userId', $user->id);
        $response->assertSessionHas('questionId', $question->id);

        $response = $this->withSession([
            'userId' => $user->id,
            'questionId' => $question->id
        ])->post('/answers', [
            'content' => 'Valid answer content that meets all requirements and should clear session data'
        ]);

        $response->assertSessionMissing('questionId');
        $response->assertRedirect("/questions/{$question->id}");
    }

    public function testAnswerDisplayInQuestionWorkflow(): void
    {
        $question = $this->createQuestionWithAnswers([], 2);

        $response = $this->get("/questions/{$question->id}");
        $response->assertStatus(200);
        
        $viewData = $response->viewData('data');
        $this->assertObjectHasAttribute('question', $viewData);
        $this->assertObjectHasAttribute('answers', $viewData);
        $this->assertEquals($question->id, $viewData->question->id);
    }
}