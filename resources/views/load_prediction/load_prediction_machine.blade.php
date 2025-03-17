@extends('layouts.app')

@php
    use Carbon\Carbon;
@endphp
@section('css')
@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/load_prediction/load_prediction_graph.css') }}" rel="stylesheet">
@else

    <link href="{{ asset('/css/load_prediction/load_prediction_graph.css') }}" rel="stylesheet">
@endif
@endsection

@section('content')
    
    <div class="browser_back_area">
        <a href="{{ route('load_prediction.department_select') }}"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span></a>
    </div>
    <div class="container">
            @if (session('message'))
                <div class="alert alert-warning">
                    {!! nl2br(e(session('message'))) !!}
                </div>
            @endif
        <div class="row justify-content-center">
            <div class="table_area">
                <table class="machines_table" id="load_prediction_table">
                    <thead class="table_sticky">
                        <div id="department_id" hidden>{{ $department_id }}</div>
                        <tr>
                            <th rowspan="2"  class="th1">W/C</th>
                            @foreach ($days as $day)
                            {{-- //$new_weeks[count($new_weeks)] --}}
                                <th class="th1">{{ Carbon::parse($day[0])->format('n月j日') }}～{{ Carbon::parse($day[count($day)-1])->format('n月j日')}}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($days as $day)
                                <th class="th2">稼働日数：{{ count($day) }}</th>
                            @endforeach
                        </tr>

                    </thead>
                    <tbody>
                        @foreach ($machines as $machine_number => $value)
                            <tr>
                                <td class="machine line_no">{{ $machine_number }}</td>
                                @foreach ($value as $item)
                                    <td class="machine">{{ $item }}</td>
                                @endforeach
                            </tr>

                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        var test = <?php echo json_encode($days, JSON_UNESCAPED_UNICODE); ?>;
        var days = JSON.stringify(test, null, 2);
    </script>

    
    @if(config('app.env') === 'production')
        <script src="{{secure_asset('js/load_prediction/load_prediction.js')}}"></script>
    @else
        <script src="{{asset('js/load_prediction/load_prediction.js')}}"></script>
    @endif
@endsection