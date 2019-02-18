hello: {{ $user->name }}
token: {{ route('password.reset', ['token' => $token])}}
