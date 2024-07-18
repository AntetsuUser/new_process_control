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
    <a href="#" onclick="history.back(-1);return false;"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span></a>
</div>
<div class="container">
    <div class="row justify-content-center">
        <div class="confirm">
            <div class="confirm_title">
                @if (isset($id))
                    @if ($data['submit'] == "更新")
                        <h1>以下の内容で編集しますか</h1>
                    @else   
                        <h1>以下の内容を削除しますか</h1>
                    @endif
                @else
                    <h1 >以下の内容で追加しますか</h1>
                @endif
            </div>
            <div class="">
                <div class="flex">
                    <p class="confirm_contents_title">工場</p>
                    <div class="confirm_item">{{ $data['factory_name'] }}</div>
                </div>
                <div class="flex">
                    <p class="confirm_contents_title">製造課</p>
                    <div class="confirm_item">{{ $data['department_name'] }}</div>
                </div>
                <div class="flex">
                    <p class="confirm_contents_title">ライン</p>
                    <div class="confirm_item">{{ $data['line'] }}</div>
                </div>
                <div class="flex">
                    <p class="confirm_contents_title">設備No</p>
                    <div class="confirm_item">{{ $data['equipment_id'] }}</div>
                </div>
                <div class="flex">
                    <p class="confirm_contents_title">区分</p>
                    <div class="confirm_item">{{ $data['category'] }}</div>
                </div>
                <div class="flex">
                    <p class="confirm_contents_title">型式</p>
                    <div class="confirm_item">{{ $data['model'] }}</div>
                </div>
            </div>
            <form action="#" method="POST">
                @csrf
                <input type="hidden" name="factory_id" value="{{ $data['factory_id'] }}">
                <input type="hidden" name="department_id" value="{{ $data['department_id'] }}">
                <input type="hidden" name="line" value="{{ $data['line'] }}">
                <input type="hidden" name="equipment_id" value="{{ $data['equipment_id'] }}">
                <input type="hidden" name="category" value="{{ $data['category'] }}">
                <input type="hidden" name="model" value="{{ $data['model'] }}">
                @if(isset($id))
                    <input type="hidden" name="id" value="{{ $id }}">   
                    @if ($data['submit'] == "更新")
                        <div class="add_btn">
                            <input type="submit" class="btn btn-primary ms-3" value="更新" formaction="{{ route('masta.equipment_store') }}">
                        </div>
                    @else   
                        <div class="add_btn">
                            <input type="submit" class="btn btn-danger ms-3" value="削除" formaction="{{ route('masta.equipment_delete') }}">
                        </div>
                    @endif
                @else
                <div class="add_btn">
                    <input type="submit" class="worker_confirm_btn btn btn-primary ms-3" value="追加" formaction="{{ route('masta.equipment_store') }}">
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
