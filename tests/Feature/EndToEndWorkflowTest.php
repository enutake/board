<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;
use App\Models\User;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EndToEndWorkflowTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_complete_question_and_answer_workflow()
    {
        // Step 1: Create users
        $questionAuthor = $this->createUser([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com'
        ]);
        
        $answerAuthor1 = $this->createUser([
            'name' => 'Bob Smith',
            'email' => 'bob@example.com'
        ]);
        
        $answerAuthor2 = $this->createUser([
            'name' => 'Carol Davis',
            'email' => 'carol@example.com'
        ]);

        // Step 2: Question author visits home page (empty state)
        $homeEmptyResponse = $this->actingAs($questionAuthor)->get('/');
        $homeEmptyResponse->assertStatus(200);
        $homeEmptyResponse->assertViewIs('home');
        
        $homeEmptyData = $homeEmptyResponse->viewData('data');
        $this->assertCount(0, $homeEmptyData->questions);

        // Step 3: Question author creates a new question
        $createQuestionPageResponse = $this->actingAs($questionAuthor)->get('/questions/new');
        $createQuestionPageResponse->assertStatus(200);
        $createQuestionPageResponse->assertViewIs('question.create');

        $questionData = [
            'title' => 'How to implement real-time notifications in Laravel?',
            'content' => 'I need to implement real-time notifications for my Laravel application. Users should receive notifications when someone comments on their posts. What\'s the best approach using Laravel\'s built-in features?'
        ];

        $storeQuestionResponse = $this->actingAs($questionAuthor)->post('/questions', $questionData);
        $storeQuestionResponse->assertStatus(302);

        // Get the created question
        $question = Question::where('title', 'How to implement real-time notifications in Laravel?')->first();
        $this->assertNotNull($question);
        $storeQuestionResponse->assertRedirect('/questions/' . $question->id);

        // Step 4: Verify question appears on home page
        $homeWithQuestionResponse = $this->get('/');
        $homeWithQuestionResponse->assertStatus(200);
        
        $homeWithQuestionData = $homeWithQuestionResponse->viewData('data');
        $this->assertCount(1, $homeWithQuestionData->questions);
        
        $homeQuestion = $homeWithQuestionData->questions[0];
        $this->assertEquals('How to implement real-time notifications in Laravel?', $homeQuestion->title);

        // Step 5: Guest user views the question (no answers yet)
        $questionPageNoAnswersResponse = $this->get('/questions/' . $question->id);
        $questionPageNoAnswersResponse->assertStatus(200);
        $questionPageNoAnswersResponse->assertViewIs('question.index');
        
        $questionNoAnswersData = $questionPageNoAnswersResponse->viewData('data');
        $this->assertEquals($question->id, $questionNoAnswersData->question->id);
        $this->assertCount(0, $questionNoAnswersData->answers);

        // Step 6: First user creates an answer
        $createAnswer1PageResponse = $this->actingAs($answerAuthor1)->get('/questions/' . $question->id . '/answers/new');
        $createAnswer1PageResponse->assertStatus(200);
        $createAnswer1PageResponse->assertViewIs('answer');
        $createAnswer1PageResponse->assertSessionHas('questionId', $question->id);
        $createAnswer1PageResponse->assertSessionHas('userId', $answerAuthor1->id);

        $answer1Data = [
            'content' => 'You can use Laravel Broadcasting with Pusher or Socket.io. First, install the pusher PHP SDK: `composer require pusher/pusher-php-server`. Then configure your broadcasting settings in config/broadcasting.php and set up event classes to broadcast notifications.'
        ];

        $storeAnswer1Response = $this->actingAs($answerAuthor1)->post('/answers', $answer1Data);
        $storeAnswer1Response->assertStatus(302);
        $storeAnswer1Response->assertRedirect('/questions/' . $question->id);
        $storeAnswer1Response->assertSessionMissing('questionId');

        // Step 7: Second user views question and sees the first answer
        $questionPageOneAnswerResponse = $this->actingAs($answerAuthor2)->get('/questions/' . $question->id);
        $questionPageOneAnswerResponse->assertStatus(200);
        
        $questionOneAnswerData = $questionPageOneAnswerResponse->viewData('data');
        $this->assertCount(1, $questionOneAnswerData->answers);
        
        $firstAnswer = $questionOneAnswerData->answers[0];
        $this->assertStringContainsString('Laravel Broadcasting', $firstAnswer->content);
        $this->assertEquals($answerAuthor1->id, $firstAnswer->user_id);

        // Step 8: Second user adds their own answer
        $createAnswer2PageResponse = $this->actingAs($answerAuthor2)->get('/questions/' . $question->id . '/answers/new');
        $createAnswer2PageResponse->assertStatus(200);

        $answer2Data = [
            'content' => 'Another approach is to use Laravel Echo with WebSockets. You can also implement polling with AJAX if real-time isn\'t critical. For database notifications, use Laravel\'s built-in notification system: `php artisan make:notification PostCommented`.'
        ];

        $storeAnswer2Response = $this->actingAs($answerAuthor2)->post('/answers', $answer2Data);
        $storeAnswer2Response->assertStatus(302);
        $storeAnswer2Response->assertRedirect('/questions/' . $question->id);

        // Step 9: Question author views their question with all answers
        $questionPageFinalResponse = $this->actingAs($questionAuthor)->get('/questions/' . $question->id);
        $questionPageFinalResponse->assertStatus(200);
        
        $questionFinalData = $questionPageFinalResponse->viewData('data');
        $this->assertEquals($question->id, $questionFinalData->question->id);
        $this->assertCount(2, $questionFinalData->answers);

        // Verify answer details
        $answerContents = collect($questionFinalData->answers)->pluck('content')->toArray();
        $this->assertContains($answer1Data['content'], $answerContents);
        $this->assertContains($answer2Data['content'], $answerContents);

        $answerUserIds = collect($questionFinalData->answers)->pluck('user_id')->toArray();
        $this->assertContains($answerAuthor1->id, $answerUserIds);
        $this->assertContains($answerAuthor2->id, $answerUserIds);

        // Step 10: Verify database state
        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'title' => 'How to implement real-time notifications in Laravel?',
            'user_id' => $questionAuthor->id
        ]);

        $this->assertDatabaseHas('answers', [
            'question_id' => $question->id,
            'user_id' => $answerAuthor1->id,
            'content' => $answer1Data['content']
        ]);

        $this->assertDatabaseHas('answers', [
            'question_id' => $question->id,
            'user_id' => $answerAuthor2->id,
            'content' => $answer2Data['content']
        ]);

        // Step 11: Final home page verification shows question with context
        $finalHomeResponse = $this->get('/');
        $finalHomeResponse->assertStatus(200);
        
        $finalHomeData = $finalHomeResponse->viewData('data');
        $this->assertCount(1, $finalHomeData->questions);
    }

    public function test_multiple_questions_with_mixed_answers_workflow()
    {
        $user1 = $this->createUser(['name' => 'Developer 1']);
        $user2 = $this->createUser(['name' => 'Developer 2']);
        $user3 = $this->createUser(['name' => 'Expert']);

        // User 1 creates first question
        $question1Data = [
            'title' => 'Best practices for Laravel testing?',
            'content' => 'What are the best practices for writing tests in Laravel?'
        ];

        $this->actingAs($user1)->post('/questions', $question1Data);
        $question1 = Question::where('title', 'Best practices for Laravel testing?')->first();

        // User 2 creates second question
        $question2Data = [
            'title' => 'How to optimize Laravel performance?',
            'content' => 'My Laravel app is running slowly. How can I optimize it?'
        ];

        $this->actingAs($user2)->post('/questions', $question2Data);
        $question2 = Question::where('title', 'How to optimize Laravel performance?')->first();

        // Expert answers first question
        $this->actingAs($user3)->get('/questions/' . $question1->id . '/answers/new');
        $this->actingAs($user3)->post('/answers', [
            'content' => 'Use Feature tests for HTTP endpoints, Unit tests for business logic, and mock external dependencies.'
        ]);

        // User 2 also answers first question
        $this->actingAs($user2)->get('/questions/' . $question1->id . '/answers/new');
        $this->actingAs($user2)->post('/answers', [
            'content' => 'Don\'t forget to use factories for test data and RefreshDatabase trait.'
        ]);

        // Expert answers second question
        $this->actingAs($user3)->get('/questions/' . $question2->id . '/answers/new');
        $this->actingAs($user3)->post('/answers', [
            'content' => 'Enable query caching, use eager loading, optimize database queries, and implement Redis for caching.'
        ]);

        // Verify final state
        $home = $this->get('/');
        $homeData = $home->viewData('data');
        $this->assertCount(2, $homeData->questions);

        $question1Page = $this->get('/questions/' . $question1->id);
        $question1Data = $question1Page->viewData('data');
        $this->assertCount(2, $question1Data->answers);

        $question2Page = $this->get('/questions/' . $question2->id);
        $question2PageData = $question2Page->viewData('data');
        $this->assertCount(1, $question2PageData->answers);
    }

    public function test_user_can_answer_own_question_workflow()
    {
        $user = $this->createUser(['name' => 'Self Helper']);

        // User creates a question
        $questionData = [
            'title' => 'How to solve my own problem?',
            'content' => 'I encountered an issue and I\'m working on solving it myself.'
        ];

        $this->actingAs($user)->post('/questions', $questionData);
        $question = Question::where('title', 'How to solve my own problem?')->first();

        // User later solves it and answers their own question
        $this->actingAs($user)->get('/questions/' . $question->id . '/answers/new');
        $this->actingAs($user)->post('/answers', [
            'content' => 'I figured it out! The solution was to check the configuration file and update the settings.'
        ]);

        // Verify the workflow worked
        $questionPage = $this->get('/questions/' . $question->id);
        $questionData = $questionPage->viewData('data');
        
        $this->assertCount(1, $questionData->answers);
        $this->assertEquals($user->id, $questionData->question->user_id);
        $this->assertEquals($user->id, $questionData->answers[0]->user_id);
    }

    public function test_guest_to_authenticated_user_workflow()
    {
        $user = $this->createUser();
        $question = $this->createQuestionWithAnswers([], 2);

        // Step 1: Guest visits home and question page
        $guestHomeResponse = $this->get('/');
        $guestHomeResponse->assertStatus(200);

        $guestQuestionResponse = $this->get('/questions/' . $question->id);
        $guestQuestionResponse->assertStatus(200);

        // Step 2: Guest tries to create question (should redirect to login)
        $guestCreateAttempt = $this->get('/questions/new');
        $guestCreateAttempt->assertStatus(302);
        $guestCreateAttempt->assertRedirect('/login');

        // Step 3: Guest tries to answer (should redirect to login)
        $guestAnswerAttempt = $this->get('/questions/' . $question->id . '/answers/new');
        $guestAnswerAttempt->assertStatus(302);
        $guestAnswerAttempt->assertRedirect('/login');

        // Step 4: User authenticates and can now access protected features
        $authCreateResponse = $this->actingAs($user)->get('/questions/new');
        $authCreateResponse->assertStatus(200);

        $authAnswerResponse = $this->actingAs($user)->get('/questions/' . $question->id . '/answers/new');
        $authAnswerResponse->assertStatus(200);

        // Step 5: User creates content successfully
        $this->actingAs($user)->post('/questions', [
            'title' => 'Authenticated User Question',
            'content' => 'Now I can create questions!'
        ]);

        $this->assertDatabaseHas('questions', [
            'title' => 'Authenticated User Question',
            'user_id' => $user->id
        ]);
    }

    public function test_error_recovery_workflow()
    {
        $user = $this->createUser();
        $question = $this->createQuestion(['user_id' => $user->id]);

        // Step 1: User starts answering process
        $this->actingAs($user)->get('/questions/' . $question->id . '/answers/new');

        // Step 2: User submits invalid answer (empty content)
        $invalidAnswerResponse = $this->actingAs($user)->post('/answers', [
            'content' => ''
        ]);

        // Should redirect back with validation errors
        $invalidAnswerResponse->assertStatus(302);

        // Verify no invalid answer was created
        $this->assertDatabaseMissing('answers', [
            'content' => '',
            'question_id' => $question->id
        ]);

        // Step 3: User reaccesses the answer page and submits valid content
        $this->actingAs($user)->get('/questions/' . $question->id . '/answers/new');
        
        $validAnswerResponse = $this->actingAs($user)->post('/answers', [
            'content' => 'This is a valid answer after error recovery.'
        ]);

        $validAnswerResponse->assertStatus(302);
        $validAnswerResponse->assertRedirect('/questions/' . $question->id);

        // Verify valid answer was created
        $this->assertDatabaseHas('answers', [
            'content' => 'This is a valid answer after error recovery.',
            'question_id' => $question->id,
            'user_id' => $user->id
        ]);
    }

    public function test_session_timeout_and_recovery_workflow()
    {
        $user = $this->createUser();
        $question = $this->createQuestion(['user_id' => $user->id]);

        // Step 1: User accesses answer create page (sets session)
        $createResponse = $this->actingAs($user)->get('/questions/' . $question->id . '/answers/new');
        $createResponse->assertSessionHas('questionId', $question->id);

        // Step 2: Simulate session timeout by manually clearing session
        session()->flush();

        // Step 3: User tries to submit answer without proper session
        $answerResponse = $this->actingAs($user)->post('/answers', [
            'content' => 'Answer without proper session'
        ]);

        // This might fail or create unexpected behavior
        // The exact behavior depends on the implementation

        // Step 4: User reestablishes session and successfully creates answer
        $this->actingAs($user)->get('/questions/' . $question->id . '/answers/new');
        
        $recoveryResponse = $this->actingAs($user)->post('/answers', [
            'content' => 'Answer after session recovery'
        ]);

        $recoveryResponse->assertStatus(302);
        $recoveryResponse->assertRedirect('/questions/' . $question->id);

        $this->assertDatabaseHas('answers', [
            'content' => 'Answer after session recovery',
            'question_id' => $question->id,
            'user_id' => $user->id
        ]);
    }
}