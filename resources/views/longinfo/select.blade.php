@extends('layouts.app')

@section('css')

@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/reset.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('/css/longinfos/div_select.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('/css/loading.css') }}" rel="stylesheet">
@else
    <link href="{{ asset('/css/reset.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/longinfos/div_select.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/loading.css') }}" rel="stylesheet">
@endif

@endsection

@section('content')

<div class="browser_back_area">
    <a href="{{ url('/') }}"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span></a>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="main_contents">
            <div class="main_box">
                <div class="sub_box">
                    <form action="{{ route('longinfo.view') }}" method="POST">
                        @csrf
                        <p>工場</p>
                        <select name="factory" id="factory" class="lavel input_color" >
                            <option value="{{ old('factory') ?? '' }}" disabled selected>{{ old('factory') ?? '--- 選択してください ---' }}</option>
                            @foreach ($factory as  $data)
                                <option value="{{ $data->id }}">{{ $data->name }}</option>
                            @endforeach
                        </select>
                        @error('factory')
                            <div class="ms-3 text-danger">{{ $message }}</div>
                        @enderror

                        <p>製造課</p>
                        <select name="department" id="department" class="lavel input_color" >
                            <option value="{{ old('department') ?? '' }}" disabled selected>{{ old('department') ?? '--- 選択してください ---' }}</option>
                        </select>
                        @error('department')
                            <div class="ms-3 text-danger">{{ $message }}</div>
                        @enderror

                        <p>W/C</p>
                        <select name="line" id="line"  class="input_color" >
                            <option value="{{ old('line') ?? '' }}" disabled selected>{{ old('line') ?? '--- 選択してください ---' }}</option>
                        </select>
                        <span style="font-size: 25px; vertical-align: middle;">ー</span>
                        <select name="numbers" id="numbers" class="input_color" >
                            <option value="{{ old('numbers') ?? '' }}" disabled selected>{{ old('numbers') ?? '0' }}</option>
                        </select>
                        @error('line')
                            <div class="ms-3 text-danger">{{ $message }}</div>
                        @enderror
                        @error('numbers')
                            <div class="ms-3 text-danger">{{ $message }}</div>
                        @enderror

                        <p>作業者</p>
                        <select name="workers" id="workers" class="lavel input_color" required>
                            <option value="{{ old('workers') ?? '' }}" disabled selected>{{ old('workers') ?? '--- 選択してください ---' }}</option>
                        </select>
                        @error('workers')
                            <div class="ms-3 text-danger">{{ $message }}</div>
                        @enderror
                        <button class="btn trans_btn submit_btn">長期情報表示</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>    

@if(config('app.env') === 'production')
    <script src="{{secure_asset('js/longinfos/longinfo_select.js')}}" ></script>

@else
    <script src="{{asset('js/longinfos/longinfo_select.js')}}"></script>

@endif

@endsection