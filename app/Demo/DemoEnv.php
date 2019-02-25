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
        $database = $this->prepareDemoDbIfNotExists();
        $this->setDemoConnection($database);
    }

    protected function prepareDemoDbIfNotExists()
    {
        if (! $this->databaseExists($database = $this->getDbPath())) {
            $this->createDemoDb($database, $lifetime = 5 * 24 * 60);
            $this->migrate($database);
            $this->seed($database);
        }
        return $database;
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
        Cookie::queue(Cookie::make('db_connection', $path, $lifetime));
    }

    protected function migrate($database)
    {
        $this->setDemoConnection($database);

        Artisan::queue('migrate', ['--force' => true]);
    }

    protected function seed($database)
    {
        $this->setDemoConnection($database);

        // TODO: replace with seeder
        Artisan::queue('db:seed', ['--class' => 'DemoDatabaseSeeder']);
    }

    protected function setDemoConnection($connection)
    {
        DB::purge();
        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', $connection);
    }
}
