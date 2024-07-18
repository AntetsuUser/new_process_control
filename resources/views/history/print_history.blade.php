@extends('layouts.app')

@section('css')
@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/history/print_history.css') }}" rel="stylesheet">
@else
    <link href="{{ asset('/css/history/print_history.css') }}" rel="stylesheet">
@endif
@endsection

@section('content')

<div class="browser_back_area">
    <a href="{{ url('/') }}"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span></a>
</div>
<div class="container">
    <div class="row justify-content-center">
        <div class="print_title">印刷履歴</div>
        <div class="search_area">
            <div class="input_seitch">
                <input type="radio" name="s2" id="not_entered" value="1" checked="">
                <label for="not_entered" class="switch-on">未入力</label>
                <input type="radio" name="s2" id="entered" value="0">
                <label for="entered" class="switch-off">入力済み</label>
            </div>
        </div>
        <div class="table_area">
            <table id="print_history_table">
                <tr class="th_fixed">
                    <th></th>
                    <th class="filter_th" filter_btn>固有ID</th>
                    <th class="filter_th" filter_btn>品目名称</th>
                    <th class="filter_th" filter_btn>品目コード</th>
                    <th class="filter_th" filter_btn>子品番1</th>
                    <th class="filter_th" filter_btn>子品番2</th>
                    <th class="filter_th" filter_btn>納期</th>
                    <th class="filter_th" filter_btn>加工数</th>
                    <th class="filter_th" filter_btn>着手日</th>
                    <th class="filter_th" filter_btn>作業者</th>
                    <th class="filter_th" filter_btn>工程</th>
                    <th class="filter_th" filter_btn>W/C</th>
                </tr>
                @foreach($print_history as $data2)
                <tr class="entered_row">
                    <td><a href="#"><button class="reprint_btn">再印刷</button></a></td>
                    <td>{{$data2["characteristic_id"]}}</td>
                    <td>{{$data2["item_name"]}}</td>
                    <td>{{$data2["processing_item"]}}</td>
                    <td>{{$data2["child_part_number1"]}}</td>
                    <td>{{$data2["child_part_number2"]}}</td>
                    <td>{{$data2["delivery_date"]}}</td>
                    <td>{{$data2["processing_quantity"]}}</td>
                    <td>{{$data2["start_date"]}}</td>
                    <td>{{$data2["woker_id"]}}</td>
                    <td>{{$data2["process"]}}</td>
                    <td>{{$data2["workcenter"]}}</td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>

</div>

@endsection