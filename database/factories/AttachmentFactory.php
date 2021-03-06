<?php

use Faker\Generator as Faker;
use App\Task;
use App\Comment;

$factory->define(App\Attachment::class, function (Faker $faker) {
    return [
        'path' => 'attachments/demo/attachment.jpg',
        'name' => 'filename.jpg',
        'attachable_id' => factory(Task::class)->create()->id,
        'attachable_type' => 'App\Task'
    ];
});

$factory->state(App\Attachment::class, 'comment', function (Faker $faker) {
    return [
        'attachable_id' => ($comment = factory(Comment::class)->create())->id,
        'attachable_type' => get_class($comment)
    ];
});
