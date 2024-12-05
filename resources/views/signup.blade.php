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
        <h3>新規登録</h3>

        <form method="POST" action="">
            @csrf

            <div class="form_group">
                <label for="username">ユーザー名</label>
                <input type="text" id="name" name="name" required autofocus>
            </div>

            <div class="form_group">
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password" required>
            </div>

             <div class="form_group flex">
                <select name="all_departments_id" id="all_departments_id">
                    <option value="">所属</option>
                    @foreach ($department as $item)
                        <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                    @endforeach
                </select>
                <select name="positions_id" id="positions_id">
                    <option value="">役職</option>
                     @foreach ($position as $item)
                        <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form_group">
                <button type="submit">登録</button>
            </div>
        </form>
    </div>
</div>

@if(config('app.env') === 'production')

@else

@endif
@endsection