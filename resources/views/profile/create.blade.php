@extends('layouts.app')
@section('content')
<h2>Accounts</h2>
<form 
    method="POST" 
    action="{{ route('profile.create') }}"
    class="m-top-l max-w-s margin-center"
>
    @csrf
    <div class="card">
        <div class="card__content">
            <h3 class="card__title">Create Account</h3>
            <input class="input m-bottom-xs full-width" type="text" name="name" placeholder="Full Name" required>
            <input class="input m-bottom-s full-width" type="email" name="email" placeholder="Email" required>
            <div class="valign">
                <input class="input" type="checkbox" name="isAdmin"><span class="m-left-xs">Admin privileges</span>
            </div>
            @if($errors->any())
            <ul>
                @foreach($errors->all() as $error)
                <li class="red italic">{{ $error }}</li>
                @endforeach
    
            </ul>
            @endif
        </div>
        <div class="card__secondary-content">
            <i class="fas fa-user fa-3x white"></i>
        </div>
        <button type="submit" class="button card__button bg-green white">
            <i class="fas fa-chevron-right fa-2x"></i>
        </button>
    </div>
</form>
<table>
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Admin</th>
    </tr>
    <tr>
        <td>John Doe</td>
        <td>john@doe.com</td>
        <td>No</td>
    </tr>
    <tr>
        <td>Jane Doe</td>
        <td>jane@doe.com</td>
        <td>Yes</td>
    </tr>
</table>
@endsection
