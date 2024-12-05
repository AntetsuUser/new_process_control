@extends('layouts.app')

<head>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

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
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif
        <div class="search_area">
            <div class="input_seitch">
                <input type="radio" name="s2" id="not_entered" value="1" checked="" onclick="toggleRows()">
                <label for="not_entered" class="switch-on">未入力</label>
                <input type="radio" name="s2" id="entered" value="0" onclick="toggleRows()">
                <label for="entered" class="switch-off">入力済み</label>
            </div>
            <div style="margin-left: 80px;"><button onclick="reset()"　>表示リセット</button></div>
            <div>
                {{-- <input type="text" id="searchInput" placeholder="検索...">
                <button onclick="searchTable()">検索</button> --}}
            </div>
        </div>
        <div class="table_area">
            <table id="print_history_table">
                <thead>
                    <tr class="th_fixed">
                    <th></th>
                    <th class="filter_th">固有ID<span class="filter_position" filter_btn></span></th>
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
                </thead>
                @foreach($print_history as $data2 )
                @if ($data2["input_complete_flag"] == "false" )
                    <tr class="entered_row "  data-flag="true">
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
                @else
                    <tr class="entered_row " data-flag="false">
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
                @endif
                @endforeach
            </table>
        </div>
    </div>

</div>
<script>
    //再印刷ボタンを押したとき
    let table = document.getElementById("print_history_table");
    const update_btn = document.getElementsByClassName("reprint_btn");

    for (let i = 0; i < update_btn.length; i++) 
    {
        update_btn[i].addEventListener("click", function () 
        {
            let ID = table.rows[i +1].cells[1].innerHTML
            console.log((i +1) + "番目のボタンがクリックされた IDは " + ID)
            const form = document.createElement('form');
            form.action = "reprint"
            form.method = "POST";
            // CSRFトークンをフォームに追加
            let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            const formValue = document.createElement("input")
            formValue.type = 'hidden'; 
            formValue.name = "id"
            formValue.value = ID
            form.appendChild(formValue)
            document.body.appendChild(form)
            form.submit()
        })
    }
</script>

@if(config('app.env') === 'production')
    <script src="{{secure_asset('js/filter.js')}}" ></script>

@else
    <script src="{{asset('js/filter.js')}}"></script>
@endif
@endsection