<?php

namespace App\Http\Controllers;

use App\Task;
use App\Attachment;
use App\Events\CommentAdded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentsController extends Controller
{
    public function index(Task $task)
    {
        $this->authorize('task.commenting', $task);
        return $task->comments;
    }

    public function store(Request $request, Task $task)
    {
        $this->authorize('task.commenting', $task);
        $request->validate(['body' => 'required']);

        $comment = $task->comments()->create([
            'body' => $request->body,
            'author_id' => Auth::user()->id
        ]);

        event(new CommentAdded($comment));

        if ($request->has('attachments')) {
            foreach($request->file('attachments') as $file) {
                $comment->attachments()->save(Attachment::fromFile($file));
            }
        }

        if ($request->wantsJson()) {
            return $comment->load('author', 'attachments');
        }

        return redirect()->back();
    }
}
