@extends('layouts.app')

@section('css')
@if(config('app.env') === 'production')
    {{-- <link href="{{ secure_asset('/css/reset.css') }}" rel="stylesheet"> --}}
    <link href="{{ secure_asset('/css/masta/workers.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('/css/loading.css') }}" rel="stylesheet">
@else
    {{-- <link href="{{ asset('/css/reset.css') }}" rel="stylesheet"> --}}
    <link href="{{ asset('/css/masta/workers.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/loading.css') }}" rel="stylesheet">
@endif
@endsection
@section('content')

<div class="browser_back_area">
    <a href="#" onclick="history.back(-1);return false;">
        <img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt="" >
        <span>戻る</span>
    </a>
</div>
<!-- メインページ -->
<div class="container">
    <div class="row justify-content-center">

        <div class="worker_upsert">
            <form action="{{ route('masta.worker_store') }}" method="POST">
                @csrf

                @if (isset($id))
                    <h1>以下の内容で編集しますか？</h1>
                @else
                    <h1>以下の内容で追加しますか？</h1>
                @endif

                <div class="form_confirm">
                    <div class="worker_factory_gloup">
                        <span class="worker_factory">工場</span>
                        <span class="factory_name">{{ $data['factory_name'] }}</span>
                    </div>

                    <div class="worker_department_gloup">
                        <span class="worker_department">部署</span>
                        <span class="department_name">{{ $data['department_name'] }}</span>
                    </div>

                    <div class="worker_name_gloup">
                        <span class="worker_name">名前</span>
                        <span class="w_name">{{ $data['family_name'] }}{{ $data['personal_name'] }}</span>
                    </div>
                </div>

                <input type="hidden" name="factory_id" id="factory_id" value="{{ $data['factory_id'] }}">
                <input type="hidden" name="department_id" id="department_id" value="{{ $data['department_id'] }}">
                <input type="hidden" name="name" id="name" value="{{ $data['family_name'] . "　" . $data['personal_name'] }}">

                @if(isset($id))
                    <input type="hidden" name="id" value="{{ $id }}">   

                    <input type="submit" class="btn btn-primary ms-3" value="更新">
                @else
                    <input type="submit" class="worker_confirm_btn btn btn-primary ms-3" value="追加">
                @endif

            </form>
        </div>
    </div>
</div>

@if(config('app.env') === 'production')
    <script src="{{secure_asset('js/masta/worker.js')}}?time="></script>
    <script src="{{secure_asset('js/loading.js')}}"></script>
@else
    <script src="{{asset('js/masta/worker.js')}}"></script>
    <script src="{{asset('js/loading.js')}}"></script>
@endif

@endsection