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
        <a href="{{ route('load_prediction.department_select') }}"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span></a>
    </div>
    <div class="container">
        <div class="row justify-content-center">
           
        </div>
    </div>
@endsection