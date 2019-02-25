<?php

namespace App;

use App\Events\TaskDeleted;
use App\Filter\UrlQueryFilter;
use App\Events\UsersAssignedOnTask;
use Illuminate\Support\Facades\Auth;
use App\Events\UsersDeassignedFromTask;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\UserAssignedOnTask;
use App\Notifications\UserDeassignedFromTask;
use Illuminate\Database\Eloquent\Collection;

class Task extends Model
{
    protected $fillable = ['title', 'description', 'start_date', 'due_date', 'finished_at', 'finisher_id'];
    protected $dates = ['start_date', 'due_date', 'finished_at'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function assign($assignees)
    {
        $this->assignees()->attach($assignees);
        event(new UsersAssignedOnTask($this, $assignees));
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class);
    }

    public function finish()
    {
        $this->update([
            'finished_at' => \Carbon\Carbon::now(),
            'finisher_id' => Auth::check() ? Auth::user()->id : null
        ]);
    }

    public function finisher()
    {
        return $this->belongsTo(User::class);
    }

    public function unfinish()
    {
        $this->update(['finished_at' => null]);
    }

    public function scopeUnfinished($query)
    {
        return $query->whereNull('finished_at');
    }

    public function scopeFinished($query)
    {
        return $query->whereNotNull('finished_at');
    }

    public function scopeFilter($query, UrlQueryFilter $filter)
    {
        return $filter->applyOn($query);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function setDueDateAttribute($value)
    {
        $this->attributes['due_date'] = \Carbon\Carbon::parse($value)->setTime(23, 59, 59);
    }

    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at->format('M jS, Y');
    }

    public function for($assignees)
    {
        $changes = $this->assignees()->sync($assignees);

        event(new UsersAssignedOnTask($this, User::whereIn('id', $changes['attached'])->get()));
        event(new UsersDeassignedFromTask($this, User::whereIn('id', $changes['detached'])->get()));

        return $this;
    }

    public function delete()
    {
        event(new TaskDeleted($this));
        parent::delete();
    }
}
