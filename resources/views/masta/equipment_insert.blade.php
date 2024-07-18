@extends('layouts.app')

@section('css')

@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/masta/equipment.css') }}" rel="stylesheet">

@else
    <link href="{{ asset('/css/masta/equipment.css') }}" rel="stylesheet">

@endif

@endsection

@section('content')
<div class="browser_back_area">
    <a href="{{ route('masta.equipment') }}"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span></a>
</div>
<div class="container">
    <div class="row justify-content-center">

        <div class="upsert">
            <form action="" method="POST">
                @csrf
                <div class="edit_title">
                @if (isset($data))
                    <h1>設備編集</h1>
                @else
                    <h1>設備追加</h1>
                @endif
                </div>
                <div>
                    <div class="flex_area">
                        <div class="input_area">
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
                        </div>
                        <div class="input_area">
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
                            @error('department_id')
                                <div class="ms-3 text-danger">{{ $message }}</div>
                            @enderror
                            <input type="hidden" id="department_name" name="department_name" value="{{ $data->department_name ?? ''}}">
                        </div>
                    </div>
                    <div class="flex_area">
                        <div class="input_area">
                            <p>ライン</p><input type="text" name="line" value="{{ old('line') ?? $data->line ??'' }}" class="text_area">
                            @error('line')
                                <div class="ms-3 text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="input_area">
                            <p>設備No</p><input type="text" name="equipment_id" value="{{ old('equipment_id') ?? $data->equipment_id ??'' }}" class="text_area">
                            @error('equipment_id')
                                <div class="ms-3 text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="flex_area">
                        <div class="input_area">
                            <p>区分</p><input type="text" name="category" value="{{ old('category') ?? $data->category ??'' }}" class="text_area">
                            @error('category')
                                <div class="ms-3 text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="input_area">
                             <p>型式</p><input type="text" name="model" value="{{ old('model') ?? $data->model ??'' }}" class="text_area">
                             @error('model')
                                <div class="ms-3 text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                @if (isset($data))
                    <input type="hidden" value="{{ $data->id }}" name="id" id="id">
                    <div class="btn_flex">
                        <input type="submit" class="worker_delete_btn btn btn-danger ms-3" name="submit" value="削除" formaction="{{ route('masta.equipment_confirm') }}">
                        <input type="submit" class="worker_add_btn btn btn-primary ms-3" name="submit" value="更新" formaction="{{ route('masta.equipment_confirm') }}">
                    </div>
                @else
                    <div class="add_btn">
                        <input type="submit" class="add_btn btn btn-primary ms-3" name="submit" value="追加" formaction="{{ route('masta.equipment_confirm') }}">
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

@if(config('app.env') === 'production')
    <script src="{{secure_asset('js/masta/equipment.js')}}"></script>
@else
    <script src="{{asset('js/masta/equipment.js')}}"></script>
@endif
@endsection
