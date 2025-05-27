<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Question;
use App\Models\Answer;
use Tests\TestCase;
use Tests\TestHelpers;
use Tests\TestConfig;

class TestInfrastructureTest extends TestCase
{
    use TestHelpers;

    /**
     * @test
     */
    public function test_database_connection_works(): void
    {
        $this->assertEquals('mysql_testing', config('database.default'));
        $this->assertEquals('board_testing', config('database.connections.mysql_testing.database'));
    }

    /**
     * @test
     */
    public function test_factories_work_with_proper_relationships(): void
    {
        $user = $this->createUser();
        $question = $this->createQuestion(['user_id' => $user->id]);
        $answer = $this->createAnswer([
            'user_id' => $user->id,
            'question_id' => $question->id
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(Question::class, $question);
        $this->assertInstanceOf(Answer::class, $answer);
        
        $this->assertEquals($user->id, $question->user_id);
        $this->assertEquals($user->id, $answer->user_id);
        $this->assertEquals($question->id, $answer->question_id);
    }

    /**
     * @test
     */
    public function test_factory_states_work(): void
    {
        $unverifiedUser = factory(User::class)->states('unverified')->create();
        $adminUser = factory(User::class)->states('admin')->create();
        $shortQuestion = factory(Question::class)->states('short')->create();
        $detailedAnswer = factory(Answer::class)->states('detailed')->create();

        $this->assertNull($unverifiedUser->email_verified_at);
        $this->assertEquals('Admin User', $adminUser->name);
        $this->assertLessThan(100, strlen($shortQuestion->content));
        $this->assertGreaterThan(500, strlen($detailedAnswer->content));
    }

    /**
     * @test
     */
    public function test_helper_methods_work(): void
    {
        $users = $this->createUsers(3);
        $questions = $this->createQuestions(2);
        $questionWithAnswers = $this->createQuestionWithAnswers();

        $this->assertCount(3, $users);
        $this->assertCount(2, $questions);
        $this->assertCount(2, $questionWithAnswers->answers);
    }

    /**
     * @test
     */
    public function test_database_helper_assertions_work(): void
    {
        $user = $this->createUser(['name' => 'Test User']);
        
        $this->assertDatabaseHasModel($user, ['name' => 'Test User']);
        
        $user->delete();
        
        $this->assertDatabaseMissingModel($user);
    }

    /**
     * @test
     */
    public function test_config_class_provides_constants(): void
    {
        $this->assertEquals('mysql_testing', TestConfig::TEST_DB_CONNECTION);
        $this->assertEquals('board_testing', TestConfig::TEST_DB_NAME);
        $this->assertEquals('password123', TestConfig::TEST_PASSWORD);
        
        $credentials = TestConfig::getTestUserCredentials();
        $this->assertArrayHasKey('email', $credentials);
        $this->assertArrayHasKey('password', $credentials);
    }

    /**
     * @test
     */
    public function test_authentication_helpers_work(): void
    {
        $user = $this->createUser();
        $this->actingAsUser($user);
        $this->assertAuthenticatedAs($user);
        
        $this->actingAsAdmin();
        $admin = auth()->user();
        $this->assertInstanceOf(User::class, $admin);
        $this->assertEquals('Admin User', $admin->name);
        $this->assertAuthenticatedAs($admin);
    }
}