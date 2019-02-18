<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(App\Task::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence,
        'description' => $faker->paragraph,
        'start_date' => Carbon::now()->subWeek()->toDateString(),
        'due_date' => Carbon::now()->addMonth(1)->toDateString(),
        'creator_id' => function() {
            return factory('App\User')->create()->id;
        }
    ];
});
