@extends('layouts.app')

@section('css')
@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/index.css') }}" rel="stylesheet">
@else
    <link href="{{ asset('/css/index.css') }}" rel="stylesheet">
@endif
@endsection

@section('content')

<div class="container">
    <div class="row justify-content-center main_box">
        <div class="col-md-3">
            <div class="sub_box">
                <a href="{{ route('load_prediction.department_select') }}" class="btn btn-primary main_btn">
                    <img src="./img/icon/icon_predict.png" alt="" srcset="">
                    <p>負荷予測</p>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="sub_box">
                <a href="{{ route('longinfo.select') }}" class="btn btn-primary main_btn">
                    <img src="./img/icon/icon_info.png" alt="" srcset="">
                    <p>長期情報</p>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="sub_box">
                        <!-- /home/pi/Desktop/Process_Control/qr_reading/index.html -->
                <a href="{{ route('qr.qrcamera') }}" class="btn btn-primary main_btn">
                    <img src="./img/icon/icon_qr.png" alt="" srcset="">
                    <p>QR読取</p>

                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="sub_box">
                <a href="{{ route ('masta') }}" class="btn btn-primary main_btn">
                    <img src="./img/icon/icon_master.png" alt="" srcset="">
                    <p>マスタ管理</p>
                </a>
            </div>
        </div>
    </div>

    <div class="row justify-content-center contents2">
        <div class="col-md-3">
            <div class="sub_box">
                <a href="{{ route('history.print') }}" class="btn btn-primary main_btn">
                    <img src="./img/icon/icon_printer.png" alt="" srcset="">
                    <p>印刷履歴</p>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="sub_box">
                <a href="{{ route('history.processing') }}" class="btn btn-primary main_btn">
                    <img src="./img/icon/icon_input.png" alt="" srcset="">
                    <p>入力履歴</p>
                </a>
            </div>
        </div>
        <div class="col-md-3">
        </div>
        <div class="col-md-3">
            <!-- 機能を追加するにはここに記載 -->
        </div>
        <!-- <div class="col-md-3">

        </div> -->
    </div>
</div>
@endsection