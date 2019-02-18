<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('update-task', function ($user, $task) {
            return $task->creator_id == $user->id;
        });

        Gate::define('task.show', function($user, $task) {
            if ($user->id === $task->creator->id) {
                return true;
            }

            return $task->assignees->contains($user);
        });

        Gate::define('task.commenting', function($user, $task) {
            if ($user->id === $task->creator->id) {
                return true;
            }

            return $task->assignees->contains($user);
        });

        Gate::define('task.finish', function($user, $task) {
            if ($user->id === $task->creator->id) {
                return true;
            }
            
            return $task->assignees->contains($user);
        });

        Gate::define('task.delete', function($user, $task) {
            return $task->creator_id == $user->id;
        });
    }
}
