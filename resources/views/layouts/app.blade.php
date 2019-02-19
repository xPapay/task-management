<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport"content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script>
        window.App = {!! json_encode([
            'user' => Auth::user()
        ]) !!}

        window.App.formatDate = function(date) {
            if (! (date instanceof Date) ) {
                date = new Date(date);
            }
            return `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()}`;
        }

        window.App.flash = function(message, forTime) {
            Event.$emit('flash', message, forTime);
        }

    </script>
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

</head>
<body>
    <div id="app" class="page">
        <div class="navigation-position">
            <navigation :attention="{{ json_encode(Auth::check() ? Auth::user()->unreadNotifications->count() > 0 : false) }}" inline-template v-cloak>
                <nav class="navigation">
                    <div 
                        class="hamburger"
                        :class="{[`hamburger--open`]: isOpen, ['hamburger--attention']: attention}"
                        @click="isOpen=!isOpen"
                    >
                        <div class="hamburger__line"></div>
                    </div>
                    <ul class="navigation-menu" :class="{[`navigation-menu--open`]: isOpen}">
                        <li class="navigation-menu__item">
                            <a href="/">Dashboard</a>
                        </li>
                        <li class="navigation-menu__item">
                            <a href="{{ route('notifications') }}">Notifications</a>
                        </li>
                        <li class="navigation-menu__item">
                            <a href="{{ route('profile') }}">Profile</a>
                        </li>
                        <li class="navigation-menu__item">
                            <a href="{{ route('logout') }}"
                                @click.prevent="$refs.logoutForm.submit();">
                                 {{ __('Logout') }}
                             </a>
                             <form ref="logoutForm" action="{{ route('logout') }}" method="POST" style="display: none;">
                                 @csrf
                             </form>
                        </li>
                    </ul>
                  </nav>
            </navigation>
        </div>
        @yield('content')
        <flash message="{{ session('flash') }}"></flash>
    </div>
</body>
</html>
