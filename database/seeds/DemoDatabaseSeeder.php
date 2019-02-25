<?php

use App\Task;
use App\User;
use App\Comment;
use App\Attachment;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Collection;

class DemoDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $demoUser = factory(User::class)->create([
            'name' => 'John Doe',
            'picture' => null,
            'email' => Config::get('auth.demo.login'),
            'password' => bcrypt(Config::get('auth.demo.password'))
        ]);

        $users = factory(User::class, 10)->create();

        $task = factory(Task::class)->create([
            'start_date' => (new Carbon('-1 day'))->toDateString(),
            'due_date' => (new Carbon('+12 days'))->toDateString()
        ])->for(Collection::wrap([$demoUser, $users[0]]));

        factory(Attachment::class)->create([
            'name' => 'example_filename',
            'attachable_id' => $task->id
        ]);

        $comment = factory(Comment::class)->create([
            'task_id' => $task->id,
            'author_id' => $users[0]
        ]);

        factory(Attachment::class)->states('comment')->create([
            'name' => 'example_filename',
            'attachable_id' => $comment->id
        ]);

        factory(Task::class)->create([
            'start_date' => (new Carbon('+1 day'))->toDateString(),
            'due_date' => (new Carbon('+5 days'))->toDateString()
        ])->for(Collection::wrap([$demoUser, $users[1]]));

        factory(Task::class)->create([
            'start_date' => (new Carbon('+6 day'))->toDateString(),
            'due_date' => (new Carbon('+1 month'))->toDateString()
        ])->for(Collection::wrap([$demoUser, $users[2]]));

        factory(Task::class)->create([
            'start_date' => (new Carbon('+1 day'))->toDateString(),
            'due_date' => (new Carbon('+2 months'))->toDateString(),
            'creator_id' => $demoUser->id
        ])->for($users->only([2, 3, 4, 5]));
    }
}
