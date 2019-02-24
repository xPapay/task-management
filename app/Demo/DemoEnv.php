<?php

namespace App\Demo;

use App\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Artisan;

class DemoEnv
{
    public function __construct()
    {
        $this->prepareDemoDbIfNotExists();
        $this->setDemoConnection(Cookie::get('db_connection'));
    }

    protected function prepareDemoDbIfNotExists()
    {
        if ($this->databaseExists($database = $this->getDbPath())) {
            return;
        }
        
        $this->createDemoDb($database, $lifetime = 5 * 24 * 60);
        $this->migrate($database);
        $this->seed($database);
    }

    protected function databaseExists($database)
    {
        return file_exists($database);
    }

    protected function getDbPath()
    {
        return Cookie::get('db_connection') ? : database_path("demo/" . Str::uuid() . ".sqlite");
    }

    protected function createDemoDb($path, $lifetime)
    {
        touch($path);
        Cookie::queue(Cookie::make('db_connection', $lifetime));
    }

    protected function migrate($database)
    {
        $this->setDemoConnection($database);

        Artisan::queue('migrate', ['--force' => true]);
    }

    protected function seed($database)
    {
        $this->setDemoConnection($database);

        factory(User::class)->create([
            'name' => 'John Doe',
            'email' => Config::get('auth.demo.login'), 
            'password' => bcrypt(Config::get('auth.demo.login'))
        ]);
    }

    protected function setDemoConnection($connection)
    {
        DB::purge();
        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', $connection);
    }
}
