<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;
use App\Models\Question;
use App\Models\User;

class SimpleSqliteTest extends FeatureTestCase
{
    public function test_can_create_and_retrieve_question()
    {
        // Create a user
        $user = factory(User::class)->create();
        
        // Create a question
        $question = factory(Question::class)->create([
            'user_id' => $user->id,
            'title' => 'Test Question',
            'content' => 'Test Content'
        ]);
        
        // Assert it exists in database
        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'title' => 'Test Question'
        ]);
        
        // Retrieve and assert
        $retrieved = Question::find($question->id);
        $this->assertNotNull($retrieved);
        $this->assertEquals('Test Question', $retrieved->title);
    }
    
    public function test_home_page_loads()
    {
        // Create some test data
        factory(Question::class, 3)->create();
        
        // Try to access home page
        $response = $this->get('/');
        
        // Check if it loads (might be 200 or 302 redirect to login)
        $this->assertContains($response->status(), [200, 302]);
    }
}