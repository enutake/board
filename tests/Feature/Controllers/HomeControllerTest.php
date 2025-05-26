<?php

namespace Tests\Feature\Controllers;

use Tests\FeatureTestCase;

class HomeControllerTest extends FeatureTestCase
{
    public function testIndexDisplaysQuestionsSuccessfully()
    {
        $questions = $this->createQuestions(3);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('home');
        $response->assertViewHas('data');
        
        $viewData = $response->viewData('data');
        $this->assertObjectHasAttribute('questions', $viewData);
    }

    public function testIndexDisplaysWithoutQuestionsSuccessfully()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('home');
        $response->assertViewHas('data');
    }

    public function testIndexUsesQuestionService()
    {
        $this->createQuestions(5);

        $response = $this->get('/');

        $response->assertStatus(200);
        $viewData = $response->viewData('data');
        $this->assertObjectHasAttribute('questions', $viewData);
    }
}