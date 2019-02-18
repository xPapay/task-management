<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['body', 'author_id'];
    protected $appends = ['created_ago'];

    public function author()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function getCreatedAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}
