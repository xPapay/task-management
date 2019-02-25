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
    private $database;

    public function __construct()
    {
        $this->database = $this->getDbPath();
        $this->createDbIfNotExists();
        $this->setDemoConnection();
    }

    private function createDbIfNotExists()
    {
        if ($this->databaseExists()) {
            return;
        }

        $this->createDemoDbValidFor($lifetime = 5 * 24 * 60);
        $this->migrate();
        $this->seed();
    }

    private function getDbPath()
    {
        return Cookie::get('db_connection') ? : database_path("demo/" . Str::uuid() . ".sqlite");
    }

    private function databaseExists()
    {
        return file_exists($this->database);
    }


    private function createDemoDbValidFor($lifetime)
    {
        touch($this->database);
        Cookie::queue(Cookie::make('db_connection', $this->database, $lifetime));
    }

    private function migrate()
    {
        $this->setDemoConnection();

        Artisan::queue('migrate', ['--force' => true]);
    }

    private function seed()
    {
        $this->setDemoConnection();

        Artisan::queue('db:seed', ['--class' => 'DemoDatabaseSeeder']);
    }

    private function setDemoConnection()
    {
        DB::purge();
        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', $this->database);
    }
}
