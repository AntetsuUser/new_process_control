@extends('layouts.app')

@section('css')

@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/masta/number.css') }}" rel="stylesheet">

@else
    <link href="{{ asset('/css/masta/number.css') }}" rel="stylesheet">

@endif

@endsection

@section('content')

<div id="easyModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h1>工程一覧</h1>
            <span class="modalClose">×</span>
        </div>
        <div class="modal-body">
            <div class="container">
                <div class="row">
                    <div class="col-md-3">
                        <div class="main_box">
                            <p>加工品目</p>
                            <input type="hidden" id="selection_id" name="selection_id" value="1">
                            <input type="text" name="parent_name" id="parent_name" readonly>
                        </div>
                        <div class="sub_box1">
                            <p>組立工程</p>
                            <input type="text" id="process00" name="parent_process0"  readonly>
                            <p>ストア・W/C</p>
                            <input type="text" id="store00" name="parent_store0"  readonly>
                            <p>加工時間</p>
                            <input type="text" id="time00" name="parent_time0"  readonly>
                            <p>工程ロット</p>
                            <input type="text" id="lot00" name="parent_lot0"  readonly>
                        </div>
                        <div class="sub_box2">
                            <p>加工工程</p>
                            <input type="text" id="process01" name="parent_process1"  readonly>
                            <p>ストア・W/C</p>
                            <input type="text" id="store01" name="parent_store1"  readonly>
                            <p>加工時間</p>
                            <input type="text" id="time01" name="parent_time1"  readonly>
                            <p>工程ロット</p>
                            <input type="text" id="lot01" name="parent_lot1"  readonly>
                        </div>
                        <div class="sub_box1">
                            <p>組立工程</p>
                            <input type="text" id="process02" name="parent_process2"  readonly>
                            <p>ストア・W/C</p>
                            <input type="text" id="store02" name="parent_store2"  readonly>
                            <p>加工時間</p>
                            <input type="text" id="time02" name="parent_time2"  readonly>
                            <p>工程ロット</p>
                            <input type="text" id="lot02" name="parent_lot2"  readonly>
                        </div>
                        <div class="sub_box2">
                            <p>加工工程</p>
                            <input type="text" id="process03" name="parent_process3"  readonly>
                            <p>ストア・W/C</p>
                            <input type="text" id="store03" name="parent_store3"  readonly>
                            <p>加工時間</p>
                            <input type="text" id="time03" name="parent_time3"  readonly>
                            <p>工程ロット</p>
                            <input type="text" id="lot03" name="parent_lot3"  readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="main_box">
                            <p>加工品目</p>
                            <input type="text" name="child_name1" id="child_name1" readonly>
                        </div>
                        <div class="sub_box1">
                            <p>加工工程</p>
                            <input type="text" id="process10" name="child1_process0"  readonly>
                            <p>ストア・W/C</p>
                            <input type="text" id="store10" name="child1_store0"  readonly>
                            <p>加工時間</p>
                            <input type="text" id="time10" name="child1_time0"  readonly>
                            <p>工程ロット</p>
                            <input type="text" id="lot10" name="child1_time0"  readonly>
                        </div>
                        <div class="sub_box2">
                            <p>加工工程</p>
                            <input type="text" id="process11" name="child1_process1"  readonly>
                            <p>ストア・W/C</p>
                            <input type="text" id="store11" name="child1_store1"  readonly>
                            <p>加工時間</p>
                            <input type="text" id="time11" name="child1_time1"  readonly>
                            <p>工程ロット</p>
                            <input type="text" id="lot11" name="child1_time1"  readonly>
                        </div>
                        <div class="sub_box1">
                            <p>組立工程</p>
                            <input type="text" id="process12" name="child1_process2"  readonly>
                            <p>ストア・W/C</p>
                            <input type="text" id="store12" name="child1_store2"  readonly>
                            <p>加工時間</p>
                            <input type="text" id="time12" name="child1_time2"  readonly>
                            <p>工程ロット</p>
                            <input type="text" id="lot12" name="child1_time2"  readonly>
                        </div>
                        <div class="sub_box2">
                            <p>加工工程</p>
                            <input type="text" id="process13" name="child1_process3"  readonly>
                            <p>ストア・W/C</p>
                            <input type="text" id="store13" name="child1_store3"  readonly>
                            <p>加工時間</p>
                            <input type="text" id="time13" name="child1_time3"  readonly>
                            <p>工程ロット</p>
                            <input type="text" id="lot13" name="child1_time3"  readonly>
                        </div>
                    </div>
                    <div class="col-md-3">

                        <div class="main_box">
                            <p>加工品目</p>
                            <input type="text" name="child_name2" id="child_name2" readonly>
                        </div>
                        <div class="sub_box1">
                            <p>加工工程</p>
                            <input type="text" id="process20" name="child2_process0"  readonly>
                            <p>ストア・W/C</p>
                            <input type="text" id="store20" name="child2_store0"  readonly>
                            <p>加工時間</p>
                            <input type="text" id="time20" name="child2_time0"  readonly>
                            <p>工程ロット</p>
                            <input type="text" id="lot20" name="child2_time0"  readonly>
                        </div>
                        <div class="sub_box2">
                            <p>加工工程</p>
                            <input type="text" id="process21" name="child2_process1"  readonly>
                            <p>ストア・W/C</p>
                            <input type="text" id="store21" name="child2_store1"  readonly>
                            <p>加工時間</p>
                            <input type="text" id="time21" name="child2_time1"  readonly>
                            <p>工程ロット</p>
                            <input type="text" id="lot21" name="child2_time0"  readonly>
                        </div>
                        <div class="sub_box1">
                            <p>組立工程</p>
                            <input type="text" id="process22" name="child2_process2"  readonly>
                            <p>ストア・W/C</p>
                            <input type="text" id="store22" name="child2_store2"  readonly>
                            <p>加工時間</p>
                            <input type="text" id="time22" name="child2_time2"  readonly>
                            <p>工程ロット</p>
                            <input type="text" id="lot22" name="child2_time0"  readonly>
                        </div>
                        <div class="sub_box2">
                            <p>加工工程</p>
                            <input type="text" id="process23" name="child2_process3"  readonly>
                            <p>ストア・W/C</p>
                            <input type="text" id="store23" name="child2_store3"  readonly>
                            <p>加工時間</p>
                            <input type="text" id="time23" name="child2_time3"  readonly>
                            <p>工程ロット</p>
                            <input type="text" id="lot23" name="child2_time0"  readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="browser_back_area">
    <a href="{{ route('masta') }}"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span></a>
