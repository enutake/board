<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;

class UserAuthenticationTest extends FeatureTestCase
{
    public function testCompleteUserRegistrationWorkflow()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertViewIs('auth.register');

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'securepassword123',
            'password_confirmation' => 'securepassword123'
        ];

        $response = $this->post('/register', $userData);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
    }

    public function testCompleteUserLoginWorkflow()
    {
        $user = $this->createUser([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function testUserLogoutWorkflow()
    {
        $user = $this->actingAsUser();

        $this->assertAuthenticated();

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function testAuthenticationRequiredWorkflow()
    {
        $question = $this->createQuestion();

        $response = $this->get('/questions/new');
        $response->assertRedirect('/login');

        $response = $this->get("/questions/{$question->id}/answers/new");
        $response->assertRedirect('/login');

        $response = $this->post('/questions', [
            'title' => 'Test Question',
            'content' => 'Test content'
        ]);
        $response->assertRedirect('/login');

        $response = $this->post('/answers', [
            'content' => 'Test answer content that meets minimum requirements'
        ]);
        $response->assertRedirect('/login');
    }

    public function testAuthenticatedUserAccessWorkflow()
    {
        $user = $this->actingAsUser();
        $question = $this->createQuestion();

        $response = $this->get('/questions/new');
        $response->assertStatus(200);

        $response = $this->get("/questions/{$question->id}/answers/new");
        $response->assertStatus(200);

        $response = $this->post('/questions', [
            'title' => 'Authenticated User Question',
            'content' => 'This question is created by an authenticated user'
        ]);
        $response->assertStatus(302);
        $response->assertRedirect();

        $response = $this->withSession([
            'userId' => $user->id,
            'questionId' => $question->id
        ])->post('/answers', [
            'content' => 'This answer is created by an authenticated user with proper session data'
        ]);
        $response->assertRedirect("/questions/{$question->id}");
    }

    public function testInvalidLoginAttemptsWorkflow()
    {
        $user = $this->createUser([
            'email' => 'valid@example.com',
            'password' => bcrypt('correctpassword')
        ]);

        $response = $this->post('/login', [
            'email' => 'valid@example.com',
            'password' => 'wrongpassword'
        ]);
        $response->assertSessionHasErrors();
        $this->assertGuest();

        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'anypassword'
        ]);
        $response->assertSessionHasErrors();
        $this->assertGuest();

        $response = $this->post('/login', [
            'email' => 'invalid-email-format',
            'password' => 'password'
        ]);
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function testPasswordValidationWorkflow()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short'
        ]);
        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123'
        ]);
        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    public function testDuplicateEmailRegistrationWorkflow()
    {
        $existingUser = $this->createUser(['email' => 'existing@example.com']);

        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
        
        $this->assertDatabaseMissing('users', [
            'name' => 'New User',
            'email' => 'existing@example.com'
        ]);
    }
}