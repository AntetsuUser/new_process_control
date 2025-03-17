@extends('layouts.app')

@section('css')
@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/signage/signage.css') }}" rel="stylesheet">
@else
    <link href="{{ asset('/css/signage/signage.css') }}" rel="stylesheet">
@endif
@endsection

<body>
    {{-- <input type="hidden" name="production_section" value="{{ $ip_address }}"> --}}
    <input type="hidden" name="production_section" value="{{ $production }}">
    <div class="table_area">
        <table id="my_table">
            <thead>
                <tr>
                    <th rowspan="2">品目コード</th>
                    <th rowspan="2">工程</th>
                    <th rowspan="2">
                        <form id="ip_form" action="{{ route('signage.main') }}" method="GET" class="main_btn ip_form">
                            @csrf
                            <input type="hidden" name="division" value="{{ $production }}">
                            <input type="submit" value="更新" class="reload_btn">
                        </form>

                    </th>
                    
                    @foreach ($dateArray as $index => $value)
                    @if($value == "遅延")
                        <th colspan="2" rowspan="2" class="th_text" id="{{ $info_day[$index] }}">{{ $value }}</th>
                    @else
                        <th colspan="2" class="th_text" id="{{ $info_day[$index] }}">{{ $value }}</th>
                    @endif
                    @endforeach
                </tr>
                <tr>
                    @foreach ($weekdayArray as $value)
                        @if($value == "")

                        @else
                            <th colspan="2" class="th_text">{{ $value }}</th>
                        @endif
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $key => $value)
                {{-- 素材在庫 --}}
                    <tr>
                        <td rowspan="2"  class="blue_line black_line item_name">{{ $key }}</td>
                        <td class="blue_line">　</td>
                        {{-- material_stock_1 --}}
                        <td class="blue_line">102材料在庫：{{ $material_stock[$key]["102"] }}</td>
                        @foreach ($dateArray as $index => $value)
                            <td class="blue_line {{ $info_day[$index] }} cellend">{{ $material_mark[$key]["102"][$index] }}</td>
                            <td class="blue_line {{ $info_day[$index] }} ">{{ $material_mark[$key]["103"][$index] }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="black_line">　</td>
                        <td class="black_line">103材料在庫：{{ $material_stock[$key]["103"] }}</td>
                        @foreach ($dateArray as $index => $date)
                            @if (isset($items[$key]["target"][$index]))
                                @if($items[$key]["target"][$index] == 0)
                                    <td colspan="2" class="black_line {{ $info_day[$index] }} cellend">　</td>
                                @else
                                    <td colspan="2" class="black_line {{ $info_day[$index] }} cellend">{{ $items[$key]["target"][$index] }}</td>
                                @endif
                            @endif
                        @endforeach
                    </tr>
                    {{-- 工程 --}}
                    @foreach ($display_arr[$key] as $process => $process_name)
                        <tr>
                            <td class="">　</td>
                            <td class="">{{ $process_name }}</td>
                            <td class="">在庫：{{ $process_names[$key][$process] }}</td>
                            @foreach ($dateArray as $index => $date)
                            @if (isset($items[$key][$process][$index]))
                                @if($items[$key][$process][$index] == 0)
                                    <td colspan="2" class="{{ $info_day[$index] }} cellend">　</td>
                                @else
                                    <td colspan="2" class="{{ $info_day[$index] }} cellend">残{{ $items[$key][$process][$index] }}</td>
                                @endif
                            @endif
                        @endforeach
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    @if(config('app.env') === 'production')
        <script src={{secure_asset('js/signge/signge.js')}}></script>

    @else
        <script src={{asset('js/signge/signge.js')}}></script>
        {{-- /home/pi/Desktop/process_control/public/js/local_ip.js --}}
    @endif
</body>