@extends('layouts.app')

@section('content')
<form 
    method="POST" 
    action="{{ route('password.update') }}" 
    class="card max-w-s absolute-center login-form"
>
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <div class="card__content w-100">
        <h3>Reset password</h3>
        <input class="input login-form__input m-bottom-xs" type="email" name="email" value="{{ $email ?? old('email') }}" autofocus placeholder="e-mail" required>
        <input type="password" class="input login-form__input m-bottom-xs" name="password" placeholder="Password" required>
        <input type="password" class="input login-form__input m-bottom-xs" name="password_confirmation" placeholder="Confirm Password" required>
        @if($errors->any())
        <ul>
            @foreach($errors->all() as $error)
            <li class="red italic">{{ $error }}</li>
            @endforeach

        </ul>
        @endif
    </div>
    <button type="submit" class="button card__button card__button--mw bg-green white">Reset Password</button>
</form>
@endsection
