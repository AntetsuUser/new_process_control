@extends('layouts.app')

@section('css')
@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/masta/store.css') }}" rel="stylesheet">
@else
    <link href="{{ asset('/css/masta/store.css') }}" rel="stylesheet">
@endif
@endsection
@section('content')
<!-- モーダル部分 -->
<div class="browser_back_area">
    <a href="{{ route('masta') }}"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span></a>
</div>
<!-- メインページ -->
<div class="container">
    <div class="row justify-content-center">
        <div>
            <p class="contents_title" id="contents_title">ストアマスタ</p>
        </div>
        <div class="table_area">
            <a href="{{ route('masta.store_edit') }}" class="btn btn-primary main_btn">ストア追加</a>
            <div class="table_div">
                <table id="workersTable">
                    <thead>
                        <tr>
                            <th class="edit_btn_area"></th>
                            <th id="col1">No.</th>
                            <th>工場</th>
                            <th id="col2">部署</th>
                            <th id="col3">ストア</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($store as $date)
                            <tr>
                                <td><a href="{{ route('masta.store_edit',['id' => $date->id]) }}"><button class="btn btn-secondary">編集</button></a></td>
                                <td>{{ $date->id }}</td>
                                <td>{{ $date->factory_name }}</td>
                                <td>{{ $date->department_name }}</td>
                                <td>{{ $date->store }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if(config('app.env') === 'production')
    {{-- <script src="{{secure_asset('js/masta/worker.js')}}?time="></script> --}}
@else
    {{-- <script src="{{asset('js/masta/worker.js')}}"></script> --}}
@endif

@endsection