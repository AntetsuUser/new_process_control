@extends('layouts.app')

@section('css')
@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/masta/calendar.css') }}" rel="stylesheet">

@else
    <link href="{{ asset('/css/masta/calendar.css') }}" rel="stylesheet">
@endif
@endsection

@section('content')

<div class="browser_back_area">
    <a href="{{ route('masta') }}"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span></a>
</div>


<div class="container-fluid">
    <div class="select_area">
        <p>休日カレンダー登録</p>
        <label for="">
            <select name="yearsort">
                    <option value="{{ $year[0] }}">{{ $year[0] }}年度</option>
                    <option value="{{ $year[1] }}" selected>{{ $year[1] }}年度</option>
                    <option value="{{ $year[2] }}">{{ $year[2] }}年度</option>
            </select>
        </label>
        <input type="button" value="切り替え" id="year_select_btn">
        <input type="button" value="更新" id="update_btn">
    </div>
    <div class="row">
        <!-- テーブルが入ります -->
        
    </div>
    <form id="calendar_form" action="{{ route('masta.calendar_update') }}" method="POST">
        @csrf <!-- LaravelのCSRFトークンを埋め込む -->
        <input type="hidden" name="holiday" value="">
    </form>
</div>
<script>const holidayData = @json($holidayData);</script>
@if(config('app.env') === 'production')
    <script src=" {{secure_asset('js/masta/calendar.js')}}"></script>
@else
    <script src=" {{asset('js/masta/calendar.js')}}"></script>
@endif
@endsection