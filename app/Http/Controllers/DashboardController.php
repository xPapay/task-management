<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;

class DashboardController extends Controller
{
    public function index()
    {
        $users = User::select(['id', 'name'])->get();
        return view('dashboard.index', compact('users'));
    }
}
