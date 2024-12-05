@extends('layouts.app')

@section('css')
@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/reset.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('/css/masta/workers.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('/css/loading.css') }}" rel="stylesheet">
@else
    <link href="{{ asset('/css/reset.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/masta/workers.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/loading.css') }}" rel="stylesheet">
@endif
@endsection
@section('content')
<div id="loading">
    <div class="sk-cube-grid">
        <div class="sk-cube sk-cube1"></div>
        <div class="sk-cube sk-cube2"></div>
        <div class="sk-cube sk-cube3"></div>
        <div class="sk-cube sk-cube4"></div>
        <div class="sk-cube sk-cube5"></div>
        <div class="sk-cube sk-cube6"></div>
        <div class="sk-cube sk-cube7"></div>
        <div class="sk-cube sk-cube8"></div>
        <div class="sk-cube sk-cube9"></div>
    </div>
</div>
<div class="browser_back_area">
    <a href="{{ route('masta') }}"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span></a>
</div>
<!-- メインページ -->
<div class="container">
    <div class="row justify-content-center">
        <div>
            <p class="contents_title" id="contents_title">作業者マスタ</p>
        </div>
        <div class="table_area">
            <a href="{{ route('masta.worker_edit') }}" class="btn btn-primary main_btn">作業者追加</a>
            <div class="table_div">
                <table id="workersTable" class="filtering_table">
                    <thead>
                        <tr>
                            <th class="edit_btn_area"></th>
                            <th id="col1">No.</th>
                            <th>工場</th>
                            <th id="col2">部署</th>
                            <th id="col3">氏名</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($worker as $value)
                            <tr>
                                <td><a href="{{ route('masta.worker_edit',['id' => $value->id]) }}" class=""><button class="btn btn-secondary">編集</button></a></td>
                                <td>{{ $value->id }}</td>
                                <td>{{ $value->factory_name }}</td>
                                <td>{{ $value->department_name }}</td>
                                <td>{{ $value->name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if(config('app.env') === 'production')
    <script src="{{secure_asset('js/masta/worker.js')}}?time="></script>
    <script src="{{secure_asset('js/loading.js')}}"></script>
    <script src="{{secure_asset('js/other_filtering.js')}}" ></script>
@else
    <script src="{{asset('js/masta/worker.js')}}"></script>
    <script src="{{asset('js/loading.js')}}"></script>
    <script src="{{asset('js/other_filtering.js')}}" ></script>
@endif

@endsection