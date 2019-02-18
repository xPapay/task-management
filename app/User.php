<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $appends = ['picture_path'];

    public function createTask($data)
    {
        $task = $this->supervisedTasks()->create($data);
        return $task;
    }

    public function supervisedTasks()
    {
        return $this->hasMany(Task::class, 'creator_id');
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class);
    }

    public function getPicturePathAttribute()
    {
        return asset($this->picture ? "storage/{$this->picture}": "https://s3.amazonaws.com/uifaces/faces/twitter/kerem/128.jpg");
    }
}
