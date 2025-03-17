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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
         {{-- <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script> <!-- これを追加 --> --}}
    <!-- 拡大できないようにする -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script> <!-- これを追加 -->
    @yield('css')
    <style>
        @media screen and (max-width: 1920px) {
            body {
                touch-action: pan-x pan-y;
            }
        }
        .username{
            font-size: 20px;
            color: white;
        }
    </style>
</head>

<body>
    <div id="app">
        {{-- <div  id="user" hidden>{{ Auth::user()->name }}</div> --}}
        <nav class="navbar navbar-expand-md navbar-light nav_back shadow-sm">
            <div class="container">
                @if(Auth::guard('web')->check())
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', '工程管理システム') }}
                    </a>
                    <div id="user" hidden>{{ Auth::user()->name }}</div>
                @else
                    <a class="navbar-brand" href="{{ route('login') }}">
                        {{ config('app.name', '工程管理システム') }}
                    </a>
                    <div id="user" hidden>no</div>
                @endif
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div>
                    @yield('nav_title')
                </div>
                <div class="collapse navbar-collapse d-flex justify-content-end" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                </div>
                <div>
                    @if(Auth::guard('web')->check())
                       <div class="dropdown">
                            <!-- ユーザー名を表示 -->
                            <button class="btn btn-link dropdown-toggle username" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ Auth::user()->name }}
                            </button>
                            <!-- ドロップダウンメニュー -->
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="dropdown-item ">ログアウト</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <!-- ログインしていない場合の表示 -->
                        <a class="nav-link username" href="{{ route('login') }}">ログイン</a>
                    @endif
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
        var user = $('#user').text();
        console.log(user);
        console.log(localStorage.getItem('redirected'));
        // 初回遷移の場合、URLに遷移
        if (user == "no" && localStorage.getItem('redirected') != "true") {
            localStorage.setItem('redirected', 'true');  // リダイレクト済みフラグを設定
            window.location.href = "https://192.168.3.96/login";
        } else if (user != "no") {
            // ユーザーが"no"以外の場合、フラグをリセット
            localStorage.setItem('redirected', 'false');  // リダイレクト済みフラグをリセット
        }
    </script>
</body>

</html>