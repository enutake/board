<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Question;
use Faker\Generator as Faker;

$factory->define(Question::class, function (Faker $faker) {
    return [
        'title'   => $faker->sentence,
        'content' => $faker->text(500),
        'user_id' => function () {
            return factory(\App\Models\User::class)->create()->id;
        },
    ];
});

$factory->state(Question::class, 'short', [
    'content' => function (Faker $faker) {
        return $faker->sentence;
    },
]);

$factory->state(Question::class, 'long', [
    'content' => function (Faker $faker) {
        return $faker->text(1000);
    },
]);
