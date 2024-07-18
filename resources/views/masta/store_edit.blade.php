@extends('layouts.app')

@section('css')
@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/masta/store.css') }}" rel="stylesheet">
@else
    <link href="{{ asset('/css/masta/store.css') }}" rel="stylesheet">
@endif
@endsection
@section('content')
<div class="browser_back_area">
    <a href="{{ route('masta.store') }}">
        <img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span>
    </a>
</div>
<!-- メインページ -->
<div class="container">
    <div class="row justify-content-center">

        <div class="worker_upsert">
            <form action="" method="POST">
                @csrf
                @if (isset($data))
                    <h1>ストア編集</h1>
                @else
                    <h1>ストア追加</h1>
                @endif
                <div>
                    <p>工場</p>
                    <select name="factory_id" id="factory_id">
                        <option value="{{ $data->factory_id ?? '' }}" selected>{{ $data->factory_name ?? '--- 選択してください ---' }}</option>
                        @foreach ($factory as $date)
                            {{-- バリデーションに引っかかったときに前回選んだ工場名と同じものがあればselectedを付与 --}}
                            <option value="{{ $date->id }}" {{ $date->id == old('factory_id') ? 'selected' : '' }}>{{ $date->name }}</option>
                        @endforeach
                    </select>
                    @error('factory_id')
                        <div class="ms-3 text-danger">{{ $message }}</div>
                    @enderror

                    <input type="hidden" name="factory_name" id="factory_name" value="">

                    <p>製造課</p>
                    @if (isset($data))
                        <select name="department_id" id="department_id">
                            <option value="{{ $data->department_id }}" {{ $data->department_id == old('department_id') ? 'selected' : '' }}>{{ $data->department_name }}</option>
                        </select>
                    @else
                        <select name="department_id" id="department_id">
                            <option value="" selected>--- 選択してください ---</option>
                        </select>
                    @endif
                    {{-- <select name="department_id" id="department_id">
                        <option value="{{ $data->department_id ?? '' }}" selected>{{ $data->department_name ?? '--- 選択してください ---'  }}</option>
                    </select> --}}
                    @error('department_id')
                        <div class="ms-3 text-danger">{{ $message }}</div>
                    @enderror

                    <input type="hidden" id="department_name" name="department_name" value="{{ $data->department_name ?? ''}}">

                    <p>ストア</p><input type="text" name="store" value="{{ old('store') ?? $data->store ??'' }}">
                     @error('store')
                        <div class="ms-3 text-danger">{{ $message }}</div>
                    @enderror
                </div>
                @if (isset($data))
                    <input type="hidden" value="{{ $data->id }}" name="id" id="id">
                    <input type="submit" class="worker_delete_btn btn btn-danger ms-3" name="submit" value="削除" formaction="{{ route('masta.store_confirm') }}">
                    <input type="submit" class="worker_add_btn btn btn-primary ms-3" name="submit" value="更新" formaction="{{ route('masta.store_confirm') }}">

                @else
                    <input type="submit" class="worker_add_btn btn btn-primary ms-3" name="submit" value="追加" formaction="{{ route('masta.store_confirm') }}">
                @endif
            </form>
        </div>
    </div>
</div>

@if(config('app.env') === 'production')
    <script src="{{secure_asset('js/masta/store.js')}}"></script>
@else
    <script src="{{asset('js/masta/store.js')}}"></script>
@endif

@endsection