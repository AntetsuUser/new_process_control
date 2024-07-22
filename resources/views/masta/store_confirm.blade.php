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
    <a href="#" onclick="history.back(-1);return false;">
        <img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt="" ><span>戻る</span>
    </a>
</div>
<!-- メインページ -->
<div class="container">
    <div class="row justify-content-center">
        <div class="confirm">
            @if ($data["submit"] == "追加")
                <h1>以下の内容で追加しますか？</h1>
            @elseif ($data["submit"] == "更新")
                <h1>以下の内容で更新しますか？</h1>
            @else
                <h1>以下の内容を削除しますか？</h1>
            @endif
            <div class="confirm_contents">
                <div class="flex">
                    <p>工場</p>
                    <div class="confirm_item">{{ $data["factory_name"] }}</div>
                </div>
                <div class="flex">
                    <p>製造課</p>
                    <div class="confirm_item">{{ $data["department_name"] }}</div>
                </div>
                <div class="flex">
                    <p>ストア</p>
                    <div class="confirm_item">{{ $data["store"] }}</div>
                </div>
                <form action="">
                    @csrf
                    <input type="hidden" name="factory_id" value="{{ $data["factory_id"] }}">
                    <input type="hidden" name="department_id" value="{{ $data["department_id"] }}">
                    <input type="hidden" name="store" value="{{ $data["store"] }}">
                    <input type="hidden" name="id" value="{{ $id}}">
                    @if ($data["submit"] == "追加")
                    <div class="add_btn">
                        <input type="submit" value="登録" class="btn btn-primary ms-3" formaction="{{ route('masta.store_upsert') }}" formmethod="POST">
                    </div>
                    @elseif ($data["submit"] == "更新")
                    <div class="add_btn">
                        <input type="submit" value="更新" class="btn btn-primary ms-3" formaction="{{ route('masta.store_upsert') }}" formmethod="POST">
                    </div>
                    @else
                    <div class="add_btn">
                        <input type="submit" value="削除" class="btn btn-danger ms-3" formaction="{{ route('masta.store_delete') }}" formmethod="POST">
                    </div>
                    @endif
                </form> 
            </div>
        </div>
    </div>
</div>
@endsection