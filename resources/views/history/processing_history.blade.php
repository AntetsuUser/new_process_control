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
        <div class="print_title">入力履歴</div>
        <table id="print_history_table">
        <tr class="th_fixed">
                <th class="filter_th" filter_btn>固有ID</th>
                <th class="filter_th" filter_btn>品名</th>
                <th class="filter_th" filter_btn>品番</th>
                <th class="filter_th" filter_btn>工程</th>
                <th class="filter_th" filter_btn>W/C</th>
                <th class="filter_th" filter_btn>加工予定数</th>
                <th class="filter_th" filter_btn>良品</th>
                <th class="filter_th" filter_btn>加工不良</th>
                <th class="filter_th" filter_btn>材料不良</th>
                <th class="filter_th" filter_btn>納期</th>
            </tr>
            {{-- processed_history --}}
            @foreach($processed_history as $data)
                <tr class="entered_row">
                    <td>{{$data["characteristic_id"]}}</td>
                    <td>{{$data["item_name"]}}</td>
                    <td>{{$data["processing_item"]}}</td>
                    <td>{{$data["process"]}}</td>
                    <td>{{$data["workcenter"]}}</td>
                    <td>{{$data["processing_plan_quantity"]}}</td>
                    <td>{{$data["good_item"]}}</td>
                    <td>{{$data["processing_defect_item"]}}</td>
                    <td>{{$data["material_defect_item"]}}</td>
                    <td>{{$data["delivery_date"]}}</td>
                </tr>
                @endforeach
        </table>                        
    </div>

</div>

@endsection