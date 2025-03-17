<head>
    <meta charset="UTF-8">
    <!-- スマートフォンでのズームを無効にする -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <!-- CSS -->
    @if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/qr/qr.css') }}" rel="stylesheet">
    @else
        <link href="{{ asset('/css/qr/qr.css') }}" rel="stylesheet">
    @endif
    <!-- JavaScript -->
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <a href="{{ url('/') }}">
    <!-- <a href="https://processcontrol.antetsu-systems.com/"> -->
        <div class="home-icon">
            <img src="../img/qr/home.svg" alt="">
        </div>
    </a>
    <div id="help_id" class="help_area">
        <div>
            QRコードが読み込めないときは
            <img src="../img/qr/icon8.svg" alt="">
        </div>
    </div>
    <div id="wrapper">
        <!-- <div id="msg">QRコード読み込みます</div> -->
        <canvas id="canvas"></canvas>
    </div>
    <!-- QR読み取れた時のモーダル部分 -->
    <div id="js-modal" class="modal fade-in">
        <div class="modal-content">
            <p class="modal-title">読み取り結果</p>
            <p id="js-result"></p>
            <div class="modal-url">
                <a href="" id="js-link" class="modal-btn">開く</a>
                <a type="button" id="js-modal-close" class="a_btn">閉じる</a>
            </div>
        </div>
    </div>
    <!-- IDを入力する時のモーダル部分 -->
    <div id="help-ja-modal" class="modal fade-in">
        <div class="modal-content">
            <p class="modal-title">IDを入力してください</p>
            <p id="id-not-found" class="not-found-none">※IDが見つかりませんでした</p>
            <input type="text" id="help-id-input" maxlength="18" oninput="value = value.replace(/[^0-9]+/i,'');">
            <table class="help_btn_table">
                <tr class="help-btn_tr">
                    <td class="help-btn_td"><a id="help-js-link" class="blue">開く</a></td>
                    <!-- <td style="width: 15px;"></td> -->
                    <td class="help-btn_td"><p href="" class="gray" id="help-modal-close">閉じる</p></td>
                </tr>
            </table>
            </div>
        </div>
    </div>
    <script> 
        var log_camera_url = "{{ route('log.camera') }}" 
    </script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    @if(config('app.env') === 'production')
        <script src=" {{secure_asset('./js/qr/jsQR.js')}}"></script>
        <script src="{{secure_asset('./js/qr/qrmain.js')}}"></script>
    @else
        <script src=" {{asset('./js/qr/jsQR.js')}}"></script>
        <script src="{{asset('./js/qr/qrmain.js')}}"></script>
    @endif
</body>