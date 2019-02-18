<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $notifications = Auth::user()->notifications()->latest()->take(20)->get();

        if ($request->wantsJson()) {
            return $notifications;
        }
        
        return view('notifications.index', compact('notifications'));
    }

    public function update(DatabaseNotification $notification)
    {
        $notification->markAsRead();
        return $notification;
    }

    public function readAll()
    {
        Auth::user()->notifications->markAsRead();
    }
}
