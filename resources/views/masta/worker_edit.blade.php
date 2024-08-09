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
{{-- <div id="loading">
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
</div> --}}
<!-- モーダル部分 -->
<div class="browser_back_area">
    <a href="{{ route('masta.worker') }}"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span></a>
</div>
<!-- メインページ -->
<div class="container">
    <div class="row justify-content-center">

        <div class="worker_upsert">
            <form action="" method="POST">
                @csrf

                @if (isset($data))
                <h1>作業者編集</h1>
                @else
                    <h1>作業者追加</h1>
                @endif 
                <div>
                    <p>工場</p>
                    <select name="factory_id" id="factory_id">
                        <option value="{{ $data->factory_id ?? '' }}" selected>{{ $data->factory_name ?? '--- 選択してください ---' }}</option>
                        @foreach ($factory as $value)
                            {{-- バリデーションに引っかかったときに前回選んだ工場名と同じものがあればselectedを付与 --}}
                            <option value="{{ $value->id }}" {{ $value->id == old('factory_id') ? 'selected' : '' }}>{{ $value->name }}</option>
                        @endforeach
                    </select>
                    @error('factory_id')
                        <div class="ms-3 text-danger">{{ $message }}</div>
                    @enderror

                    {{-- 選択した工場名をjQueryでvalueに入れる --}}
                    <input type="hidden" value="" name="factory_name" id="factory_name">

                    <p>部署</p>
                    @if (isset($data))
                        <select name="department_id" id="department_id">
                            <option value="{{ $data->department_id }}" {{ $data->department_id == old('department_id') ? 'selected' : '' }}>{{ $data->department_name }}</option>
                        </select>
                    @else
                        <select name="department_id" id="department_id">
                            <option value="" selected>--- 選択してください ---</option>
                        </select>
                    @endif
                    @error('department_id')
                        <div class="ms-3 text-danger">{{ $message }}</div>
                    @enderror

                    {{-- 選択した部署名をjQueryでvalueに入れる --}}
                    <input type="hidden" value="" name="department_name" id="department_name">

                    @php
                        if (isset($data))
                        {
                            $worker_name = $data->name;

                            // 空白を基準に苗字と名前を分割
                            $name_arr = explode("　", $worker_name);

                            $family_name = $name_arr[0];
                            $personal_name = $name_arr[1];
                        }
                        else 
                        {
                            // 新規追加の場合は名前がないので空白を指定
                            $family_name = "";
                            $personal_name = "";
                        }
                    @endphp

                    <div class="worker_name_gloup">
                        <div class="family_gloup">
                            <p>苗字</p>

                            {{-- old()に値がなかったら$family_nameが入る --}}
                            <input type="text" class="family_name" name="family_name" id="family_name" value="{{ old('family_name', $family_name) }}">

                            {{-- バリデーションの設定は app/Http/Requests/Masta/WorkerEditRequest.php --}}
                            @error('family_name')
                                <div class="ms-3 text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="personal_gloup">
                            <p>名前</p>
                            <input type="text" class="personal_name" name="personal_name" id="personal_name" value="{{ old('personal_name', $personal_name) }}">

                            @error('personal_name')
                                <div class="ms-3 text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="">
                        @if(isset($data))
                            <input type="submit" class="worker_delete_btn btn btn-danger ms-3" value="削除" formaction="{{ route('masta.worker_delete') }}">

                            <input type="submit" class="worker_update_btn btn btn-primary ms-3" value="更新" formaction="{{ route('masta.worker_edit_confirm') }}">

                            {{-- 更新する場合は値をinputに設定してformに送信 --}}
                            <input type="hidden" value="{{ $data->id }}" name="id" id="id">

                            {{-- <input type="hidden" value="{{ $data->factory_name }}" name="factory_name" id="factory_name">
                            <input type="hidden" value="{{ $data->department_name }}" name="department_name" id="department_name">
                            <input type="hidden" value="{{ $data->factory_id }}" name="factory_id" id="factory_id">
                            <input type="hidden" value="{{ $data->department_id }}" name="department_id" id="department_id"> --}}
                        @else
                            <input type="submit" class="worker_add_btn btn btn-primary ms-3" value="追加" formaction="{{ route('masta.worker_edit_confirm') }}">
                        @endif
                    </div>
                </div>
                
            </form>
        </div>
    </div>
</div>

@if(config('app.env') === 'production')
    <script src="{{secure_asset('js/masta/worker.js')}}"></script>
@else
    <script src="{{asset('js/masta/worker.js')}}"></script>
@endif

@endsection