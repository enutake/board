<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthenticationFeatureTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_create_question_page()
    {
        $response = $this->get('/questions/new');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_create_question()
    {
        $questionData = [
            'title' => 'Test Question',
            'content' => 'Test content'
        ];

        $response = $this->post('/questions', $questionData);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $this->assertDatabaseMissing('questions', ['title' => 'Test Question']);
    }

    public function test_guest_cannot_access_create_answer_page()
    {
        $user = $this->createUser();
        $question = $this->createQuestion(['user_id' => $user->id]);

        $response = $this->get('/questions/' . $question->id . '/answers/new');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_create_answer()
    {
        $user = $this->createUser();
        $question = $this->createQuestion(['user_id' => $user->id]);
        
        $answerData = [
            'content' => 'Test answer content'
        ];

        $response = $this->post('/answers', $answerData);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $this->assertDatabaseMissing('answers', ['content' => 'Test answer content']);
    }

    public function test_authenticated_user_can_access_protected_routes()
    {
        $user = $this->createUser();
        $question = $this->createQuestion(['user_id' => $user->id]);

        // Test question create page
        $createQuestionResponse = $this->actingAs($user)->get('/questions/new');
        $createQuestionResponse->assertStatus(200);

        // Test answer create page
        $createAnswerResponse = $this->actingAs($user)->get('/questions/' . $question->id . '/answers/new');
        $createAnswerResponse->assertStatus(200);
    }

    public function test_authenticated_user_can_create_content()
    {
        $user = $this->createUser();

        // Test question creation
        $questionData = [
            'title' => 'Authenticated User Question',
            'content' => 'This question is created by authenticated user'
        ];

        $questionResponse = $this->actingAs($user)->post('/questions', $questionData);
        $questionResponse->assertStatus(302);
        
        $this->assertDatabaseHas('questions', [
            'title' => 'Authenticated User Question',
            'user_id' => $user->id
        ]);

        // Test answer creation
        $question = $this->createQuestion(['user_id' => $user->id]);
        $this->actingAs($user)->get('/questions/' . $question->id . '/answers/new');
        
        $answerData = [
            'content' => 'This answer is created by authenticated user'
        ];

        $answerResponse = $this->actingAs($user)->post('/answers', $answerData);
        $answerResponse->assertStatus(302);
        
        $this->assertDatabaseHas('answers', [
            'content' => 'This answer is created by authenticated user',
            'user_id' => $user->id
        ]);
    }

    public function test_user_can_view_public_content_without_authentication()
    {
        $user = $this->createUser();
        $question = $this->createQuestionWithAnswers(['user_id' => $user->id], 2);

        // Test home page
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertViewIs('home');

        // Test question show page
        $questionResponse = $this->get('/questions/' . $question->id);
        $questionResponse->assertStatus(200);
        $questionResponse->assertViewIs('question.index');
    }

    public function test_authentication_state_persistence()
    {
        $user = $this->createUser();

        // Log in user
        $this->actingAs($user);

        // Make multiple requests to ensure authentication persists
        $response1 = $this->get('/questions/new');
        $response1->assertStatus(200);

        $response2 = $this->get('/questions/new');
        $response2->assertStatus(200);

        // Create content to ensure user context is maintained
        $questionData = [
            'title' => 'Persistence Test Question',
            'content' => 'Testing authentication persistence'
        ];

        $response3 = $this->post('/questions', $questionData);
        $response3->assertStatus(302);
        
        $this->assertDatabaseHas('questions', [
            'title' => 'Persistence Test Question',
            'user_id' => $user->id
        ]);
    }

    public function test_different_users_create_separate_content()
    {
        $user1 = $this->createUser(['name' => 'User One', 'email' => 'user1@example.com']);
        $user2 = $this->createUser(['name' => 'User Two', 'email' => 'user2@example.com']);

        // User 1 creates a question
        $question1Data = [
            'title' => 'User 1 Question',
            'content' => 'Question by user 1'
        ];

        $response1 = $this->actingAs($user1)->post('/questions', $question1Data);
        $response1->assertStatus(302);

        // User 2 creates a question
        $question2Data = [
            'title' => 'User 2 Question',
            'content' => 'Question by user 2'
        ];

        $response2 = $this->actingAs($user2)->post('/questions', $question2Data);
        $response2->assertStatus(302);

        // Verify both questions exist with correct ownership
        $this->assertDatabaseHas('questions', [
            'title' => 'User 1 Question',
            'user_id' => $user1->id
        ]);

        $this->assertDatabaseHas('questions', [
            'title' => 'User 2 Question',
            'user_id' => $user2->id
        ]);

        // Verify cross-ownership doesn't exist
        $this->assertDatabaseMissing('questions', [
            'title' => 'User 1 Question',
            'user_id' => $user2->id
        ]);

        $this->assertDatabaseMissing('questions', [
            'title' => 'User 2 Question',
            'user_id' => $user1->id
        ]);
    }

    public function test_user_context_in_answer_creation()
    {
        $questionAuthor = $this->createUser(['name' => 'Question Author']);
        $answerAuthor = $this->createUser(['name' => 'Answer Author']);
        
        $question = $this->createQuestion([
            'user_id' => $questionAuthor->id,
            'title' => 'Test Question for Answer Context',
            'content' => 'Testing user context'
        ]);

        // Answer author creates an answer
        $this->actingAs($answerAuthor)->get('/questions/' . $question->id . '/answers/new');
        
        $answerResponse = $this->actingAs($answerAuthor)->post('/answers', [
            'content' => 'Answer with correct user context'
        ]);

        $answerResponse->assertStatus(302);
        
        $this->assertDatabaseHas('answers', [
            'content' => 'Answer with correct user context',
            'user_id' => $answerAuthor->id, // Should be answer author, not question author
            'question_id' => $question->id
        ]);

        // Verify the answer is not attributed to the question author
        $this->assertDatabaseMissing('answers', [
            'content' => 'Answer with correct user context',
            'user_id' => $questionAuthor->id
        ]);
    }

    public function test_authentication_middleware_consistency()
    {
        $user = $this->createUser();
        $question = $this->createQuestion(['user_id' => $user->id]);

        // Define protected routes
        $protectedRoutes = [
            ['method' => 'get', 'uri' => '/questions/new'],
            ['method' => 'post', 'uri' => '/questions'],
            ['method' => 'get', 'uri' => '/questions/' . $question->id . '/answers/new'],
            ['method' => 'post', 'uri' => '/answers'],
        ];

        // Test each protected route without authentication
        foreach ($protectedRoutes as $route) {
            $response = $this->call($route['method'], $route['uri']);
            $response->assertStatus(302);
            $response->assertRedirect('/login');
        }

        // Test each protected route with authentication
        foreach ($protectedRoutes as $route) {
            if ($route['method'] === 'get') {
                $response = $this->actingAs($user)->get($route['uri']);
                $response->assertStatus(200);
            } else {
                // For POST routes, we need appropriate data
                $data = [];
                if ($route['uri'] === '/questions') {
                    $data = ['title' => 'Test', 'content' => 'Test'];
                } elseif ($route['uri'] === '/answers') {
                    // Set session first
                    $this->actingAs($user)->get('/questions/' . $question->id . '/answers/new');
                    $data = ['content' => 'Test answer'];
                }
                
                $response = $this->actingAs($user)->post($route['uri'], $data);
                $response->assertStatus(302); // Redirect after successful operation
            }
        }
    }

    public function test_session_data_isolation_between_users()
    {
        $user1 = $this->createUser(['name' => 'User 1']);
        $user2 = $this->createUser(['name' => 'User 2']);
        $question = $this->createQuestion();

        // User 1 sets up session for answering
        $response1 = $this->actingAs($user1)->get('/questions/' . $question->id . '/answers/new');
        $response1->assertSessionHas('userId', $user1->id);
        $response1->assertSessionHas('questionId', $question->id);

        // User 2 has their own session (simulating different browser/session)
        $response2 = $this->actingAs($user2)->get('/questions/' . $question->id . '/answers/new');
        
        // In a real scenario, these would be separate sessions
        // This test verifies that the correct user ID is used when creating answers
        $answerResponse1 = $this->actingAs($user1)->post('/answers', [
            'content' => 'Answer from user 1'
        ]);

        $answerResponse2 = $this->actingAs($user2)->post('/answers', [
            'content' => 'Answer from user 2'
        ]);

        $answerResponse1->assertStatus(302);
        $answerResponse2->assertStatus(302);

        // Verify correct user attribution
        $this->assertDatabaseHas('answers', [
            'content' => 'Answer from user 1',
            'user_id' => $user1->id
        ]);

        $this->assertDatabaseHas('answers', [
            'content' => 'Answer from user 2',
            'user_id' => $user2->id
        ]);
    }
}