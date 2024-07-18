@extends('layouts.app')

@section('css')

@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/qr/directions.css') }}" rel="stylesheet">
@else
    <link href="{{ asset('/css/qr/directions.css') }}" rel="stylesheet">
@endif

@endsection

@section('content')


<div class="browser_back_area container-fluid">
    <div class="row">
        <div class="col-md-1">
            <a href="#" onclick="history.back(-1);return false;"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span></a>
        </div>
    </div>
</div>
<div class="container">
        <div class="row justify-content-center">
            <!-- モーダル本体 -->
            <div class="modal js_modal">
                <div class="modal-container">
                    <!-- モーダルを閉じるボタン -->
                    <div class="modal-close js_modal_close">×</div>
                    <!-- モーダル内部のコンテンツ -->
                    <div class="modal-content">
                        <p id="fix_title" class="fix_title">以下の内容で送信してよろしいですか？</p>
                        <table class="fix_table">
                            <tr id="processing_number">
                                <td class="sub_text">加工数</td>
                            </tr>
                            <tr id="good_item">
                                <td class="sub_text">良品</td>
                            </tr>
                            <tr id="processing_defect">
                                <td class="sub_text">加工不良</td>
                            </tr>
                            <tr id="material_defect">
                                <td class="sub_text">材料不良</td>
                            </tr>
                        </table>
                        <button onclick="undisabled();" type="submit" class="fix_button" form="achievement">確定</button>
                    </div>
                </div>
            </div>
            <div class="col-md-10 outer">
                <div class="main_contents">
                    <form id="achievement" class="form" action="http://192.168.3.91:8002/achievement_entry" method="post">
                        <table class="main_tbl" id="main_tbl">
                            
                            <tr>
                                <th>品名</th>
                                <td></td>
                                <th>品番</th>
                                <td></td>
                            </tr>
                            <tr id="limited_item" class="">
                                <th>シャフト</th>
                                <td></td>
                                <th>ホールド</th>
                                <td></td>
                            </tr>
                            <tr>
                                <th>固有ID</th>
                                <td></td>
                                <th>納期日</th>
                                <td></td>
                            </tr>
                            <tr>
                                <th>工程</th>
                                <td></td>
                                <th>W/C</th>
                                <td></td>
                            </tr>
                            <tr>
                                <th>加工数</th>
                                <td></td>
                                <th class="asterisk_box"><span class="asterisk">*</span>良品</th>
                                <td></td>
                            </tr>
                            <tr>
                                <th class="asterisk_box"><span class="asterisk">*</span>加不</th>
                                <td></td>
                                <th class="asterisk_box"><span class="asterisk">*</span>材不</th>
                                <td></td>
                            </tr>

                        </table>
                    </form>
                    <div class="button_arae">
                        <button class="button js_modal_open">実行</button>
                        <a href="{{ route('qr.qrcamera') }}" class="button a_button">キャンセル</a>
                    </div>
                </div>
            </div>
        </div>
    </div>



@endsection
