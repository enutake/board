<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;
use Tests\TestHelpers;
use App\Models\User;
use App\Models\Question;
use App\Models\Answer;

class VueFrontendIntegrationTest extends FeatureTestCase
{
    use TestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function vue_app_div_is_present_in_layout()
    {
        $response = $this->get('/');
        
        $response->assertSee('<div id="app">');
        $response->assertSee('js/app.js');
        $response->assertSee('css/app.css');
    }

    /** @test */
    public function vue_components_can_access_csrf_token()
    {
        $response = $this->get('/');
        
        $response->assertSee('<meta name="csrf-token"');
        $response->assertSee(csrf_token());
    }

    /** @test */
    public function home_page_provides_data_for_vue_components()
    {
        $user = $this->createUser();
        $questions = $this->createQuestions(3, ['user_id' => $user->id]);

        $response = $this->get('/');

        $response->assertStatus(200);
        
        // Check that questions data is available for Vue components
        foreach ($questions as $question) {
            $response->assertSee($question->title);
            $response->assertSee($question->content);
            $response->assertSee($question->user->name);
        }
        
        // Check Bootstrap classes are present
        $response->assertSee('class="container"');
        $response->assertSee('class="row justify-content-center"');
        $response->assertSee('class="col-md-8"');
        $response->assertSee('class="card"');
        $response->assertSee('class="card-header"');
        $response->assertSee('class="card-body"');
        $response->assertSee('class="btn btn-primary"');
    }

    /** @test */
    public function question_detail_provides_data_for_vue_components()
    {
        $user = $this->createUser();
        $question = $this->createQuestion(['user_id' => $user->id]);
        $answers = $this->createAnswers(2, ['question_id' => $question->id, 'user_id' => $user->id]);

        $response = $this->get("/questions/{$question->id}");

        $response->assertStatus(200);
        
        // Check question data
        $response->assertSee($question->title);
        $response->assertSee($question->content);
        $response->assertSee($question->user->name);
        
        // Check answers data
        foreach ($answers as $answer) {
            $response->assertSee($answer->content);
            $response->assertSee($answer->user->name);
        }
        
        // Check Vue-specific elements
        $response->assertSee('この質問に回答する');
        $response->assertSee('class="answer-question-btn btn btn-primary"');
    }

    /** @test */
    public function authenticated_navigation_provides_vue_data()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        
        // Check authenticated user data is available
        $response->assertSee($user->name);
        $response->assertSee('ログアウト');
        $response->assertSee('質問を投稿する');
        
        // Check Bootstrap navigation classes
        $response->assertSee('class="navbar navbar-expand-md navbar-light bg-white shadow-sm"');
        $response->assertSee('class="nav-item dropdown"');
        $response->assertSee('class="dropdown-menu dropdown-menu-right"');
    }

    /** @test */
    public function guest_navigation_provides_vue_data()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        
        // Check guest user navigation
        $response->assertSee('ログイン');
        $response->assertSee('新規登録');
        $response->assertDontSee('ログアウト');
        $response->assertDontSee('質問を投稿する');
    }

    /** @test */
    public function question_creation_form_has_vue_integration_points()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/questions/new');

        $response->assertStatus(200);
        
        // Check form structure for Vue integration
        $response->assertSee('<form');
        $response->assertSee('class="form-control"');
        $response->assertSee('class="form-group"');
        $response->assertSee('class="btn btn-primary"');
        
        // Check CSRF token for Vue axios requests
        $response->assertSee('name="csrf-token"');
        $response->assertSee(csrf_token());
    }

    /** @test */
    public function answer_creation_form_has_vue_integration_points()
    {
        $user = $this->createUser();
        $question = $this->createQuestion(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/questions/{$question->id}/answers/new");

        $response->assertStatus(200);
        
        // Check form structure for Vue integration
        $response->assertSee('<form');
        $response->assertSee('class="form-control"');
        $response->assertSee('class="form-group"');
        $response->assertSee('class="btn btn-primary"');
        
        // Check question data is available for Vue components
        $response->assertSee($question->title);
        $response->assertSee($question->content);
    }

    /** @test */
    public function api_endpoints_return_json_for_vue_components()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'api')->get('/api/user');

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    /** @test */
    public function vue_components_can_submit_forms_with_csrf_protection()
    {
        $user = $this->createUser();

        // Simulate Vue component form submission with CSRF token
        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'X-CSRF-TOKEN' => csrf_token(),
            ])
            ->post('/questions', [
                'title' => 'Vue Test Question',
                'content' => 'This question was submitted via Vue component',
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('questions', [
            'title' => 'Vue Test Question',
            'content' => 'This question was submitted via Vue component',
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function vue_components_receive_validation_errors_in_json()
    {
        $user = $this->createUser();

        // Simulate Vue component form submission with validation errors
        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->post('/questions', [
                'title' => '', // Invalid: empty title
                'content' => '', // Invalid: empty content
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'content']);
    }

    /** @test */
    public function bootstrap_css_classes_are_available_for_vue_components()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        
        // Check that Bootstrap CSS is loaded
        $response->assertSee('css/app.css');
        
        // Check common Bootstrap classes are present in the HTML
        $bootstrapClasses = [
            'container',
            'row',
            'col-md-8',
            'card',
            'card-header',
            'card-body',
            'btn',
            'btn-primary',
            'navbar',
            'nav-item',
            'nav-link',
            'justify-content-center',
            'text-center',
            'text-right',
            'mb-4',
            'mb-3',
            'small'
        ];

        foreach ($bootstrapClasses as $class) {
            $response->assertSee("class=\"{$class}\"", false);
        }
    }

    /** @test */
    public function javascript_assets_are_properly_loaded_for_vue()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        
        // Check that Vue and related JS assets are loaded
        $response->assertSee('js/app.js');
        $response->assertSee('defer');
        
        // Check that the Vue app container is present
        $response->assertSee('<div id="app">');
    }

    /** @test */
    public function error_pages_maintain_vue_app_structure()
    {
        $response = $this->get('/nonexistent-route');

        $response->assertStatus(404);
        
        // Even on error pages, Vue app structure should be maintained
        // This ensures Vue components can still function for navigation, etc.
        $response->assertSee('<div id="app">');
        $response->assertSee('js/app.js');
        $response->assertSee('css/app.css');
    }

    /** @test */
    public function vue_components_can_access_localization_data()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        
        // Check that Japanese language strings are present for Vue components
        $response->assertSee('ログイン');
        $response->assertSee('新規登録');
        $response->assertSee('この質問の回答を見る');
        
        // Check locale is properly set
        $response->assertSee('lang="ja"', false);
    }
}