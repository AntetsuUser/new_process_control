@extends('layouts.app')

@section('css')
@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/index.css') }}" rel="stylesheet">
@else
    <link href="{{ asset('/css/index.css') }}" rel="stylesheet">
@endif
@endsection

@section('content')
    
    <div class="browser_back_area">
        <a href="{{ url('/') }}"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span></a>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="main_contents select_main">
                @if (session('message'))
                    <div class="alert alert-warning">
                        {{ session('message')  }}
                    </div>
                @endif
                <form action="{{ route('load_prediction.process') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="from_area">
                        <p class="from_title">製造課</p>
                        <select name="division" id="division" class="" required>
                            <option value="--- 選択してください ---" disabled selected>--- 選択してください ---</option>
                            @foreach ($department as $data)
                            <option value="{{ $data->id }}">{{ $data->name }}</option>
                            @endforeach
                        </select>
                        <input type="submit" value="送信" class="submit_btn">
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection