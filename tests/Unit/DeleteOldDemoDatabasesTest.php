<?php

namespace Tests\Unit;

use Mockery;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Demo\DeleteOldDemoDatabases;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Testing\FileFactory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Str;
use App\Attachment;
use Illuminate\Http\UploadedFile;
use App\Task;
use App\User;

class DeleteOldDemoDatabasesTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        Storage::fake('databases');
        Storage::fake('public');
    }

    /** @test */
    public function it_deletes_all_demo_databases_older_than_given_number_of_days()
    {
        $newDatabase = $this->createDatabase('new_database.sqlite');
        $this->migrate($this->connectDatabase($newDatabase));

        $oldDatabase = $this->createDatabase('old_database.sqlite');
        $this->migrate($this->connectDatabase($oldDatabase));
        $this->setFileAge($oldDatabase, 6);
        
        Storage::disk('databases')->assertExists('demo/new_database.sqlite');
        Storage::disk('databases')->assertExists('demo/old_database.sqlite');

        (new DeleteOldDemoDatabases())();
        
        Storage::disk('databases')->assertMissing('demo/old_database.sqlite');
        Storage::disk('databases')->assertExists('demo/new_database.sqlite');        
    }

    /** @test */
    public function it_deletes_all_files_associated_with_database()
    {
        $database = $this->createDatabase('old_database.sqlite');
        $connection = $this->connectDatabase($database);
        $this->migrate($connection);
        $attachment = $this->createAttachment($connection);
        $profilePicture = $this->createProfilePicture($connection);
        $this->setFileAge($database, $days = 6);
        Storage::disk('public')->assertExists($attachment->path);
        Storage::disk('public')->assertExists($profilePicture->picture);

        (new DeleteOldDemoDatabases())();

        Storage::disk('public')->assertMissing($attachment->path);
        Storage::disk('public')->assertMissing($profilePicture->picture);
    }

    protected function createDatabase($name = null)
    {
        $name = $name ?: Str::random() . ".sqlite";
        $path = File::create($name)->storeAs('demo', $name, 'databases');

        $database = Storage::disk('databases')->getDriver()->getAdapter()->getPathPrefix() . $path;

        return $database;
    }

    protected function setFileAge($file, $days = null)
    {
        if ($days) {
            touch($file, strtotime("-{$days} days"));
        }
    }

    protected function createAttachment($connection)
    {
        return factory(Task::class)->create()->attachments()->save(
            Attachment::fromFile(UploadedFile::fake()->create('attachment.pdf'))
        );
    }

    protected function createProfilePicture($connection)
    {
        $path = File::fake()->create('profile.jpg')->store('profiles', 'public');
        return factory(User::class)->create(['picture' => $path]);
    }

    protected function migrate($connection)
    {
        Artisan::queue('migrate', ['--force' => true, '--database' => $connection]);
    }

    protected function connectDatabase($database)
    {
        $name = explode('.', substr($database, strrpos($database, '/') + 1))[0];
        DB::purge();
        Config::set('database.default', $name);
        Config::set("database.connections.{$name}", [
            'driver' => 'sqlite',
            'database' => $database,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        return $name;
    }
}
