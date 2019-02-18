<?php

use App\Task;

function taskToTimeline(Task $task)
{
    $dataset = [
        [
            'start' => $task->start_date,
            'end' => $task->due_date,
            'content' => $task->title
        ]
    ];

    return json_encode($dataset);
}