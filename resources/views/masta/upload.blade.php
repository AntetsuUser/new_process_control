@extends('layouts.app')

@section('css')

@if(config('app.env') === 'production')
    {{-- <link href="{{ secure_asset('/css/reset.css') }}" rel="stylesheet"> --}}
    <link href="{{ secure_asset('/css/masta/upload.css') }}" rel="stylesheet">
@else
    {{-- <link href="{{ asset('/css/reset.css') }}" rel="stylesheet"> --}}
    <link href="{{ asset('/css/masta/upload.css') }}" rel="stylesheet">
@endif

@endsection
@php
    // セッションから `tab` を取得し、`null` の場合は `'all'` をデフォルト値として設定
    $currentTab = session('tab', 'all');
@endphp

@section('content')
<div class="browser_back_area">
    <a href="{{ route('masta') }}"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span></a>
</div>
<div class="container">
    <div class="row justify-content-center">
        <div class="tabs">
            <input id="all" type="radio" name="tab_item" {{ $currentTab == 'all' ? 'checked' : '' }}>
            <label class="tab_item" for="all">長期情報</label>
            <input id="shipment" type="radio" name="tab_item" {{ $currentTab == 'shipping' ? 'checked' : '' }}>
            <label class="tab_item" for="shipment">出荷情報アップロード</label>
            <input id="material_ledger" type="radio" name="tab_item">
            <label class="tab_item" for="material_ledger">材料台帳</label>
            <input id="adding_request" type="radio" name="tab_item">
            <label class="tab_item" for="adding_request">追加依頼</label>
            <!-- 長期情報アップロード -->
            <div class="tab_content" id="all_content">
                <div class="tab_content_description">
                    <form id="form_upload" action="{{ route('masta.longinfo_upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="contents_margin contents">
                            @if (session('message_all'))
                                <div class="alert alert-{{ session('message_type') }}">
                                    {{ session('message_all') }}
                                </div>
                            @endif
                            <div class="row form_range">
                                <div class="col-md-10">
                                    <input type="hidden" name="selection_elements" id="selection_elements" value="1">
                                    <input type="file" name="file" value="選択" id="longinfo_file" class="submit_btn" >
                                </div>
                                <div class="col-md-2 btn_class">
                                    <button type="submit" class="add_btn">送信</button>
                                </div>
                                @error('file')
                                    <div class="ms-3 text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </form>
                    <table class="contents_margin">
                        <thead>
                            <tr>
                                <th>ファイル種別</th>
                                <th>ファイル名</th>
                                <th>アップロード日時</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $count = 0; ?>
                            @foreach ($longinfo_log as $value)
                            <tr>
                                <td>{{ $value["category"] }}</td>
                                <td>{{ $value["file_name"] }}</td>
                                <td>{{ $value["upload_day"] }}</td>
                                <?php $count = $count + 1; ?>
                            </tr>
                            @endforeach
                            @if ($count < 10) @for ($i=$count; $i < 10; $i++)
                            <tr>
                                <td>　</td>
                                <td>　</td>
                                <td>　</td>
                            </tr>
                            @endfor
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- 出荷情報アップロード -->
            <div class="tab_content" id="shipment_content">
                <div class="tab_content_description">
                    <form id="form_upload_shipping" action="{{ route('masta.shipping_upload') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <!-- アップロード番号 -->
                        <input type="hidden" name="selection_elements" id="selection_elements" value="2">
                        @if (session('message_shipment'))
                            <div class="alert alert-{{ session('message_type') }}">
                                {{ session('message_shipment') }}
                            </div>
                        @endif
                        <div class="contents_margin contents">
                            <div class="row form_range">
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6 align-items-center file_area">
                                            <input type="file" name="shipment_file" id="shipment_file" value="選択" class="submit_btn">
                                        </div>
                                        <div class="col-md-6 day_area">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="">開始日</label>
                                                    <input type="date" name="delivery_day" id="delivery_day"value="{{ old('delivery_day') }}">

                                                </div>
                                                <div class="col-md-6">
                                                    <label for="">終了日</label>
                                                    <input type="date" name="delivery_day_end" id="delivery_day_end"value="{{ old('delivery_day_end') }}">
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 file_area">
                                    <button type="submit" class="add_btn">アップロード</button>
                                    <button type="button" class="add_btn" onclick="window.location.href='{{ route('masta.clearing_application') }}'">出荷確認</button>
                                </div>

                            </div>
                        </div>
                    </form>
                    @error('shipment_file')
                        <div class="ms-3 text-danger">{{ $message }}</div>
                    @enderror
                    @error('delivery_day')
                        <div class="ms-3 text-danger">{{ $message }}</div>
                    @enderror
                    @error('delivery_day_end')
                        <div class="ms-3 text-danger">{{ $message }}</div>
                    @enderror
                    <table class="contents_margin">
                        <thead>
                            <tr>
                                <th>ファイル種別</th>
                                <th>ファイル名</th>
                                <th>開始日</th>
                                <th>終了日</th>
                                <th>アップロード日時</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $count = 0; ?>
                            @foreach ($shipment_log as $value)
                            <tr>

                                <td>{{ $value["category"] }}</td>
                                <td>{{ $value["file_name"] }}</td>
                                <td>{{ $value["start_date"] }}</td>
                                <td>{{ $value["end_date"] }}</td>
                                <td>{{ $value["upload_day"] }}</td>
                                <?php $count = $count + 1; ?>
                            </tr>
                            @endforeach
                            @if ($count < 10) @for ($i=$count; $i < 10; $i++)
                            <tr>
                                <td>　</td>
                                <td>　</td>
                                <td>　</td>
                                <td>　</td>
                                <td>　</td>
                            </tr>
                            @endfor
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- 材料台帳 -->
            <div class="tab_content" id="material_ledger_content">
                <div class="tab_content_description">
                    <form>
                        <div class="contents_margin contents">
                            <div class="row form_range">
                                <div class="col-md-10">
                                    <input type="file" name="file" id="shipment_file" value="選択" class="submit_btn">
                                </div>
                                <div class="col-md-2 btn_class">
                                    <button type="submit" class="add_btn">送信</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <table class="contents_margin">
                        <thead>
                            <tr>
                                <th>ファイル種別</th>
                                <th>ファイル名</th>
                                <th>アップロード日時</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i=0; $i < 10; $i++) 
                            <tr>
                                <td>　</td>
                                <td>　</td>
                                <td>　</td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- 追加依頼 -->
            <div class="tab_content" id="adding_request_content">
                <div class="tab_content_description">
                    <form id="form_order" action="#" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-3">
                                    <p class="contents_name">品目</p>
                                    <select name="item" id="item" class="lavel item_select" required>
                                        <option value="" disabled selected>--選択してください--</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <div class="contents_name">納期</div>
                                    <input type="date" name="delivery_date" required>
                                </div>
                                <div class="col-md-3">
                                    <div class="contents_name">数量</div>
                                    <input type="number" name="quantity" class="quantity" required>
                                </div>
                                <div class="col-md-3 btn_class">
                                    <button type="submit" class="add_btn form_btn">追加</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <table class="contents_margin">
                        <thead>
                            <tr>
                                <th>追加日時</th>
                                <th>品目</th>
                                <th>納期</th>
                                <th>数量</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i=0; $i < 10; $i++) 
                            <tr>
                                <td>　</td>
                                <td>　</td>
                                <td>　</td>
                                <td>　</td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@if(config('app.env') === 'production')
    <script src=" {{secure_asset('js/masta/calendar.js')}}"></script>
@else
    <script src=" {{asset('js/masta/calendar.js')}}"></script>
@endif

@endsection