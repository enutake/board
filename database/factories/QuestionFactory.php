<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Question;
use Faker\Generator as Faker;

$factory->define(Question::class, function (Faker $faker) {
    return [
        'title'   => $faker->text,
        'content' => $faker->paragraph,
        'user_id' => 1,
    ];
});
