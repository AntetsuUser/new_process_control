@extends('layouts.app')

@section('css')

@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/reset.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('/css/longinfos/print.css') }}" rel="stylesheet">
    <script src="https://unpkg.com/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>
@else
    <link href="{{ asset('/css/reset.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('/css/longinfos/print.css') }}" rel="stylesheet">
    <script src="https://unpkg.com/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>
@endif
@endsection

@section('content')
<div class="browser_back_area">
     <a href="#" onclick="window.history.back(); return false;"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt="" style="width: 2.5%;"><span>戻る</span></a>
</div>
<!-- モーダルエリアここから -->
<div id="modalArea" class="modalArea">
    <div id="modalBg" class="modalBg"></div>
    <div class="modalWrapper">
        <div class="modalContents">
            <h1 class="modal_text">この指示書を入力済みにしますか？</h1>
            <div class="modal_btn_area">
                <form action="{{ route('history.entered') }}"  method="POST">
                    @csrf
                    <input type="hidden" name="directions_id" value="{{$print_history[0]['characteristic_id']}}">
                    <button type="submit" id="modal_submit" class="btn btn-info">実行</button>
                </form>
            </div>
        </div>
        <div id="closeModal" class="closeModal">×</div>
    </div>
</div>
<!-- モーダルエリアここまで -->
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="btn_area">
            <form>
                <input type="button" value="印刷" id="print_btn" class="btn btn--orange" onclick="window.print();">
            </form>

            <button type="button" id="id_btn" class="btn btn-info">指示書反映</button>
        </div>
        <div id="print_main" class="print_main_area">
        @foreach($print_history as $value)
        <p class="title">作業指示書 兼 現品票</p>
        <div class="row page_break">
                <div class="container table">
                    <div class="row table_border">
                        <div class="col-sm-9 table_border_bottom">
                            <div class="row ">
                                <div class="col-sm-2-5 table_border_bottom table_border_right d-flex align-items-center justify-content-center padding">品名</div>
                                <div class="col-sm-9-5 table_border_bottom table_border_right d-flex align-items-center  justify-content-center padding">{{$value['item_name']}}</div>
                                <div class="col-sm-2-5 table_border_right d-flex align-items-center justify-content-center">品番</div>
                                <div class="col-sm-9-5 table_border_right d-flex align-items-center div_big justify-content-center">{{$value['processing_item']}}</div>
                            </div>
                        </div>
                        <div class="col-sm-3 table_border_bottom d-flex align-items-center justify-content-center">
                            <div class="qr_area">
                                <div id="qrCode{{$value['characteristic_id']}}"></div>
                            </div>
                        </div>
                        <!-- 組付けの処理も一緒 -->
                        @if($value['item_name'] == "シャフトアッシー")
                        <div class="col-sm-2 table_border_bottom table_border_right d-flex align-items-center justify-content-center padding">シャフト</div>
                        <div class="col-sm-4 table_border_bottom table_border_right d-flex align-items-center justify-content-center padding">{{$value['child_part_number1']}}</div>
                        <div class="col-sm-2 table_border_bottom table_border_right d-flex align-items-center justify-content-center padding">ホールド</div>
                        <div class="col-sm-4 table_border_bottom d-flex align-items-center justify-content-center padding">{{$value['child_part_number2']}}</div>
                        @else
                        <div class="col-sm-4 table_border_bottom table_border_right d-flex align-items-center justify-content-center padding">シャフトアッシー</div>
                        <div class="col-sm-8 table_border_bottom d-flex align-items-center justify-content-center padding">{{$value['parent_name']}}</div>
                        @endif

                        <div class="col-sm-2 table_border_bottom table_border_right d-flex align-items-center justify-content-center div_big2 padding">納期日</div>
                        <div class="col-sm-4 table_border_bottom table_border_right d-flex align-items-center justify-content-center div_big2 padding">{{$value['delivery_date']}}</div>
                        <div class="col-sm-2 table_border_bottom table_border_right d-flex align-items-center justify-content-center div_big2 padding">加工数</div>
                        <div class="col-sm-4 table_border_bottom d-flex align-items-center justify-content-center div_big2 padding">{{$value['processing_quantity']}} <span class="span_text">({{$value['processing_all']}}/{{$value['long_term_all']}})</span></div>

                        <div class="col-sm-2 table_border_bottom table_border_right d-flex align-items-center justify-content-center padding">着手日</div>
                        <div class="col-sm-4 table_border_bottom table_border_right d-flex align-items-center justify-content-center padding">{{$value['start_date']}}</div>
                        <div class="col-sm-2 table_border_bottom table_border_right d-flex align-items-center justify-content-center padding">作業者</div>
                        <div class="col-sm-4 table_border_bottom d-flex align-items-center justify-content-center padding">{{$value['woker_id']}}</div>

                        <?php
                        
                            if (strpos($value['process'], "NC") !== false) {
                                // 文字列に "NC" が含まれているかを確認してから置き換え
                                $string = str_replace("NC", "旋盤", $value['process']);
                                $process = $string;
                                if(strpos($process, "MC") !== false)
                                {
                                    $string2 = str_replace("MC", "マシニング", $process);
                                    $process = $string2;
                                }
                            }else if (strpos($value['process'], "MC") !== false) {
                                // 文字列に "MC" が含まれているかを確認してから置き換え
                                $string = str_replace("MC", "マシニング", $value['process']);
                                $process = $string;
                            }else{
                                $process = $value['process'];
                            }
                        ?>
                        <div class="col-sm-2 table_border_bottom table_border_right d-flex align-items-center justify-content-center padding">工程</div>
                        <div class="col-sm-4 table_border_bottom table_border_right d-flex align-items-center justify-content-center padding">{{ $process }}</div>
                        <div class="col-sm-2 table_border_bottom table_border_right d-flex align-items-center justify-content-center padding">W/C</div>
                        <div class="col-sm-4 table_border_bottom d-flex align-items-center justify-content-center padding">{{$value['workcenter']}}</div>

                        <div class="col-sm-6 table_border_right d-flex align-items-center justify-content-center padding"><span>ID　</span>{{$value['characteristic_id']}}</div>
                        <div class="col-sm-2 table_border_right d-flex align-items-center justify-content-center padding">確認者</div>
                        <div class="col-sm-4 padding"></div>
                    </div>
                </div>
            </div>
            <script>
                var url = "/qr/input_directions?characteristic_id=" + "{{$value['characteristic_id']}}"
                var qrCode = new QRCodeStyling({
                    width: 130,
                    height: 130,
                    type: "canvas",
                    data: url,
                    qrOptions: {
                        errorCorrectionLevel: 'Q'
                    },
                    dotsOptions: {
                        color: "#000",
                        type: "square"
                    },
                    cornersSquareOptions: {
                        type: "square"
                    },
                    cornersDotOptions: {
                        type: "square"
                    },
                    backgroundOptions: {
                        color: "#fff",
                    },
                    imageOptions: {
                        crossOrigin: "anonymous",
                        margin: 0,
                    }
                });

                /// 要素に生成されたQRコードを表示
                var $qrCode = document.getElementById('qrCode' + "{{$value['characteristic_id']}}");
                qrCode.append($qrCode);
            </script>
        @endforeach
        </div>
    </div>
</div>
<script>
$(function () {
    $('#id_btn').click(function(){
        $('#modalArea').fadeIn();
    });
    $('#closeModal , #modalBg , #btn_close_modal').click(function(){
        $('#modalArea').fadeOut();
    });
});
</script>

@if(config('app.env') === 'production')
    <script src="https://unpkg.com/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>
@else
    <script src="https://unpkg.com/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>
@endif
@endsection