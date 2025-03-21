@extends('layouts.app')

@section('css')

@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/signage/tablet.css') }}" rel="stylesheet">
@else
    <link href="{{ asset('/css/signage/tablet.css') }}" rel="stylesheet">
@endif

@endsection

@section('content')

<div class="browser_back_area">
    <a href="{{ route('signage.tablet') }}"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span></a>
</div>

<div class="wrapper_div">
    <div class="wrapper">
    <div class="circle"></div>
    <div class="circle"></div>
    <div class="circle"></div>
    <div class="shadow"></div>
    <div class="shadow"></div>
    <div class="shadow"></div>
    <span>Loading</span>
</div>

</div>

<div class="filtering_area">
    <input id="production" type="hidden" name="production" value="{{ $production }}">
    <div id="item_checkbox_area" class="checkbox_area">
        <input type="text" name="select_item_name" id="select_item_name" value="すべて" readonly placeholder="品目を選択してください"> <!-- readonly属性を追加 -->
        <div class="check_box" style="display: none;">
            <label>
                <input type="checkbox" name="all" value="すべて" class="item-checkbox" checked/>
                <span>すべて</span>
            </label>
            @foreach ($item_names as $name)
            <label>
                <input type="checkbox" name="{{ $name }}" value="{{ $name }}" class="item-checkbox"/>
                <span>{{ $name }}</span>
            </label>
            @endforeach
        </div>
    </div>

    <div id="process_checkbox_area" class="checkbox_area">
        <input type="text" name="select_process" id="select_process" value="すべて" readonly placeholder="工程を選択してください"> <!-- readonly属性を追加 -->
        <div class="check_box" style="display: none;">
            <label>
                <input type="checkbox" name="all" value="すべて" class="process-checkbox" checked/>
                <span>すべて</span>
            </label>
            @foreach ($process_arr as $process)
                <label>
                <input type="checkbox" name="{{ $process }}" value="{{ $process }}" class="process-checkbox"/>
                <span>{{ $process }}</span>
            </label>
            @endforeach
        </div>
    </div>
    {{-- 長期順と納期順の変更ボタン --}}
    <div class="sample">
        <input type="radio" name="s2" id="on" value="1" checked="">
        <label for="on" class="switch-on">長期順</label>
        <input type="radio" name="s2" id="off" value="0">
        <label for="off" class="switch-off">納期順</label>
    </div>

   <button class="button_4" id="update_button">更新</button>

</div>
<div class="table_area">
    <table id="my_table">
        <thead>
            <tr>
                <th rowspan="2">品目コード</th>
                <th rowspan="2">工程</th>
                <th rowspan="2">　</th>
                @foreach ($dateArray as $index => $value)
                @if($value == "遅延")
                    <th class="fixed_size" id="" colspan="2" rowspan="2">{{ $value }}</th>
                @else
                    <th class="fixed_size" id="{{$date[$index-1] }}" colspan="2">{{ $value }}</th>
                @endif
                @endforeach
            </tr>
            <tr>
                @foreach ($weekdayArray as $value)
                    @if($value == "")

                    @else
                        <th colspan="2">{{ $value }}</th>
                    @endif
                @endforeach
            </tr>
        </thead>
        <tbody id="dataBody">

        </tbody>
    </table>
</div>
<div hidden id="data-container" data-data="{{ json_encode($work_arr) }}"></div>

{{-- jsにajaxで使うパスを渡す --}}
<script>var logRoute = "{{ route('signage.ajax') }}";</script>
{{-- jsにajaxで使うパスを渡す --}}
<script> var dateArray = @json($dateArray);</script>
@if(config('app.env') === 'production')
    <script src={{secure_asset('js/signge/tablet.js')}}></script>
@else
    <script src={{asset('js/signge/tablet.js')}}></script>
    {{-- /home/pi/Desktop/process_control/public/js/local_ip.js --}}
@endif

@endsection
