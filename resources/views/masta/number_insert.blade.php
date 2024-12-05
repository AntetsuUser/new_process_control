@extends('layouts.app')

@section('css')

@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/masta/number.css') }}" rel="stylesheet">

@else
    <link href="{{ asset('/css/masta/number.css') }}" rel="stylesheet">

@endif

@endsection

@section('content')
<div class="browser_back_area">
    <a href="{{ route('masta.number') }}"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span></a>
</div>
<div class="container-fluid">

    @if (isset($data))
        <p class="contents_title">品目編集</p>
        @php
            $ids =[]
        @endphp
    @else
        <p class="contents_title">品目追加</p>
    @endif 
    <div class="masta_area">
        <form action="" method ="POST" onsubmit="return handleSubmit(this);">
            @csrf
            <div class="row  justify-content-center">
                {{-- 親品番の品目情報 --}}
                @for ($i= 1; $i<4; $i++)
                @if($i== 1)
                <div class="masta_number col parent">
                    <div class="masta_number_title">親品番</div>
                @elseif ($i== 2)
                <div class="masta_number ml-3 col child_1">
                    <div class="masta_number_title">子品番1</div>
                @else
                <div class="masta_number ml-3 col child_2">
                    <div class="masta_number_title">子品番2</div>
                @endif
                    <div class="masta_number_area masta_number_area__blue">
                        {{-- 図面番号 --}}
                        <div class="number_item">
                            <div class="number_item_title">図面番号 </div>
                            <input type="text" name="print_number[]" value="{{ $data[$i-1]['select_item']->print_number ?? '' }}">
                            <select>
                                <option value="">--</option>
                                @if ($i== 1)
                                @foreach ($numbers['print_number_parent'] as  $value)
                                    <option value="{{$value}}">{{$value}}</option>
                                @endforeach
                                @else
                                    @foreach ($numbers['print_number_child'] as  $value)
                                    <option value="{{$value}}">{{$value}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                        {{-- 加工品目 --}}
                        <div class="number_item">
                            <div class="number_item_title">加工品目</div>
                            <input type="text" name="name[]" value="{{ $data[$i-1]['select_item']->processing_item ?? '' }}">
                            <select>
                                <option value="">--</option>
                                @if ($i== 1)
                                @foreach ($numbers['processing_item'] as  $value)
                                    <option value="{{$value}}">{{$value}}</option>
                                @endforeach
                                @else
                                    @foreach ($numbers['processing_item_child'] as  $value)
                                    <option value="{{$value}}">{{$value}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                        {{-- 品目名称 --}}
                        <div class="number_item">
                            <div class="number_item_title">品目名称</div>
                            <input type="text" name="item_name[]" value="{{ $data[$i-1]['select_item']->item_name ?? '' }}">
                            <select>
                                <option value="">--</option>
                                @if ($i== 1)
                                @foreach ($numbers['item_name'] as  $value)
                                    <option value="{{$value}}">{{$value}}</option>
                                @endforeach
                                @else
                                    @foreach ($numbers['item_name_child'] as  $value)
                                    <option value="{{$value}}">{{$value}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                        {{-- 材料品目 --}}
                        <div class="number_item">
                            <div class="number_item_title">材料品目</div>
                            <input type="text" name="resource_item[]" value="{{ $data[$i-1]['select_item']->material_item ?? '' }}">
                            <select>
                                <option value="">--</option>
                                @if ($i== 1)
                                @foreach ($numbers['material_item'] as  $value)
                                    <option value="{{$value}}">{{$value}}</option>
                                @endforeach
                                @else
                                    @foreach ($numbers['material_item_child'] as  $value)
                                    <option value="{{$value}}">{{$value}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                        {{-- 品目集約 --}}
                        <div class="number_item">
                            <div class="number_item_title">品目集約</div>
                            <input type="text" name="collect_name[]" value="{{ $data[$i-1]['select_item']->collect_name ?? '' }}">
                            <select>
                                <option value="">--</option>
                                 @if ($i== 1)
                                @foreach ($numbers['collect_name'] as  $value)
                                    <option value="{{$value}}">{{$value}}</option>
                                @endforeach
                                @else
                                    @foreach ($numbers['collect_name_child'] as  $value)
                                    <option value="{{$value}}">{{$value}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                        {{-- 工場 --}}
                        <div class="number_item">
                            <div class="number_item_title">工場</div>
                            <select name="factories_id[]" id="factories_id_{{ $i-1 }}">
                                <option value="{{ $data[$i-1]['select_item']->factory_id ?? '--' }}">{{ $data[$i-1]['select_item']->factory_name ?? '--' }}</option>
                                @foreach ($factory as $value)
                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="factories_name[]" id="factories_name_{{ $i-1 }}" value="{{ $data[$i-1]['select_item']->factory_name ?? '--' }}">
                        </div>
                        {{-- 製造課 --}}
                        <div class="number_item">
                            <div class="number_item_title">製造課</div>
                            <select name="departments_id[]" id="departments_id_{{ $i-1 }}">
                                <option value="{{ $data[$i-1]['select_item']->department_id ?? '--' }}">{{ $data[$i-1]['select_item']->department_name ?? '--' }}</option>
                            </select>
                            <input type="hidden" name="departments_name[]" id="departments_name_{{ $i-1 }}" value="{{ $data[$i-1]['select_item']->department_name ?? '--' }}">
                        </div>
                        {{-- ライン --}}
                        <div class="number_item">
                            <div class="number_item_title">ラインNo.</div>
                            <input type="text" name="line_number[]" value="{{ $data[$i-1]['select_item']->line ?? '' }}">
                        </div>
                        @if($i== 1)
                            {{-- 工程を結合するか --}}
                            <div class="number_item">
                                <div class="number_item_title">子品番の1工程,2工程の結合</div>
                                <label for="join"><input type="radio" id="join" name="join_flag" value="1"   {{  isset($data[0]['select_item']->join_flag) && $data[0]['select_item']->join_flag == "1" ? 'checked' : '' }} />結合</label>
                                <label for="nojoin"><input type="radio" id="nojoin" name="join_flag" value="0" {{ (isset($data[0]['select_item']->join_flag) && $data[0]['select_item']->join_flag == "0") || !isset($data[0]['select_item']->join_flag) ? 'checked' : '' }} />結合しない</label>
                            </div>

                        @endif
                    </div>
                    {{-- 工程 --}}
                    @for ($j = 1;  $j <= 4; $j++)
                    @if (isset($data[$i-1]['process'][$j-1]))
                    @if ($j == 1 || $j == 3)
                    <div class="masta_number_area masta_number_area__orange">
                    @else
                    <div class="masta_number_area masta_number_area__green">
                    @endif
                        <div class="number_item">
                            <div class="number_item_title">工程{{ $j }}</div>
                            <input type="text" name="process_{{ $j }}[]" value="{{ $data[$i-1]['process'][$j-1]->process }}">
                            <select>
                                <option value="">--</option>
                                @foreach ($numbers['process'] as  $value)
                                    <option value="{{$value}}">{{$value}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="number_item">
                            <div class="number_item_title">ストア・W/C</div>
                            <input type="text" name="process_store_{{ $j }}[]" value="{{ $data[$i-1]['process'][$j-1]->store }}" readonly onkeydown="enableBackspace(this, event)">
                            
                        </div>
                        <div class="number_item">
                            <div class="number_item_title">加工時間</div>
                            <div class="text-right"><input type="number" name="process_sec_{{ $j }}[]" pattern="^[0-9]+$" min="0" value="{{ $data[$i-1]['process'][$j-1]->processing_time }}"> sec</div>
                        </div>
                        <div class="number_item">
                            <div class="number_item_title">工程ロット</div>
                            <div class="text-right"><input type="number" name="process_lot_{{ $j }}[]" pattern="^[0-9]+$" min="0" value="{{ $data[$i-1]['process'][$j-1]->lot }}"> 個</div>
                        </div>
                    </div>
                    @else
                    @if ($j == 1 || $j == 3)
                    <div class="masta_number_area masta_number_area__orange">
                    @else
                    <div class="masta_number_area masta_number_area__green">
                    @endif
                        <div class="number_item">
                            <div class="number_item_title">工程{{ $j }}</div>
                            <input type="text" name="process_{{ $j }}[]">
                            <select>
                                <option value="">--</option>
                            </select>
                        </div>
                        <div class="number_item">
                            <div class="number_item_title">ストア・W/C</div>
                            <input type="text" name="process_store_{{ $j }}[]"  readonly onkeydown="enableBackspace(this, event)">
                            
                        </div>
                        <div class="number_item">
                            <div class="number_item_title">加工時間</div>
                            <div class="text-right"><input type="number" name="process_sec_{{ $j }}[]" pattern="^[0-9]+$" min="0"> sec</div>
                        </div>
                        <div class="number_item">
                            <div class="number_item_title">工程ロット</div>
                            <div class="text-right"><input type="number" name="process_lot_{{ $j }}[]" pattern="^[0-9]+$" min="0"> 個</div>
                        </div>
                    </div>
                    @endif
                    @endfor
                </div>
                @php
                   if (isset($data[$i-1]['select_item']) && !is_null($data[$i-1]['select_item'])) {
                        array_push($ids, $data[$i-1]['select_item']->id);
                    }
                @endphp
                @endfor
            </div>
            @if (isset($data))
                {{-- 更新する場合は値をinputに設定してformに送信 --}}
                <input type="hidden" value="{{ json_encode($ids) }}" name="id" id="id">
                <input type="hidden" value="{{ $id }}" name="delete_id" id="delete_id">
                <input type="submit" class="btn btn-danger mt-5 update_btn" name="submit" style="height:50px; font-size:1.3rem;" value="削除" formaction="{{ route('masta.number_confirm') }}" method ="POST">
                <input type="submit" class="btn btn-primary mt-5 update_btn" name="submit" style="height:50px; font-size:1.3rem;" value="更新" formaction="{{ route('masta.number_confirm') }}" method ="POST">
            @else
                <input type="submit" class="btn btn-primary mt-5 w-100" name="submit" style="height:50px; font-size:1.3rem;" value="追加" formaction="{{ route('masta.number_confirm') }}">
            @endif 
        </form>
    </div>
</div>
<script>
function enableBackspace(input, event) {
    // Backspace キーが押された場合のみ入力を許可
    if (event.key === 'Backspace') {
        input.readOnly = false;
    } else {
        input.readOnly = true;
        event.preventDefault(); // 他のキー入力を防ぐ
    }

    // Backspaceキーで削除された後、再度readonlyを設定
    input.addEventListener('input', function() {
        input.readOnly = true;
    });


    
}
function handleSubmit(form) {
    // 送信ボタンの`formaction`属性の値を取得
    const formAction = document.activeElement.getAttribute('formaction');

    // `action`属性を設定し、フォームを送信
    form.action = formAction;
    form.method = 'POST'; // 必要に応じて`method`を設定

    form.submit(); // フォームを手動で送信
    return false; // ページのリロードを防ぐために`false`を返す
}
</script>

@if(config('app.env') === 'production')
    <script src="{{secure_asset('js/masta/number.js')}}"></script>
@else
    <script src="{{asset('js/masta/number.js')}}"></script>
@endif
@endsection
