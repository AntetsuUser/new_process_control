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

        <form method="POST" action="{{ route('signup_entry') }}"　>
            @csrf

            <div class="form_group">
                <label for="name">ユーザー名</label>
                <input type="text" id="name" name="name" placeholder="例：" pattern="^[a-zA-Z0-9_-]+$">
            </div>
            @error('name')
                <div class="ms-3 text-danger">{{ $message }}</div>
            @enderror


            <div class="form_group">
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password" pattern="^[a-zA-Z0-9_%\-]{6,12}$"  placeholder="半角英数字6～12文字">
            </div>
            @error('password')
                <div class="ms-3 text-danger">{{ $message }}</div>
            @enderror


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
            @error('all_departments_id')
                <div class="ms-3 text-danger">{{ $message }}</div>
            @enderror
            @error('positions_id')
                <div class="ms-3 text-danger">{{ $message }}</div>
            @enderror

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