@extends('layouts.app')

@section('css')
    @if (config('app.env') === 'production')
        <link href="{{ secure_asset('/css/masta/tablet.css') }}" rel="stylesheet">
    @else
        <link href="{{ asset('/css/masta/tablet.css') }}" rel="stylesheet">
    @endif
@endsection
@section('content')
    <!-- モーダル部分 -->
    <!-- オーバーレイ -->
    <div id="overlay" class="overlay"></div>
    <!-- モーダルウィンドウ -->
    <div class="modal-window">
        <form action="">
            <p>機械番号</p>
            <input type="text" name="" id="" placeholder="3桁の数字を入力してください　例：001">
            <div class="flex-container">
                <div class="half-width">
                    <p>工場</p>
                    <select name="factory_id" id="factory_id">
                        <option value="" disabled selected>選択してください</option>
                        @foreach ($factory as $item)
                        {{-- バリデーションに引っかかったときに前回選んだ工場名と同じものがあればselectedを付与 --}}
                        <option value="{{ $item->id }}" {{ $item->id == old('factory_id') ? 'selected' : '' }}>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="half-width">
                    <p>部署</p>
                    <select name="department_id" id="department_id">
                        <option value="" disabled selected>選択してください</option>
                    </select>
                </div>
            </div>
            <p>対応品番</p>
            <input type="text" name="" id="">

            <div class="btn_field">
                <!-- 追加ボタン -->
                <input type="submit" class="js-close button-close button_add" name="submit" value="追加" formaction="{{ route('masta.equipment_confirm') }}">
                <!-- 閉じるボタン -->
                <button class="js-close button-close">キャンセル</button>
            </div>
        </form>
    </div>

    <div class="browser_back_area">
        <a href="{{ route('masta') }}"><img class="back_btn" src="{{ asset('img/icon/back.png') }}"
                alt=""><span>戻る</span></a>
    </div>
    <!-- メインページ -->
    <div class="container">
        <div class="row justify-content-center">
            <div>
                <p class="contents_title" id="contents_title">タブレット一覧</p>
            </div>

            <div class="table_area">
                <a href="#" class="btn btn-primary main_btn js-open button-open">追加</a>

                <div class="table_div">
                    <table id="workersTable">
                        <thead>
                            <tr>
                                <th id="col1">機械番号</th>
                                <th>工場</th>
                                <th id="col2">部署</th>
                                <th id="col3">対応品番</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($masta_data as $key => $item)
                                <tr>
                                    <td>{{ $item->tablet_number }}</td>
                                    <td>{{ $item->factory_name }}</td>
                                    <td>{{ $item->department_name }}</td>
                                    <td>{{ $item->supported_item }}</td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(function() {
            $('.js-open').click(function() {
                $('#overlay, .modal-window').fadeIn();
            });
            $('.js-close').click(function() {
                $('#overlay, .modal-window').fadeOut();
            });
        });
    </script>

@if(config('app.env') === 'production')
    <script src="{{secure_asset('js/masta/tablet.js')}}"></script>
@else
    <script src="{{asset('js/masta/tablet.js')}}"></script>
@endif
@endsection