</div>
<div class="container">
    <div class="row justify-content-enter">
        <div class="table_area">
            <div>
                <p class="contents_title">品目マスタ</p>
            </div>
            <a href="{{ route('masta.number_insert') }}" class="btn btn-primary main_btn">品目追加</a>
            <div class="table_div">
                <table id="number_list_table" class="filtering_table">
                    <thead>
                        <tr>
                            <th></th>
                            <th id="col1">No.</th>
                            <th>工程一覧</th>
                            <th id="col2">図面番号</th>
                            <th id="col3">加工品目</th>
                            <th id="col4" >品目名称</th>
                            <th id="col5">材料品目</th>
                            <th id="col6">品目集約</th>
                            <th id="col9">子品番1</th>
                            <th id="col10">子品番2</th>
                            <th id="col11">工場</th>
                            <th id="col12">製造課</th>
                            <th id="col13">ライン</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($number as $item)
                            <tr>
                                <td><a href="{{ route('masta.number_insert',['id' => $item->id]) }}"><button class="btn btn-success">更新</button></a></td>
                                <td>{{ $item->id }}</td>
                                <td><button class="modalOpen button" data-id="{{ $item->id }}" data-processing-item="{{ $item->processing_item }}" data-child-part-number1="{{ $item->child_part_number1 }}" data-child-part-number2="{{ $item->child_part_number2 }}">工程一覧</button></td>
                                <td>{{ $item->print_number }}</td>
                                <td>{{ $item->processing_item }}</td>
                                <td>{{ $item->item_name }}</td>
                                <td>{{ $item->material_item }}</td>
                                <td>{{ $item->collect_name }}</td>
                                <td>{{ $item->child_part_number1 }}</td>
                                <td>{{ $item->child_part_number2}}</td>
                                <td>{{ $item->factory_name }}</td>
                                <td>{{ $item->department_name }}</td>
                                <td>{{ $item->line }}</td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>
@if(config('app.env') === 'production')
    <script src="{{secure_asset('js/masta/number.js')}}"></script>
    <script src="{{secure_asset('js/other_filtering.js')}}" ></script>
@else
    <script src="{{asset('js/masta/number.js')}}"></script>
    <script src="{{asset('js/other_filtering.js')}}" ></script>
@endif
@endsection