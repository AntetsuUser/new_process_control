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
                        <th>品目コード</th>
                        <th>品目名称</th>
                        <th>要求納期</th>
                        <th>数量</th>
                        <th>備考</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count = 0; ?>
                    @foreach ($history_arr as $value)
                        <tr>
                            <td>{{ $value["item_code"] }}</td>
                            <td>{{ $value["item_name"] }}</td>
                            <td>{{ $value["delivery_date"] }}</td>
                            <td>{{ $value["ordering_quantity"] }}</td>
                            <td>{{ $value["note"] }}</td>
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