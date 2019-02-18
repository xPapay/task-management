<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class Attachment extends Model
{
    protected $fillable = ['path', 'name'];
    protected $appends = ['public_path'];

    public static function fromFile(UploadedFile $file)
    {
        return new self([
            'path' => $file->store('attachments', 'public'),
            'name' => $file->getClientOriginalName()
        ]);
    }

    public function getPublicPathAttribute()
    {
        return asset("/storage/{$this->path}");
    }
}
