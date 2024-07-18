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

@section('content')
<div class="browser_back_area">
    <a href="{{ route('masta') }}"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span></a>
</div>
<div class="container">
    <div class="row justify-content-center">
        <div class="tabs">
            <input id="all" type="radio" name="tab_item" checked>
            <label class="tab_item" for="all">長期情報</label>
            <input id="shipment" type="radio" name="tab_item">
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
                                <td>{{ $value->category }}</td>
                                <td>{{ $value->file_name }}</td>
                                <td>{{ $value->upload_day }}</td>
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
                    <form id="form_upload_shipping" action="#" method="post" enctype="multipart/form-data">
                        @csrf
                        <!-- アップロード番号 -->
                        <input type="hidden" name="selection_elements" id="selection_elements" value="2">

                        <div class="contents_margin contents">
                            <div class="row form_range">
                                <div class="col-md-10">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="file" name="file" id="shipment_file" value="選択" class="submit_btn">
                                        </div>
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="boxes">
                                                        <input type="checkbox" name="delivery[]" id="delivery1" value="0">
                                                        <label for="delivery1">1便</label>

                                                        <input type="checkbox" name="delivery[]" id="delivery2" value="1">
                                                        <label for="delivery2">2便</label>

                                                        <input type="checkbox" name="delivery[]" id="delivery3" value="2">
                                                        <label for="delivery3">3便</label>

                                                        <input type="checkbox" name="delivery[]" id="delivery4" value="3">
                                                        <label for="delivery4">4便</label>

                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="date" name="delivery_day" id="delivery_day">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

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
                                <th>要求納期</th>
                                <th>便</th>
                                <th>アップロード日時</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i=0; $i < 10; $i++) <tr>
                                <td>　</td>
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