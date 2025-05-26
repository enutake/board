<?php

namespace Tests\Feature\Controllers\Auth;

use Tests\FeatureTestCase;

class RegisterControllerTest extends FeatureTestCase
{
    public function testRegistrationFormDisplaysSuccessfully()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    public function testUserCanRegisterWithValidData()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->post('/register', $userData);

        $response->assertRedirect('/home');
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
        $this->assertAuthenticated();
    }

    public function testRegistrationValidatesRequiredFields()
    {
        $response = $this->post('/register', []);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();
    }

    public function testRegistrationValidatesEmailFormat()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function testRegistrationValidatesUniqueEmail()
    {
        $existingUser = $this->createUser(['email' => 'test@example.com']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function testRegistrationValidatesPasswordConfirmation()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword'
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    public function testRegistrationValidatesPasswordLength()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short'
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }
}