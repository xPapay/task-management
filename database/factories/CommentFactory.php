<?php

use Faker\Generator as Faker;
use App\User;
use App\Task;

$factory->define(App\Comment::class, function (Faker $faker) {
    return [
        'body' => $faker->paragraph,
        'author_id' => factory(User::class)->create()->id,
        'task_id' => factory(Task::class)->create()->id
    ];
});
