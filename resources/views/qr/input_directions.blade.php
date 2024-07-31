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
                        <table class="fix_table main_tbl">
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
                    <form id="achievement" class="form" action="{{ route('qr.input_succes') }}" method="post">
                        @csrf
                        <input type="hidden" name="unique_id" value="{{ $direction_date["characteristic_id"] }}">
                        <input type="hidden" name="parent_name" value="{{ $direction_date["parent_name"] }}">
                        <input type="hidden" name="create_day" value="{{ $direction_date["capture_date"] }}">
                        <input type="hidden" name="delivery_date" value="{{ $direction_date["delivery_date"] }}">
                        <input type="hidden" name="process" value="{{ $direction_date["process"] }}">
                        <input type="hidden" name="item_name" value="{{ $direction_date["item_name"] }}">
                        <input type="hidden" name="child_part_number1" value="{{ $direction_date["child_part_number1"] }}">
                        <input type="hidden" name="child_part_number2" value="{{ $direction_date["child_part_number2"] }}">
                        <input type="hidden" name="workcenter" value="{{ $direction_date["workcenter"] }}">
                        <input type="hidden" name="processing_quantity" value="{{ $direction_date["processing_quantity"] }}">
                        <input type="hidden" name="processing_item" value="{{ $direction_date["processing_item"] }}">
                        <table class="main_tbl" id="main_tbl">
                            
                            <tr>
                                <th>品名</th>
                                <td>{{ $direction_date["item_name"] }}</td>
                                <th>品番</th>
                                <td>{{ $direction_date["processing_item"] }}</td>
                            </tr>
                            @if ($direction_date["item_name"] == "シャフトアッシー")
                                <tr id="limited_item" class="">
                                    <th>シャフト</th>
                                    <td>{{ $direction_date["child_part_number1"] }}</td>
                                    <th>ホールド</th>
                                    <td>{{ $direction_date["child_part_number2"] }}</td>
                                </tr>
                            @endif
                            <tr>
                                <th>固有ID</th>
                                <td>{{ $direction_date["characteristic_id"] }}</td>
                                <th>納期日</th>
                                <td>{{ $direction_date["delivery_date"] }}</td>
                            </tr>
                            <tr>
                                <th>工程</th>
                                <td>{{ $direction_date["process"] }}</td>
                                <th>W/C</th>
                                <td>{{ $direction_date["workcenter"] }}</td>
                            </tr>
                            <tr>
                                <th>加工数</th>
                                <td><input type="text" name="processing" id="" value="{{ $direction_date['processing_quantity'] }}" placeholder="0" disabled="readonly"></td>
                                <th class="asterisk_box"><span class="asterisk">*</span>良品</th>
                                <td><input type="text" name="good_product" id="" value="" placeholder="0" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <th class="asterisk_box"><span class="asterisk">*</span>加不</th>
                                <td><input type="text" name="poor_processing" id="" value="" placeholder="0" inputmode="numeric"></td>
                                <th class="asterisk_box"><span class="asterisk">*</span>材不</th>
                                <td><input type="text" name="poor_material" id="" value="" placeholder="0" inputmode="numeric"></td>
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



@if(config('app.env') === 'production')
<script src="{{ secure_asset('./js/qr/input_confirmation.js') }}"></script>
@else
<script src="{{ asset('./js/qr/input_confirmation.js') }}"></script>
@endif

@endsection
