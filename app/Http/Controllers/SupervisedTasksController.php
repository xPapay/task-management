<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Filter\TaskFilter;

class SupervisedTasksController extends Controller
{
    public function index(TaskFilter $filter)
    {
        return Auth::user()->supervisedTasks()->filter($filter)->get();
    }
}
