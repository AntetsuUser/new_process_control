@extends('layouts.app')

@section('css')

@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/longinfos/longinfo_view.css') }}" rel="stylesheet">
@else
    <link href="{{ asset('/css/longinfos/longinfo_view.css') }}" rel="stylesheet">
@endif

@endsection

@section('content')


<div class="browser_back_area container-fluid">
    <div class="row">
        <div class="col-md-1">
            <a href="{{ route('longinfo.select') }}"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span></a>
        </div>
        <div class="col-md-1">
            <button id="print" class="btn main_btn print">印刷</button>

        </div>
        <div class="col-md-1">
            <button id="help_btn" class="btn">色の説明</button>

        </div>
    </div>
</div>

{{-- 印刷ボタン --}}
{{-- 長期表示 --}}
<div class="container-fluid">
    <input type="hidden" name="local_ip" id="local_ip" value="">
    <div class="row">
        <!-- モーダルウィンドウ【数量選択画面】 -->
        <div id="easyModal" class="modal">
            <div class="modal-content">
                <!-- 数量選択 -->
                <div class="modal-header">
                    <p class="modal_title">数量選択</p>
                </div>
                <div class="modal-body">
                    <table id="form_tbl">
                        <tr>
                            <td><label >長期数量：</label></td>
                            <td>
                                <p id="info_num"></p>
                            </td>
                        </tr>
                        <tr>
                            <td><label >ロット数：</label></td>
                            <td>
                                <p id="lot_number"></p>
                            </td>
                        </tr>
                        <tr>
                            <td><label>印刷数量：</label><button id="max_btn" class="btn btn-primary ">MAX</button></td>
                            <td><input type="number" id="num_decision" class="modal_text" value="" placeholder="0" oninput="value = value.replace(/[^0-9]+/i,'');" /></td>
                        </tr>
                    </table>

                    <div class="btn_area">
                        <input id="decision_btn" type="button" class="btn btn-primary decision_btn" onclick="" value="決定">
                        <input id="cancel_btn" type="button" class=" btn btn-primary decision_btn" onclick="" value="取り消し">
                        <input id="close_btn" type="button" class=" btn btn-primary decision_btn" value="キャンセル">
                    </div>
                </div>
            </div>
        </div>
        <div class="help_modal-container">
            <div class="help_modal-body">
                <!-- 閉じるボタン -->
                <div class="help_modal-close">×</div>
                <!-- モーダル内のコンテンツ -->
                <div class="help_modal-content">
                    <h1 class="help_title">色の役割</h1>
                    <div class="flex"><p>選択可能なセル</p><div class="color_box set_cel">残12</div></div>
                    <div class="flex"><p>リードタイム</p><div class="color_box read_time">残12</div></div>
                    <div class="flex"><p>負荷予測</p><div class="color_box read_time today_task">残12</div></div>
                    <div class="flex"><p>数量選択時</p><div class="color_box selected">残12</div></div>
                    <div class="flex"><p>現在作業中</p><div class="color_box in_work">残12</div></div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="scrolltable">
                {{-- 設備番号と作業者を非表示で表示 --}}
                <input type="hidden" name="line_numbers" value="{{ $line_numbers }}">
                <input type="hidden" name="workers" value="{{ $workers }}">
                <input type="hidden" name="line"  value="{{ $line }}">
                <input type="hidden" name="numbers"  value="{{ $numbers }}">
                <input type="hidden" name="factory"  value="{{ $factory }}">
                <input type="hidden" name="department"  value="{{ $department }}">
                
                <table id="info_table">
                    <thead>
                        <tr>
                            <th class="col1 " rowspan="2">品目コード</th>
                            <th class="col2" rowspan="2">工程</th>
                            <th class="col3 right_black" rowspan="2"></th>
                            <th class="" rowspan="2" colspan="2">遅延</th>
                            {{-- 日付のループ --}}
                            @foreach ($date_arr[1] as $key => $date_item)
                                <th id="{{ $date_arr[0][$key] }}" class="info_row" colspan="2">{{ $date_item }}</th>
                            @endforeach
                        </tr>
                        <tr>
                            {{-- 曜日のループ --}}
                            @foreach ($date_arr[2] as $date_item)
                                <th class="info_row" colspan="2">{{ $date_item }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    
                    <tbody>
                        {{-- {{ dd($material_mark_arr); }} --}}
                        @foreach ($info_process_arr as $key => $process)
                            {{-- 基本情報 --}}
                            <tr>
                                <td class="bottom_black info_backcolor info_item" rowspan="2">{{ $key }}</td>
                                <td class="info_backcolor"></td>
                                <td class="right_black info_backcolor">102素材在庫:{{ $material_arr[$key][0] }}</td>
                                @for ($i = 0; $i <= count($date_arr[0]); $i++)
                                    <td class="info_backcolor mark_area right_border"><div class="mark_icon">{{ $material_mark_arr["102"][$key][$i] }}</div></td>
                                    <td class="info_backcolor mark_area "><div class="mark_icon">{{ $material_mark_arr["103"][$key][$i] }}</div></td>
                                @endfor
                            </tr>
                            <tr>
                                <td class="bottom_black info_backcolor" hidden></td>
                                <td class="bottom_black info_backcolor"></td>
                                <td class="bottom_black right_black info_backcolor">103素材在庫:{{ $material_arr[$key][1] }}</td>
                                @for ($i = 0; $i <= count($date_arr[0]); $i++)
                                
                                    @if($quantity_arr[$key][0][$i] === "" or $quantity_arr[$key][0][$i] === 0)
                                        <td class="bottom_black info_backcolor" colspan="2"></td>
                                    @else
                                        <td class="bottom_black info_backcolor  info_count" colspan="2">{{ $quantity_arr[$key][0][$i] }}</td>
                                    @endif
                                @endfor
                            </tr>
                            {{-- 工程 --}}
                            @foreach ($process as $process_key => $data)
                                <tr>
                                    @if ($loop->last)
                                        <td class="top_blue"><p hidden>0/{{ $lot_arr[$key][$process_key] }}</p></td>
                                        <td class="top_blue">{{ $data }}</td>
                                        <td class="top_blue right_black">完{{ preg_replace('/\d+/', '', $data) }}在庫：{{ $stock_arr[$key]["process"][$process_key] }}</td>
                                        @for ($i = 0; $i <= count($date_arr[0]); $i++)
                                            @if($quantity_arr[$key][$process_key+1][$i] === "" or $quantity_arr[$key][$process_key+1][$i] === 0)
                                                <td colspan="2" class="tap_day main_row top_blue"></td>
                                            @else
                                                <td colspan="2" class="tap_day main_row top_blue">残{{$quantity_arr[$key][$process_key+1][$i]}}</td>
                                            @endif
                                        @endfor
                                    @else
                                        <td><p hidden>0/{{ $lot_arr[$key][$process_key] }}</p></td>
                                        <td>{{ $data }}</td>
                                        <td class="right_black">完{{ preg_replace('/\d+/', '', $data) }}在庫：{{ $stock_arr[$key]["process"][$process_key] }}</td>
                                        @for ($i = 0; $i <= count($date_arr[0]); $i++)
                                            @if($quantity_arr[$key][$process_key+1][$i] === "" or $quantity_arr[$key][$process_key+1][$i] === 0)
                                                <td colspan="2" class="tap_day main_row"></td>
                                            @else
                                                <td colspan="2" class="tap_day main_row">残{{$quantity_arr[$key][$process_key+1][$i]}}</td>
                                            @endif
                                        @endfor
                                    @endif
                                    
                                </tr>
                            @endforeach
                            
                        @endforeach
                        <!-- 必要に応じて行を追加 -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div hidden id="data-container" data-data="{{ json_encode($work_arr) }}"></div>
<input type="hidden" name="process_able_area" id="process_able_area" value="{{ $selectable_json }}">
<script>
    var line = @json($line);
    var numbers = @json($numbers);
    var factory = @json($factory);
    var department = @json($department);
    var workers = @json($workers);
    // 現在のページに戻れないようにする
    history.pushState(null, null, location.href);
    window.addEventListener('popstate', function (event) {
        history.pushState(null, null, location.href);
    });

    window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        let form = $('<form>', {
            action: '/longinfo/view_post',
            method: 'post'
        });

        // CSRFトークンをフォームに追加
        let csrfToken = $('meta[name="csrf-token"]').attr('content');
        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: csrfToken
        }));

        // 隠しフィールドをフォームに追加
        form.append($('<input>', {
            type: 'hidden',
            name: 'line',
            value: line
        }));
        form.append($('<input>', {
            type: 'hidden',
            name: 'numbers',
            value: numbers
        }));
        form.append($('<input>', {
            type: 'hidden',
            name: 'factory',
            value: factory
        }));
        form.append($('<input>', {
            type: 'hidden',
            name: 'department',
            value: department
        }));
        form.append($('<input>', {
            type: 'hidden',
            name: 'workers',
            value: workers
        }));

        // フォームをボディに追加して送信
        form.appendTo('body').submit();

        // フォーム送信後にリロード
        // フォーム送信後にリロードが確実に行われるようにするために、リロードを少し遅らせる
        setTimeout(() => {
            // window.location.reload();
        }, 1000); // 100ミリ秒の遅延を設定
        }

        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                console.log('タブが非アクティブになりました');
            } else {
                console.log('タブがアクティブになりました');
            }
        });
    });

</script>

@if(config('app.env') === 'production')
    <script src="{{secure_asset('js/longinfos/longinfo.js')}}" ></script>

@else
    <script src="{{asset('js/longinfos/longinfo.js')}}"></script>

@endif

@endsection
