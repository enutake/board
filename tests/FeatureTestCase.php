<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class FeatureTestCase extends TestCase
{
    use RefreshDatabase, TestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Common setup for authenticated tests
     */
    protected function setUpAuthenticated($userType = 'user'): \Tests\TestCase
    {
        switch ($userType) {
            case 'admin':
                return $this->actingAsAdmin();
            default:
                return $this->actingAsUser();
        }
    }

    /**
     * Test JSON API responses
     */
    protected function assertJsonApiResponse($status = 200, $structure = []): self
    {
        $this->assertStatus($status);
        
        if (!empty($structure)) {
            $this->assertJsonStructure($structure);
        }
        
        return $this;
    }

    /**
     * Test successful JSON responses
     */
    protected function assertJsonSuccess($data = null): \Illuminate\Testing\TestResponse
    {
        $expected = ['success' => true];
        
        if ($data !== null) {
            $expected['data'] = $data;
        }
        
        return $this->assertJson($expected);
    }

    /**
     * Test error JSON responses
     */
    protected function assertJsonError($message = null, $status = 400): \Illuminate\Testing\TestResponse
    {
        $this->assertStatus($status);
        
        $expected = ['success' => false];
        
        if ($message !== null) {
            $expected['message'] = $message;
        }
        
        return $this->assertJson($expected);
    }

    /**
     * Test validation error responses
     */
    protected function assertValidationError($fields = []): self
    {
        $this->assertStatus(422);
        
        if (!empty($fields)) {
            $this->assertJsonValidationErrors($fields);
        }
        
        return $this;
    }

    /**
     * Test redirect responses
     */
    protected function assertRedirectResponse($location = null): self
    {
        $this->assertStatus(302);
        
        if ($location !== null) {
            $this->assertRedirect($location);
        }
        
        return $this;
    }
}