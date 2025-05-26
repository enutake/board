<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\TagMaster;
use Faker\Generator as Faker;

$factory->define(TagMaster::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
    ];
});