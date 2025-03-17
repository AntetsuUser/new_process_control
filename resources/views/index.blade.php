@extends('layouts.app')

@section('css')
@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/index.css') }}" rel="stylesheet">
@else
    <link href="{{ asset('/css/index.css') }}" rel="stylesheet">
@endif
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
{{-- テストですよ --}}

<div class="aaa"></div>
<div class="container">
    <div class="row justify-content-center main_box">
        <div class="col-md-3">
            <div class="sub_box">
                <a href="{{ route('load_prediction.department_select') }}" class="btn btn-primary main_btn ajax-link">
                    <img src="./img/icon/icon_predict.png" alt="" srcset="">
                    <p>負荷予測</p>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="sub_box">
                <a href="{{ route('longinfo.select') }}" class="btn btn-primary main_btn ajax-link">
                    <img src="./img/icon/icon_info.png" alt="" srcset="">
                    <p>長期情報</p>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="sub_box">
                <a href="{{ route('qr.qrcamera') }}" class="btn btn-primary main_btn ajax-link">
                    <img src="./img/icon/icon_qr.png" alt="" srcset="">
                    <p>QR読取</p>

                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="sub_box">
                <a href="{{ route ('masta') }}" class="btn btn-primary main_btn ajax-link">
                    <img src="./img/icon/icon_master.png" alt="" srcset="">
                    <p>マスタ管理</p>
                </a>
            </div>
        </div>
    </div>

    <div class="row justify-content-center contents2">
        <div class="col-md-3">
            <div class="sub_box">
                <a href="{{ route('history.print') }}" class="btn btn-primary main_btn ajax-link">
                    <img src="./img/icon/icon_printer.png" alt="" srcset="">
                    <p>印刷履歴</p>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="sub_box">
                <a href="{{ route('history.processing') }}" class="btn btn-primary main_btn ajax-link">
                    <img src="./img/icon/icon_input.png" alt="" srcset="">
                    <p>入力履歴</p>
                </a>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="sub_box " id="tablet">
                <a href="{{ route('signage.tablet') }}" class="btn btn-primary main_btn ajax-link">
                    <img src="./img/icon/progress.png" alt="" srcset="">
                    <p>進捗確認</p>
                </a>
            </div>
        </div>
        <div class="col-md-3 ">
            <div class="sub_box " id="monitor">
                <a href="{{ route('signage.main') }}" class="btn btn-primary main_btn ajax-link">
                    <img src="./img/icon/display.png" alt="" srcset="">
                    <p>モニター</p>
                </a>
            </div>
        </div>
        <!-- <div class="col-md-3">

        </div> -->
    </div>
</div>
{{-- logを残すためにlogのURLをjsファイルに渡す --}}
<script>
    var logRoute = "{{ route('log.main') }}";

</script>
{{-- aタグのどこが押されたかをリンクで送る --}}
<script>
    $(document).ready(function () {
        $('.ajax-link').on('click', function (e) {
            e.preventDefault(); // デフォルトの遷移を停止

            let link = $(this).attr('href'); // クリックされたリンクのhrefを取得
             let clickedText = $(this).find('p').text();

            // CSRFトークンをヘッダーに設定
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // AJAXでリンク情報を送信
            $.ajax({
                url: "{{ route('log.atag') }}", // コントローラーのエンドポイント
                method: 'POST',
                data: {
                    link: link, // クリックされたリンク先のURL
                    text: clickedText // クリックされた要素の親のidを送信（任意）
                },
                success: function (response) {
                    console.log('レスポンス:', response);

                    // レスポンスを確認後に遷移
                    window.location.href = link; // 元のリンク先に遷移
                },
                error: function (error) {
                    console.error('エラー:', error);
                    alert('エラーが発生しました。');
                }
            });
        });
    });
</script>

@if(config('app.env') === 'production')
    <script src="{{secure_asset('js/monitor.js')}}<?php echo '?key='.rand();?>"></script>
    <script src="{{secure_asset('js/log.js')}}<?php echo '?key='.rand();?>"></script>
@else
    <script src="{{asset('js/monitor.js')}}<?php echo '?key='.rand();?>"></script>
    <script src="{{asset('js/log.js')}}<?php echo '?key='.rand();?>"></script>
    {{-- /home/pi/Desktop/process_control/public/js/local_ip.js --}}
@endif
@endsection
