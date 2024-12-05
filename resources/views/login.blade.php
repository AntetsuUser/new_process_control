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

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form_group">
                <label for="username">ユーザー名</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form_group">
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form_group">
                <button type="submit">ログイン</button>
            </div>
        </form>
        <div>新規登録はこちら</div>
    </div>
</div>


@if(config('app.env') === 'production')

@else

@endif
@endsection