@extends('layouts.app')

@section('css')

@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/masta/number.css') }}" rel="stylesheet">

@else
    <link href="{{ asset('/css/masta/number.css') }}" rel="stylesheet">

@endif

@endsection

@section('content')
<a href="#" onclick="history.back(-1);return false;">
    <img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt="" >
    <span>戻る</span>
</a>
<div class="container-fluid">
    @if ($number_params["submit"] == "追加")
        <p class="contents_title">以下の内容で追加しますか？</p>
    @elseif ($number_params["submit"] == "更新")
        <p class="contents_title">以下の内容で更新しますか？</p>
    @else
        <p class="contents_title">以下の内容を削除しますか？</p>
    @endif
    <div class="masta_area">
    <form action="" method ="POST">
    @csrf
        <div class="row  justify-content-center">
                @for ($i= 0; $i<3; $i++)
                    {{-- 品目情報 --}}
                    <div class="masta_number col-4 parent">

                        @if ($i== 0)
                            <div class="masta_number_title">親品番</div>
                        @elseif ($i== 1)
                            <div class="masta_number_title">子品番1</div>
                        @else
                            <div class="masta_number_title">子品番2</div>
                        @endif
                        <div class="masta_number_area masta_number_area__blue">
                            {{-- 図面番号 --}}
                            <div class="number_item">
                                <div class="number_item_title confirm_item_neme">図面番号</div>
                                <div class="number_item_value confirm_item_value">{{ $number_params['print_number'][$i] }}</div>
                                <input type="hidden" name="print_number[]" value="{{ $number_params['print_number'][$i] }}">
                            </div>
                            {{-- 加工品目 --}}
                            <div class="number_item">
                                <div class="number_item_title confirm_item_neme">加工品目</div>
                                <div class="number_item_value confirm_item_value">{{ $number_params['name'][$i] }}</div>
                                <input type="hidden" name="name[]" value="{{ $number_params['name'][$i] }}">
                            </div>
                            {{-- 品目名称 --}}
                            <div class="number_item">
                                <div class="number_item_title confirm_item_neme">品目名称</div>
                                <div class="number_item_value confirm_item_value">{{ $number_params['item_name'][$i] }}</div>
                                <input type="hidden" name="item_name[]" value="{{ $number_params['item_name'][$i] }}">
                            </div>
                            {{-- 材料品目 --}}
                            <div class="number_item">
                                <div class="number_item_title confirm_item_neme">材料品目</div>
                                @if ($number_params['resource_item'][$i])
                                    <div class="number_item_value confirm_item_value">{{ $number_params['resource_item'][$i] }}</div>
                                @else
                                    <div class="number_item_value confirm_item_value">　</div>
                                @endif
                                <input type="hidden" name="resource_item[]" value="{{ $number_params['resource_item'][$i] }}">
                            </div>
                            {{-- 品目集約 --}}
                            <div class="number_item">
                                <div class="number_item_title confirm_item_neme">品目集約</div>
                                
                                @if($i == 0)
                                    <div class="number_item_value confirm_item_value">{{ $number_params['collect_name'][$i]}}</div>
                                    <input type="hidden" name="collect_name[]" value="{{ $number_params['collect_name'][$i]}}">
                                @else
                                    <div class="number_item_value confirm_item_value">　</div>
                                    <input type="hidden" name="collect_name[]" value="">
                                @endif
                            </div>
                            {{-- 工場 --}}
                            <div class="number_item">
                                <div class="number_item_title confirm_item_neme">工場</div>
                                <div class="number_item_value confirm_item_value">{{ $number_params['factories_name'][$i]}}</div>
                                <input type="hidden" name="factories_id[]" value="{{ $number_params['factories_id'][$i]}}">
                                <input type="hidden" name="factories_name[]" value="{{ $number_params['factories_name'][$i]}}">
                            </div>
                            {{-- 製造課 --}}
                            <div class="number_item">
                                <div class="number_item_title confirm_item_neme">製造課</div>
                                <div class="number_item_value confirm_item_value">{{ $number_params['departments_name'][$i]}}</div>
                                <input type="hidden" name="departments_id[]" value="{{ $number_params['departments_id'][$i]}}">
                                <input type="hidden" name="departments_name[]" value="{{ $number_params['departments_name'][$i]}}">
                            </div>
                            {{-- ライン --}}
                            <div class="number_item">
                                <div class="number_item_title confirm_item_neme">ラインNo.</div>
                                <div class="number_item_value confirm_item_value">{{ $number_params['line_number'][$i]}}</div>
                                <input type="hidden" name="line_number[]" value="{{ $number_params['line_number'][$i]}}">
                            </div>
                            {{-- 工程を結合するか --}}
                            <div class="number_item">
                                <div class="number_item_title  confirm_item_neme">子品番の1工程,2工程の結合</div>
                                @if($i == 0)
                                    @if($number_params['join_flag'] == 1)    
                                        <div class="number_item_value confirm_item_value">結合する</div>
                                    @else
                                        <div class="number_item_value confirm_item_value">結合しない</div>
                                    @endif
                                @else
                                    <div class="number_item_value confirm_item_value">　</div>
                                @endif
                                <input type="hidden" name="join_flag" value="{{ $number_params['join_flag']}}">
                            </div>
                        </div>
                        {{-- 工程1 --}}
                        @for ($j = 1; $j <= 4; $j ++)

                            @if (is_null($number_params['process_' . $j][$i]))
                                @continue
                            @endif
                            @if ($j == 1 || $j == 3)
                            <div class="masta_number_area masta_number_area__orange">
                            @else
                            <div class="masta_number_area masta_number_area__green">
                            @endif
                                <div class="number_item">
                                    <div class="number_item_title  confirm_item_neme">工程{{ $j }}</div>
                                    <div class="number_item_value confirm_item_value">{{ $number_params['process_'. $j][$i]}}</div>
                                    <input type="hidden" name="process_{{ $j }}[]" value="{{ $number_params['process_'. $j][$i]}}">
                                </div>
                                <div class="number_item">
                                    <div class="number_item_title confirm_item_neme">ストア・W/C</div>
                                    <div class="number_item_value confirm_item_value">{{ $number_params['process_store_'. $j][$i]}}</div>
                                    <input type="hidden" name="process_store_{{ $j }}[]" value="{{ $number_params['process_store_'. $j][$i]}}">
                                </div>
                                <div class="number_item">
                                    <div class="number_item_title confirm_item_neme">加工時間</div>
                                    <div class="number_item_value confirm_item_value">{{ $number_params['process_sec_'. $j][$i]}}<span>sec</span></div>
                                    <input type="hidden" name="process_sec_{{ $j }}[]" value="{{ $number_params['process_sec_'. $j][$i]}}">
                                </div>
                                <div class="number_item">
                                    <div class="number_item_title confirm_item_neme">工程ロット</div>
                                    <div class="number_item_value confirm_item_value">{{ $number_params['process_lot_'. $j][$i]}}<span>個</span></div>
                                    <input type="hidden" name="process_lot_{{ $j }}[]" value="{{ $number_params['process_lot_'. $j][$i]}}">
                                </div>
                            </div>
                        @endfor
                    </div>
                @endfor
                @if (isset($id))
                    <input type="hidden" value="{{ json_encode($id) }}" name="id" id="id">
                    <input type="hidden" value="{{ $number_params['delete_id'] }}" name="delete_id" id="delete_id">
                    @if ($number_params["submit"] == "更新")
                        <input type="submit" class="btn btn-primary mt-5 w-100" name="submit" style="height:50px; font-size:1.3rem;" value="更新" formaction="{{ route('masta.number_store') }}" method ="POST">
                    @else
                         <input type="submit" class="btn btn-danger mt-5 w-100" name="submit" style="height:50px; font-size:1.3rem;" value="削除" formaction="{{ route('masta.number_delete') }}" method ="POST">
                    @endif  
                @else
                    <input type="submit" class="btn btn-primary mt-5 w-100" name="submit" style="height:50px; font-size:1.3rem;" value="追加" formaction="{{ route('masta.number_store') }}">
                @endif
        </div>
        </form>
    </div>
    
</div>
@endsection