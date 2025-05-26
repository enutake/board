<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Answer;
use Faker\Generator as Faker;

$factory->define(Answer::class, function (Faker $faker) {
    return [
        'content'     => $faker->text(300),
        'user_id'     => function () {
            return factory(\App\Models\User::class)->create()->id;
        },
        'question_id' => function () {
            return factory(\App\Models\Question::class)->create()->id;
        },
    ];
});

$factory->state(Answer::class, 'short', [
    'content' => function (Faker $faker) {
        return $faker->sentence;
    },
]);

$factory->state(Answer::class, 'detailed', [
    'content' => function (Faker $faker) {
        return $faker->text(800);
    },
]);
