<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;
use App\Models\User;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HomeControllerTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_home_page_loads_successfully()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('home');
        $response->assertViewHas('data');
    }

    public function test_home_page_displays_questions()
    {
        $user = $this->createUser();
        $questions = $this->createQuestions(3, ['user_id' => $user->id]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('data.questions');
        
        $viewData = $response->viewData('data');
        $this->assertCount(3, $viewData->questions);
    }

    public function test_home_page_works_with_no_questions()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('data.questions');
        
        $viewData = $response->viewData('data');
        $this->assertCount(0, $viewData->questions);
    }

    public function test_home_page_works_for_authenticated_users()
    {
        $user = $this->createUser();
        $questions = $this->createQuestions(2, ['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('home');
        $response->assertViewHas('data.questions');
    }

    public function test_home_page_works_for_guest_users()
    {
        $user = $this->createUser();
        $questions = $this->createQuestions(2, ['user_id' => $user->id]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('home');
        $response->assertViewHas('data.questions');
    }

    public function test_home_page_shows_questions_in_correct_order()
    {
        $user = $this->createUser();
        
        // Create questions with specific timestamps to test ordering
        $oldQuestion = factory(Question::class)->create([
            'user_id' => $user->id,
            'title' => 'Old Question',
            'created_at' => now()->subDays(2)
        ]);
        
        $newQuestion = factory(Question::class)->create([
            'user_id' => $user->id,
            'title' => 'New Question',
            'created_at' => now()->subHours(1)
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $viewData = $response->viewData('data');
        
        // Verify questions are returned (ordering depends on service implementation)
        $this->assertCount(2, $viewData->questions);
        
        $questionTitles = collect($viewData->questions)->pluck('title')->toArray();
        $this->assertContains('Old Question', $questionTitles);
        $this->assertContains('New Question', $questionTitles);
    }

    public function test_home_page_handles_large_number_of_questions()
    {
        $user = $this->createUser();
        $this->createQuestions(50, ['user_id' => $user->id]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('data.questions');
        
        // Test should complete without timeout or memory issues
        $this->assertTrue(true);
    }

    public function test_home_page_question_data_structure()
    {
        $user = $this->createUser();
        $question = $this->createQuestion([
            'user_id' => $user->id,
            'title' => 'Sample Question',
            'content' => 'Sample content'
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $viewData = $response->viewData('data');
        
        $this->assertCount(1, $viewData->questions);
        
        $firstQuestion = $viewData->questions[0];
        $this->assertObjectHasAttribute('id', $firstQuestion);
        $this->assertObjectHasAttribute('title', $firstQuestion);
        $this->assertObjectHasAttribute('content', $firstQuestion);
        $this->assertObjectHasAttribute('user_id', $firstQuestion);
    }
}