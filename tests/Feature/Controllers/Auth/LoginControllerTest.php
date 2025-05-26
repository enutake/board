<?php

namespace Tests\Feature\Controllers\Auth;

use Tests\FeatureTestCase;

class LoginControllerTest extends FeatureTestCase
{
    public function testLoginFormDisplaysSuccessfully()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function testUserCanLoginWithValidCredentials()
    {
        $user = $this->createUser([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }

    public function testUserCannotLoginWithInvalidCredentials()
    {
        $user = $this->createUser([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function testLoginValidatesRequiredFields()
    {
        $response = $this->post('/login', []);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function testLoginValidatesEmailFormat()
    {
        $response = $this->post('/login', [
            'email' => 'invalid-email',
            'password' => 'password123'
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function testUserCanLogout()
    {
        $user = $this->actingAsUser();

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}