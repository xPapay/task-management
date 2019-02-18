<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\User;
use Auth;
use App\Mail\UserProfileCreated;

class ProfilesController extends Controller
{
    public function show()
    {
        return view('profile.show', ['user' => Auth::user()]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|min:6'
        ]);

        $user = Auth::user();

        $user->password = Hash::make($request->password);

        $user->setRememberToken(Str::random(60));

        $user->save();

        Auth::guard()->login($user);

        return redirect()->back();
    }

    public function create()
    {
        return view('profile.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make(Str::random(8))
        ]);

        $token = Password::broker()->createToken($user);

        Mail::to($user->email)->send(new UserProfileCreated($user, $token));

        return redirect()->back();
    }

    public function update(Request $request)
    {
        Auth::user()->name = $request->name;
        Auth::user()->save();
    }

    /**
     * Update avatar
     *
     * @return \Illuminate\Http\Response
     */
    public function updateAvatar(Request $request)
    {
        $user = Auth::user();
        $avatar = $request->file('picture');
        $path = $avatar->store('profiles', 'public');
        $user->picture = $path;
        $user->save();
        return $user;
    }
}
