<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Task;

class AssigneesController extends Controller
{
    public function index(Task $task)
    {
        return $task->assignees;
    }
}
