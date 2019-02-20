@extends('layouts.app')

@section('content')
<form 
    method="POST" 
    action="{{ route('password.email') }}" 
    class="card max-w-s absolute-center login-form"
>
    @csrf
    <div class="card__content w-100">
        <h3>Reset password</h3>
        <input class="input login-form__input m-bottom-xs" type="email" name="email" value="{{ old('email') }}" autofocus placeholder="e-mail" required>
        @if($errors->any())
        <ul>
            @foreach($errors->all() as $error)
            <li class="red italic">{{ $error }}</li>
            @endforeach
        </ul>
        @endif
    </div>
    <button type="submit" class="button card__button card__button--mw bg-green white">Send Password Reset Link</button>
</form>
@endsection
