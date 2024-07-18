@extends('layouts.app')

@section('css')

@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/reset.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('/css/index.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('/css/loading.css') }}" rel="stylesheet">
@else
    <link href="{{ asset('/css/reset.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/index.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/loading.css') }}" rel="stylesheet">
@endif

@endsection

@section('content')
<div class="browser_back_area">
    <a href="{{ url('/') }}"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span></a>
</div>
<div class="container">
    <div class="row justify-content-center main_box">
        <div class="col-md-3">
            <div class="sub_box">
                <a href="{{ route('masta.number') }}" class="btn btn-primary main_btn">
                    <img src="./img/masta/icon_number.png" alt="" srcset="">
                    <p>品目マスタ</p>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="sub_box">
                <a href="{{ route ('masta.equipment') }}" class="btn btn-primary main_btn">
                    <img src="./img/masta/icon_facility.png" alt="" srcset="">
                    <p>設備マスタ</p>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="sub_box">
                <a href="{{ route ('masta.worker') }}" class="btn btn-primary main_btn">
                    <img src="./img/masta/icon_worker.png" alt="" srcset="">
                    <p>作業者マスタ</p>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="sub_box">
                <a href="{{ route ('masta.store') }}" class="btn btn-primary main_btn">
                    <img src="./img/masta/icon_store.png" alt="" srcset="">
                    <p>ストアマスタ</p>
                </a>
            </div>
        </div>
    </div>
    <div class="row justify-content-center main_box">
        <div class="col-md-3">
            <div class="sub_box">
                <a href="{{ route('masta.upload') }}" class="btn btn-primary main_btn">
                    <img src="./img/icon/icon_upload.png" alt="" srcset="">
                    <p>アップロード</p>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="sub_box">
                <a href="{{ route ('masta.calendar') }}" class="btn btn-primary main_btn">
                    <img src="./img/masta/icon_calendar.png " alt="" srcset="">
                    <p>カレンダー</p>
                </a>
            </div>
        </div>
        <div class="col-md-3">
        </div>
        <div class="col-md-3">
        </div>
    </div>
</div>
@endsection