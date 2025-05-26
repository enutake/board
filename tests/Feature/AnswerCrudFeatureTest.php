<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;
use App\Models\User;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AnswerCrudFeatureTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_complete_answer_crud_workflow()
    {
        $questionAuthor = $this->createUser(['name' => 'Question Author']);
        $answerAuthor = $this->createUser(['name' => 'Answer Author']);
        
        // Step 1: Create a question first
        $question = $this->createQuestion([
            'user_id' => $questionAuthor->id,
            'title' => 'How to implement authentication?',
            'content' => 'I need help implementing user authentication in my application.'
        ]);

        // Step 2: Access create answer page
        $createPageResponse = $this->actingAs($answerAuthor)->get('/questions/' . $question->id . '/answers/new');
        $createPageResponse->assertStatus(200);
        $createPageResponse->assertViewIs('answer');
        $createPageResponse->assertViewHas('data');
        
        $createViewData = $createPageResponse->viewData('data');
        $this->assertEquals($question->id, $createViewData->question->id);

        // Verify session data is set
        $createPageResponse->assertSessionHas('userId', $answerAuthor->id);
        $createPageResponse->assertSessionHas('questionId', $question->id);

        // Step 3: Submit answer
        $answerData = [
            'content' => 'You can use Laravel\'s built-in authentication system. Run `php artisan make:auth` to get started.'
        ];

        $storeResponse = $this->actingAs($answerAuthor)->post('/answers', $answerData);
        $storeResponse->assertStatus(302);
        $storeResponse->assertRedirect('/questions/' . $question->id);

        // Verify answer was created in database
        $this->assertDatabaseHas('answers', [
            'content' => 'You can use Laravel\'s built-in authentication system. Run `php artisan make:auth` to get started.',
            'user_id' => $answerAuthor->id,
            'question_id' => $question->id
        ]);

        // Step 4: Verify answer appears on question page
        $questionPageResponse = $this->get('/questions/' . $question->id);
        $questionPageResponse->assertStatus(200);
        
        $questionViewData = $questionPageResponse->viewData('data');
        $this->assertCount(1, $questionViewData->answers);
        
        $answer = $questionViewData->answers[0];
        $this->assertEquals('You can use Laravel\'s built-in authentication system. Run `php artisan make:auth` to get started.', $answer->content);
        $this->assertEquals($answerAuthor->id, $answer->user_id);
        $this->assertEquals($question->id, $answer->question_id);

        // Step 5: Verify session cleanup
        $storeResponse->assertSessionMissing('questionId');
    }

    public function test_multiple_answers_to_same_question()
    {
        $questionAuthor = $this->createUser(['name' => 'Question Author']);
        $user1 = $this->createUser(['name' => 'User 1']);
        $user2 = $this->createUser(['name' => 'User 2']);
        $user3 = $this->createUser(['name' => 'User 3']);
        
        $question = $this->createQuestion([
            'user_id' => $questionAuthor->id,
            'title' => 'Best practices for database design?',
            'content' => 'What are the best practices for designing a database schema?'
        ]);

        $answers = [
            ['user' => $user1, 'content' => 'Always normalize your database to at least 3NF.'],
            ['user' => $user2, 'content' => 'Use foreign keys to maintain referential integrity.'],
            ['user' => $user3, 'content' => 'Consider indexing frequently queried columns.'],
        ];

        foreach ($answers as $answerData) {
            // Access create page to set session
            $this->actingAs($answerData['user'])->get('/questions/' . $question->id . '/answers/new');
            
            // Submit answer
            $response = $this->actingAs($answerData['user'])->post('/answers', [
                'content' => $answerData['content']
            ]);
            
            $response->assertStatus(302);
            $response->assertRedirect('/questions/' . $question->id);
            
            $this->assertDatabaseHas('answers', [
                'content' => $answerData['content'],
                'user_id' => $answerData['user']->id,
                'question_id' => $question->id
            ]);
        }

        // Verify all answers appear on question page
        $questionPageResponse = $this->get('/questions/' . $question->id);
        $questionPageResponse->assertStatus(200);
        
        $viewData = $questionPageResponse->viewData('data');
        $this->assertCount(3, $viewData->answers);

        // Verify each answer content
        $answerContents = collect($viewData->answers)->pluck('content')->toArray();
        $this->assertContains('Always normalize your database to at least 3NF.', $answerContents);
        $this->assertContains('Use foreign keys to maintain referential integrity.', $answerContents);
        $this->assertContains('Consider indexing frequently queried columns.', $answerContents);
    }

    public function test_answer_creation_with_special_characters()
    {
        $user = $this->createUser();
        $question = $this->createQuestion(['user_id' => $user->id]);
        
        $specialContent = 'Answer with special characters: áéíóú, çñü, <code>console.log("Hello");</code>, &amp;, 日本語の回答, русский ответ';
        
        // Access create page
        $this->actingAs($user)->get('/questions/' . $question->id . '/answers/new');
        
        // Submit answer
        $response = $this->actingAs($user)->post('/answers', [
            'content' => $specialContent
        ]);
        
        $response->assertStatus(302);
        
        $this->assertDatabaseHas('answers', [
            'content' => $specialContent,
            'user_id' => $user->id,
            'question_id' => $question->id
        ]);

        // Verify content display
        $questionPageResponse = $this->get('/questions/' . $question->id);
        $viewData = $questionPageResponse->viewData('data');
        
        $this->assertCount(1, $viewData->answers);
        $this->assertEquals($specialContent, $viewData->answers[0]->content);
    }

    public function test_answer_creation_with_long_content()
    {
        $user = $this->createUser();
        $question = $this->createQuestion(['user_id' => $user->id]);
        
        $longContent = str_repeat('This is a very detailed answer with lots of information. ', 200);
        
        // Access create page
        $this->actingAs($user)->get('/questions/' . $question->id . '/answers/new');
        
        // Submit answer
        $response = $this->actingAs($user)->post('/answers', [
            'content' => $longContent
        ]);
        
        $response->assertStatus(302);
        
        $this->assertDatabaseHas('answers', [
            'user_id' => $user->id,
            'question_id' => $question->id
        ]);

        $answer = Answer::where('user_id', $user->id)
                       ->where('question_id', $question->id)
                       ->first();
        
        $this->assertEquals($longContent, $answer->content);
    }

    public function test_answer_creation_with_markdown_content()
    {
        $user = $this->createUser();
        $question = $this->createQuestion(['user_id' => $user->id]);
        
        $markdownContent = "## Solution\n\n**Step 1:** First do this\n\n*Step 2:* Then do that\n\n```php\n\$result = process();\necho \$result;\n```\n\n- Point 1\n- Point 2\n\n[Documentation](https://example.com)";
        
        // Access create page
        $this->actingAs($user)->get('/questions/' . $question->id . '/answers/new');
        
        // Submit answer
        $response = $this->actingAs($user)->post('/answers', [
            'content' => $markdownContent
        ]);
        
        $response->assertStatus(302);
        
        $this->assertDatabaseHas('answers', [
            'content' => $markdownContent,
            'user_id' => $user->id,
            'question_id' => $question->id
        ]);
    }

    public function test_answer_validation_workflow()
    {
        $user = $this->createUser();
        $question = $this->createQuestion(['user_id' => $user->id]);
        
        // Access create page to set session
        $this->actingAs($user)->get('/questions/' . $question->id . '/answers/new');
        
        // Test with empty content (should trigger validation)
        $response = $this->actingAs($user)->post('/answers', [
            'content' => ''
        ]);
        
        // The response depends on AnswerRequest validation rules
        // This should redirect back with validation errors
        $response->assertStatus(302);
        
        // Verify empty answer is not created
        $this->assertDatabaseMissing('answers', [
            'content' => '',
            'user_id' => $user->id,
            'question_id' => $question->id
        ]);
    }

    public function test_answer_session_management()
    {
        $user = $this->createUser();
        $question1 = $this->createQuestion(['user_id' => $user->id, 'title' => 'Question 1']);
        $question2 = $this->createQuestion(['user_id' => $user->id, 'title' => 'Question 2']);
        
        // Access create page for question 1
        $response1 = $this->actingAs($user)->get('/questions/' . $question1->id . '/answers/new');
        $response1->assertSessionHas('questionId', $question1->id);
        
        // Access create page for question 2 (should update session)
        $response2 = $this->actingAs($user)->get('/questions/' . $question2->id . '/answers/new');
        $response2->assertSessionHas('questionId', $question2->id);
        
        // Submit answer (should use the latest session data - question 2)
        $storeResponse = $this->actingAs($user)->post('/answers', [
            'content' => 'Answer to question 2'
        ]);
        
        $storeResponse->assertStatus(302);
        $storeResponse->assertRedirect('/questions/' . $question2->id);
        
        $this->assertDatabaseHas('answers', [
            'content' => 'Answer to question 2',
            'user_id' => $user->id,
            'question_id' => $question2->id
        ]);
        
        // Verify no answer was created for question 1
        $this->assertDatabaseMissing('answers', [
            'question_id' => $question1->id
        ]);
    }

    public function test_answer_author_can_answer_own_question()
    {
        $user = $this->createUser();
        $question = $this->createQuestion([
            'user_id' => $user->id,
            'title' => 'My own question',
            'content' => 'Can I answer my own question?'
        ]);
        
        // User answers their own question
        $this->actingAs($user)->get('/questions/' . $question->id . '/answers/new');
        
        $response = $this->actingAs($user)->post('/answers', [
            'content' => 'Actually, I figured it out myself!'
        ]);
        
        $response->assertStatus(302);
        $response->assertRedirect('/questions/' . $question->id);
        
        $this->assertDatabaseHas('answers', [
            'content' => 'Actually, I figured it out myself!',
            'user_id' => $user->id,
            'question_id' => $question->id
        ]);
    }

    public function test_answer_timestamps_preservation()
    {
        $user = $this->createUser();
        $question = $this->createQuestion(['user_id' => $user->id]);
        
        $this->actingAs($user)->get('/questions/' . $question->id . '/answers/new');
        
        $beforeCreation = now();
        
        $response = $this->actingAs($user)->post('/answers', [
            'content' => 'Timestamp test answer'
        ]);
        
        $afterCreation = now();
        
        $response->assertStatus(302);
        
        $answer = Answer::where('content', 'Timestamp test answer')->first();
        $this->assertNotNull($answer);
        
        $createdAt = $answer->created_at;
        $this->assertGreaterThanOrEqual($beforeCreation, $createdAt);
        $this->assertLessThanOrEqual($afterCreation, $createdAt);
        
        $this->assertNotNull($answer->updated_at);
        $this->assertEquals($answer->created_at->format('Y-m-d H:i:s'), $answer->updated_at->format('Y-m-d H:i:s'));
    }

    public function test_concurrent_answer_creation()
    {
        $question = $this->createQuestion();
        $user1 = $this->createUser(['name' => 'User 1']);
        $user2 = $this->createUser(['name' => 'User 2']);
        
        // Both users access create page
        $this->actingAs($user1)->get('/questions/' . $question->id . '/answers/new');
        $this->actingAs($user2)->get('/questions/' . $question->id . '/answers/new');
        
        // Both users submit answers
        $response1 = $this->actingAs($user1)->post('/answers', [
            'content' => 'First user answer'
        ]);
        
        $response2 = $this->actingAs($user2)->post('/answers', [
            'content' => 'Second user answer'
        ]);
        
        $response1->assertStatus(302);
        $response2->assertStatus(302);
        
        // Both answers should be created successfully
        $this->assertDatabaseHas('answers', [
            'content' => 'First user answer',
            'user_id' => $user1->id,
            'question_id' => $question->id
        ]);
        
        $this->assertDatabaseHas('answers', [
            'content' => 'Second user answer',
            'user_id' => $user2->id,
            'question_id' => $question->id
        ]);
        
        // Verify both answers appear on question page
        $questionPageResponse = $this->get('/questions/' . $question->id);
        $viewData = $questionPageResponse->viewData('data');
        $this->assertCount(2, $viewData->answers);
    }
}