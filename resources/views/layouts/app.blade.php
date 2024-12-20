<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- favicon -->
    <link rel="shortcut icon" href="{{ asset('/favicon.ico') }}">
    <!-- iOS用 -->
    <meta name="apple-mobile-web-app-capable" content="yes">

    <!-- Android用 -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta http-equiv="content-language" content="ja">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', '工程管理システム') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <link href="/icon/css/all.css" rel="stylesheet">
    <!--  -->
    <!-- Styles -->
    @if(config('app.env') === 'production')
        <link href="{{ secure_asset('/css/reset.css') }}" rel="stylesheet">
        <link href="{{ secure_asset('/css/loading.css') }}" rel="stylesheet">
        <link href="{{ secure_asset('/css/filter.css') }}" rel="stylesheet">
    @else
        <link href="{{ asset('/css/reset.css') }}" rel="stylesheet">
        <link href="{{ asset('/css/loading.css') }}" rel="stylesheet">
        <link href="{{ asset('/css/filter.css') }}" rel="stylesheet">
    @endif
    <link href="{{ asset('/css/loading.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet"  href="{{ asset('css/bootstrap.min.css') }}">
    <link href="{{ asset('css/nav.css') }}" rel="stylesheet">
    <!-- jquery -->
    <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
    {{-- フィルターアイコン --}}
    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    </head>

    <!-- 拡大できないようにする -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    @yield('css')
    <style>
        @media screen and (max-width: 1920px) {
            body {
                touch-action: pan-x pan-y;
            }
        }
    </style>
</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light nav_back shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', '工程管理システム') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div>
                    @yield('nav_title')
                </div>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                        <!-- <li class="nav-item">
                        </li> -->
                        @if (Route::has('register'))
                        <!-- <li class="nav-item">
                        </li> -->
                        @endif
                        @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }} <span class="caret"></span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        {{-- <div id="info_banner" class="news-banner">
            <div class="news-banner__content">
            <p>「ブラウザバックボタン」や「スワイプバック」などの操作はお控えいただきますようお願いします。</p>
          </div>
        </div> --}}


        <input type="hidden" name="local_ip">
        <main class="py-4">
            @yield('content')
        </main>
    </div>
    
    {{-- @if(config('app.env') === 'production')
        <script src="{{secure_asset('js/local_ip.js')}}" ></script>
    @else
        <script src="{{ asset('js/local_ip.js') }}"></script>

    @endif --}}
    <script>
        var userAgent = navigator.userAgent;
        var platform = navigator.platform;
        var isIpad = !!navigator.maxTouchPoints && navigator.maxTouchPoints > 1 && platform === 'MacIntel';
        console.log(isIpad);
        if (!isIpad) {
            // iPad以外の時、バナー表示のdivを非表示にする
            $('#info_banner').show();
        }
    </script>
</body>

</html>