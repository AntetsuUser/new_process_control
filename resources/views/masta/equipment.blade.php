@extends('layouts.app')

@section('css')


@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/masta/equipment.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('/css/loading.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">
@else
    <link href="{{ asset('/css/masta/equipment.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/loading.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">
@endif
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
@endsection

@section('content')


<!-- メイン -->
<div class="browser_back_area">
    <a href="{{ url('masta') }}"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span></a>
</div>
<div class="container">
    <div>
        <p class="contents_title">設備マスタ</p>
    </div>
    <div>
        <a href="{{ route('masta.equipment_insert') }}" id="output" class="btn btn-primary main_btn addition">設備追加</a>
    </div>
    <div class="table_area">
        <table id="facility_table" class="filtering_table">
            <thead>
                <tr class="th_fixed">
                    <th></th>
                    <th>No.</th>
                    <th>工場</th>
                    <th>製造課</th>
                    <th>ライン</th>
                    <th>設備No</th>
                    <th>区分</th>
                    <th>型式</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($equipment as $value)
                    <tr>
                        <td><a href="{{ route('masta.equipment_insert',['id' => $value->id]) }}"><button class="update_btn">更新</button></a></td>
                        <td>{{ $value->id }}</td>
                        <td>{{ $value->factory_name }}</td>
                        <td>{{ $value->department_name }}</td>
                        <td>{{ $value->line }}</td>
                        <td>{{ $value->equipment_id }}</td>
                        <td>{{ $value->category }}</td>
                        <td>{{ $value->model }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if(config('app.env') === 'production')
    <script src=" {{secure_asset('js/masta/equipment.js')}}"></script>
        <script src="{{secure_asset('js/other_filtering.js')}}" ></script>
@else
    <script src=" {{asset('js/masta/equipment.js')}}"></script>
        <script src="{{asset('js/other_filtering.js')}}" ></script>
@endif
@endsection