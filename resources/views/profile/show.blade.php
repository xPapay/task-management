@extends('layouts.app')
@section('content')
<h2>Profile</h2>
<user :user="{{ $user }}"></user>
<form 
    method="POST" 
    action="{{ route('profile.updatePassword') }}"
    class="m-top-l max-w-s margin-center"
>
    @csrf
    <div class="card">
        <div class="card__content">
            <h3 class="card__title">Change password</h3>
            <input class="input m-bottom-xs full-width" type="password" name="password" placeholder="new password" required>
            <input class="input m-bottom-s full-width" type="password" name="password_confirmation" placeholder="repeat new password" required>
            @if($errors->any())
            <ul>
                @foreach($errors->all() as $error)
                <li class="red italic">{{ $error }}</li>
                @endforeach
    
            </ul>
            @endif
        </div>
        <div class="card__secondary-content">
            <i class="fas fa-lock fa-3x white"></i>
        </div>
        <button type="submit" class="button card__button bg-green white">
            <i class="fas fa-chevron-right fa-2x"></i>
        </button>
    </div>
</form>
@endsection
