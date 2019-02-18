<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Attachment;
use Illuminate\Support\Facades\Storage;

class AttachmentsController extends Controller
{
    public function show(Attachment $attachment)
    {
        return Storage::disk('public')->download($attachment->path);
    }
}
