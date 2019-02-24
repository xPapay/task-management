<?php

namespace App\Demo;

use App\User;
use App\Attachment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;


class DeleteOldDemoDatabases
{
    public function __invoke()
    {
        $databases = $this->getDatabases();
        $databases->map(function($database) {
            $this->clearDatabase($database);
        });
    }

    protected function getDatabases()
    {
        $databases = Storage::disk('databases')->files('demo');
        return collect($databases)->filter(function($path) {
            return $this->isFileOlderThan($path, 5);
        });
    }

    protected function isFileOlderThan($path, $days = 5)
    {
        $fileTimestamp = Storage::disk('databases')->lastModified($path);
        return $fileTimestamp < strtotime("-{$days} days");
    }

    protected function clearDatabase($database)
    {
        $this->removeFiles($database);
        $this->removeDatabase($database);
    }

    protected function removeFiles($database)
    {
        $this->connectDatabase($database);
        $this->removeAttachments($database);
        $this->removeProfilePictures($database);
    }

    protected function connectDatabase($database)
    {
        $pathPrefix = Storage::disk('databases')->getDriver()->getAdapter()->getPathPrefix();
        $database = "{$pathPrefix}{$database}";
        DB::purge();
        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', $database);
    }

    protected function removeDatabase($database)
    {
        Storage::disk('databases')->delete($database);
    }

    protected function removeAttachments()
    {
        Attachment::all()->each->delete();
    }

    protected function removeProfilePictures()
    {
        User::pluck('picture')->each(function($picture) {
            Storage::disk('public')->delete($picture);
        });
    }
}