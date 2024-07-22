@extends('layouts.app')

@section('css')

@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/qr/input_succes.css') }}" rel="stylesheet">
@else
    <link href="{{ asset('/css/qr/input_succes.css') }}" rel="stylesheet">
@endif

@endsection

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 outer">
            <div class="message">
                <a class="btn btn-border" href="{{ route('qr.qrcamera') }}">QR読取を続ける</a>
                <a class="btn btn-border" href="/">TOPページに戻る</a>
            </div>
        </div>
    </div>
</div>
<script>
    history.pushState(null, null, null);
    window.addEventListener("popstate", function (e) {
        history.pushState(null, null, null);
        return;
    });
</script>


@endsection
