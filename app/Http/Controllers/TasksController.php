<?php

namespace App\Http\Controllers;

use App\Task;
use App\User;
use App\Attachment;
use App\Filter\TaskFilter;
use App\Events\TaskDeleted;
use Illuminate\Http\Request;
use App\Events\TaskEndChanged;
use App\Events\TaskStartChanged;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TasksController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only(['index']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, TaskFilter $queryFilter)
    {
        $tasksQuery = Auth::user()->tasks()->filter($queryFilter);

        $tasks = $tasksQuery->get();
        
        if ($request->wantsJson())
        {
            return $tasks;
        }
        
        return view('tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'start_date' => 'required|date',
            'due_date' => 'required|date|after:start_date',
            'assignees' => 'required|exists:users,id'
        ]);

        $task = Auth::user()->createTask($request->only([
            'title', 
            'description', 
            'start_date', 
            'due_date'
        ]))->for($request->assignees);

        if ($request->has('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $task->attachments()->save(Attachment::fromFile($file));
            }
        }
        
        return $task->load('assignees', 'attachments');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        $this->authorize('task.show', $task);
        $task->load('assignees', 'comments.author', 'attachments', 'comments.attachments');
        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task)
    {
        $this->authorize('update-task', $task);
        $request->validate([
            'title' => 'sometimes|required',
            'start_date' => ['sometimes', 'required', 'date', function($attribute, $value, $fail) use ($request, $task) {
                if (! $request->has('due_date')) {
                    if ( (new \Carbon\Carbon($request->start_date))->greaterThan($task->due_date)) {
                        $fail($attribute . 'can not be greater than due_date');
                    }
                }
            }],
            'due_date' => ['sometimes', 'required', 'date', 'after:start_date', function($attribute, $value, $fail) use ($request, $task) {
                if (! $request->has('start_date')) {
                    if ( (new \Carbon\Carbon($request->due_date))->lessThan($task->start_date)) {
                        $fail($attribute . 'can not be less than start_date');
                    }
                }
            }],
            // when I submit epmpty assignees array - I'm assigning task to nobody - forbidden
            // when I submit form without assignees variable present - I don't want to change assignees
            'assignees' => 'sometimes|required|exists:users,id'
        ]);
        
        $task->attachments()
            ->whereNotIn('id', $request->get('old_attachments', []))
            ->get()
            ->each->delete();

        $task->update($request->all());

        if ($request->has('start_date')) {
            event(new TaskStartChanged($task));
        }

        if ($request->has('due_date')) {
            event(new TaskEndChanged($task));
        }
        
        if ($request->has('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $task->attachments()->save(Attachment::fromFile($file));
            }
        }

        if ($request->has('assignees')) {
            $task->for($request->assignees);
        }
        
        return $task;
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        $this->authorize('task.delete', $task);
        $task->attachments->each->delete();
        $task->delete();
        session()->flash('flash', 'Task was delted');
        if (request()->wantsJson()) {
            return 'Deleted';
        }

        return redirect('/');
    }
}
