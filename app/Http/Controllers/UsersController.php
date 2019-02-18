<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $name = $request->get('nameLike');
        return User::where('name', 'like', "%{$name}%")->get();
    }
}
