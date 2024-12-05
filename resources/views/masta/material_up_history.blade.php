@extends('layouts.app')

@section('css')

@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/masta/upload.css') }}" rel="stylesheet">
@else
    <link href="{{ asset('/css/masta/upload.css') }}" rel="stylesheet">
@endif

@endsection

@section('content')
<div class="browser_back_area">
    <a href="{{ route('masta.upload') }}"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span></a>
</div>
<div class="container">
    <div class="row justify-content-center">
        <div class="table_container">
            <table class="shipping_data">
                <thead>
                    <tr>
                        <th>入荷日</th>
                        <th>品目コード</th>
                        <th>数量</th>
                        <th>シート名</th>
                        <th>備考</th>
                        <th>反映状況</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count = 0; ?>
                    @foreach ($history_arr as $value)
                        <tr>
                            <td>{{ $value["arrival_date"] }}</td>
                            <td>{{ $value["item_code"] }}</td>
                            <td>{{ $value["quantity"] }}</td>
                            <td>{{ $value["factory"] }}</td>
                            <td>{{ $value["note"] }}</td>
                            @if ($value["status"] == "no")
                                <td><span class="text_color">未反映</span></td>
                            @else   
                                <td><span class="text_color_green">反映済み</span></td>
                            @endif
                            <?php $count += 1; ?>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(config('app.env') === 'production')
    <script src=" {{secure_asset('js/masta/upload.js')}}"></script>
@else
    <script src=" {{asset('js/masta/upload.js')}}"></script>
@endif

@endsection