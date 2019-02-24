@extends('layouts.app')

@section('content')
    <form 
        method="POST" 
        action="{{ route('login') }}" 
        class="card max-w-s absolute-center login-form"
    >
        @csrf
        <div class="card__content w-100">
            <input class="input login-form__input m-bottom-xs" type="email" name="email" value="{{ old('email') }}" autofocus placeholder="e-mail" required>
            <input class="input login-form__input m-bottom-s" type="password" name="password" placeholder="password" required>
            @if($errors->any())
            <ul>
                @foreach($errors->all() as $error)
                <li class="red italic">{{ $error }}</li>
                @endforeach
    
            </ul>
            @endif
            @if (env('APP_ENV') === 'demo')
            <div class="m-bottom-s">
                Login: {{ config('auth.demo.login') }} | Password: {{ config('auth.demo.password') }}
            </div>
            @endif
            @if (Route::has('password.request'))
                <a class="block" href="{{ route('password.request') }}">
                    {{ __('Forgot Your Password?') }}
                </a>
            @endif
        </div>
        <button type="submit" class="button card__button bg-green white">Log in</button>
    </form>
@endsection
