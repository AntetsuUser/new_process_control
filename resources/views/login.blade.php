@extends('layouts.app')

@section('css')
@if(config('app.env') === 'production')

    <link href="{{ secure_asset('/css/login/login.css') }}" rel="stylesheet">
@else
    <link href="{{ asset('/css/login/login.css') }}" rel="stylesheet">
@endif
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="center">
    <div class="login_container">
        <h3>ログイン</h3>

        <form method="POST" action="{{ route('login_entry') }}" autocomplete="on">
            @csrf

            <div class="form_group">
                <label for="username">ユーザー名</label>
                <input type="text" id="username" name="username" value="{{ old('username',$username ?? '') }}" autocomplete="username" required >
            </div>
             @error('username')
                <div class="ms-3 text-danger">{{ $message }}</div>
            @enderror
            <div class="form_group">
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password" autocomplete="current-password"  value="{{ old('password', $password ?? '') }}"  required  >
            </div>
            @error('password')
                <div class="ms-3 text-danger">{{ $message }}</div>
            @enderror
            <div>
                <label for="remember">ログイン状態を保持</label>
                <input type="checkbox" name="remember" id="remember" checked>
            </div>

            <div class="form_group">
                <button type="submit">ログイン</button>
            </div>
        </form>
        <div><a href="{{ route('signup') }}">新規登録はこちら</a></div>
    </div>
</div>


@if(config('app.env') === 'production')
{{-- //login.js --}}
    <script src="{{secure_asset('js/login.js')}}"></script>
@else
    <script src="{{asset('js/login.js')}}"></script>
@endif
@endsection