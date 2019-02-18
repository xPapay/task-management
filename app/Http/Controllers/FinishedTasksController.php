<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Task;
use Carbon\Carbon;
use App\Events\TaskFinished;

class FinishedTasksController extends Controller
{
    public function store(Task $task)
    {
        $this->authorize('task.finish', $task);
        $task->finish();

        event(new TaskFinished($task));

        return $task;
    }

    public function destroy(Task $task)
    {
        $this->authorize('task.finish', $task);
        $task->unfinish();

        return $task;
    }
}
