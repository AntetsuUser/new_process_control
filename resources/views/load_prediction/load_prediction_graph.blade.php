@extends('layouts.app')

@php
    use Carbon\Carbon;
@endphp
<head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.js"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-annotation/0.5.7/chartjs-plugin-annotation.min.js'></script>
</head>
@section('css')
@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/load_prediction/load_prediction_graph.css') }}" rel="stylesheet">
@else

    <link href="{{ asset('/css/load_prediction/load_prediction_graph.css') }}" rel="stylesheet">
@endif
@endsection

@section('content')
    
    {{-- <div class="browser_back_area">
        <a href="{{ route('load_prediction.department_select') }}"><img class="back_btn" src="{{ asset('img/icon/back.png') }}" alt=""><span>戻る</span></a>
    </div> --}}
    <div class="container">
        <div id="line_no" value="{{ $lineNo }}">{{$lineNo}}</div>
        <div class="row justify-content-center">
            @foreach($week_arr as $index => $week)
            <div class="col-5" >                
                <table class="graph_table" id="table{{$index}}">
                    <thead>
                        <tr>
                            <th>加工日</th>
                            @foreach ($week_arr[$index] as $week_key => $week_value)
                                <?php
                                    $week_value = DateTime::createFromFormat('Y-m-d', $week_value);
                                ?>
                                <th>{{$week_value->format('m/d')}}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($processing_numbers as $name_key => $quantity)
                        <tr>
                            <td>{{$name_key}}</td>
                            @foreach ($quantity[$index] as $parents_week => $item)
                                <td>{{$item}}</td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-7 canvas_size">
                <canvas id="canvas{{ $index }}" style="position: relative;" class="canvas_size"></canvas>
            </div>
        @endforeach
        </div>
    </div>
    <script>
        let percentage = {!! json_encode($percentage) !!};
        let line_no = $('#line_no');
        console.log(line_no);

        percentage.forEach((week, index) => {
            console.log(week);
            let date = [];
            let date_value = [];
            let color = "rgba(255,0,0,1)";
            let ability = [];
            let average_arr = [];
            let average_value = 0;

            Object.entries(week).forEach(function ([day, value]) {
                // console.log(day)
                // console.log(value)
                // Dateオブジェクトを作成
                const dateObject = new Date(day);
                // 月と日を取得
                const month = dateObject.getMonth() + 1; // getMonthは0から始まるので+1する
                const day1 = dateObject.getDate();
                // フォーマットされた文字列を作成
                const formattedDate = `${month}月${day1}日`;
                date.push(formattedDate);

                // 日付の値を取得
                let values = Object.values(day);

                // jQueryオブジェクトからHTMLを取得
                if (line_no.html().includes('U-N')) {
                    ability.push(75);
                    console.log("U-N");
                } else if (line_no.html().includes('U-M')) {
                    ability.push(85);
                    console.log("U-M");
                }

                date_value.push(value);
                average_value += parseFloat(value, 10);
            });

            console.log(date_value);
            average_value /= Object.keys(week).length;
            average_arr = new Array(date.length).fill(average_value);
            console.log(average_arr);

            var canvas = document.getElementById("canvas" + index);
            var myChart = new Chart(canvas, 
            {
                type: 'bar',
                // type: 'line',
                data:{
                        labels: date,
                        datasets: [                                 
                            {
                                label: '負荷率',
                                type: 'bar',
                                // type: 'line',
                                data: date_value,
                                borderColor: color,
                                backgroundColor: "rgba(255,0,0,0.8)",
                                lineTension: 0
                            },
                        ]
                    },
                options: {
                    title: {
                        
                        display: true,
                        text: '負荷率 第'+ (index+1) + '週目 （' + date[0] + '~' + date[date.length - 1] + '）',
                        fontSize: 21, // フォントサイズを指定
                        position: 'top', // タイトルを上部に配置
                        padding: 0 // タイトルとグラフの間にスペースを追加
                    },
                    scales: {
                        xAxes: [{
                            id: 'X軸', // id名
                            // 以下省略
                        }],
                        yAxes: [{
                            id: 'y左軸',
                        ticks: {
                            suggestedMax: 100,
                            suggestedMin: 0,
                            stepSize: 10,
                            callback: function(value, index, values){
                                
                            return  value +  '%'
                            }
                        }
                        }],
                        responsive: true,
                        maintainAspectRatio: false,
                    },
                    annotation: {
                        annotations: [
                            {
                                type: 'line', // 線分を指定
                                drawTime: 'afterDatasetsDraw',
                                id: 'a-line-2', // 線のid名を指定（他の線と区別するため）
                                mode: 'horizontal', // 水平を指定
                                scaleID: 'y左軸', // 基準とする軸のid名
                                value: ability[0], // 引きたい線の数値（始点）
                                endValue: ability[0], // 引きたい線の数値（終点）
                                borderColor: 'rgba(0,0,255,1)', // 線の色
                                borderWidth: 3, // 線の幅（太さ）
                                borderDashOffset: 1,
                                label: {
                                    content: "能力 " + ability[0] + "%",
                                    enabled: true,
                                    position: "top",
                                    backgroundColor:'rgba(0,0,255,1)',
                                }
                            },
                            {
                                type: 'line', // 線分を指定
                                drawTime: 'afterDatasetsDraw',
                                id: 'a-line-1', // 線のid名を指定（他の線と区別するため）
                                mode: 'horizontal', // 水平を指定
                                scaleID: 'y左軸', // 基準とする軸のid名
                                value: average_arr[0], // 引きたい線の数値（始点）
                                endValue: average_arr[0], // 引きたい線の数値（終点）
                                borderColor: 'rgba(0,255,0,1)', // 線の色
                                borderWidth: 3, // 線の幅（太さ）
                                borderDashOffset: 1,
                                label: {
                                    content: "平均 " + Math.round(average_arr[0] * 10) / 10  +"%",
                                    enabled: true,
                                    position: "top",
                                    backgroundColor:'rgba(0,255,0,1)',
                                }
                            }
                        ]
                    }
                }
            });
        });

        $(document).ready(function() {
            function adjustCanvasHeight() {
                // 各テーブルの高さを取得し、それに合わせてキャンバスの高さを調整
                $(".graph_table").each(function(index) {
                    var tableHeight = $(this).outerHeight(); // テーブルの外枠を含む高さを取得
                    var canvas = $("#canvas" + index); // キャンバス要素を選択

                    // キャンバスの高さをテーブルの高さと一致させる
                    canvas.height(tableHeight);
                });
            }

            // 初期調整
            adjustCanvasHeight();

            // ウィンドウがリサイズされたときに再調整
            $(window).resize(function() {
                adjustCanvasHeight();
            });
        });

    </script>
    
    @if(config('app.env') === 'production')
        <script src="{{secure_asset('js/load_prediction/load_prediction_graph.js')}}"></script>
    @else
        <script src="{{asset('js/load_prediction/load_prediction_graph.js')}}"></script>
    @endif
@endsection