$(document).ready(function() {
    var $mytable = $("#load_prediction_table");
    var department_id = $("#department_id");
    
    // ヘッダーの週を取得して配列に格納
    var week_arr = [];
    $mytable.find("th").slice(1, 4).each(function() {
        week_arr.push($(this).text());
    });

    // テーブルの行を処理
    $mytable.find("tr").each(function(index) {
        if (index < 2) return;  // 最初の2行はスキップ（ヘッダー行）

        var $cells = $(this).find("td");
        var targetString = $cells.eq(0).text();

        if (targetString.includes("U-N")) {
            // 旋盤は75%以上
            $cells.each(function(i) {
                if (i > 0) { // 最初のセルはスキップ
                    var rate = parseFloat($(this).text());
                    if (rate >= 75.0) {
                        $(this).css("background-color", "rgb(255, 204, 204)");
                    }
                }
            });
        }

        if (targetString.includes("U-M")) {
            // マシニングは85%以上
            $cells.each(function(i) {
                if (i > 0) { // 最初のセルはスキップ
                    var rate = parseFloat($(this).text());
                    if (rate >= 85.0) {
                        $(this).css("background-color", "rgb(255, 204, 204)");
                    }
                }
            });
        }
    });
    //設備番号がタップされたとき
    $(".machine.line_no").on("click", function() {

         var csrfToken = $('meta[name="csrf-token"]').attr('content');
        // クリックした<td>の内容を取得
        var machineNumber = $(this).text();
        // 取得した内容をコンソールに表示（または任意の処理）
        console.log("タップされた機械番号: " + machineNumber);

       // フォームを動的に作成
        var $form = $('<form>', {
            action: 'load_prediction_graph',
            method: 'POST'
        });
        $('<input>', {
            type: 'hidden',
            name: '_token',
            value: csrfToken
        }).appendTo($form);


        // department_idをhiddenフィールドで追加
        $('<input>', {
            type: 'hidden',
            name: 'division',
            value: $('#department_id').text() // department_idを取得
        }).appendTo($form);

        // 選択された設備番号をhiddenフィールドで追加
        $('<input>', {
            type: 'hidden',
            name: 'lineNo',
            value: machineNumber
        }).appendTo($form);

        // 週が入った配列をhiddenフィールドで追加
        $('<input>', {
            type: 'hidden',
            name: 'week_arr',
            value: JSON.stringify(days) // days配列をJSON形式で送信
        }).appendTo($form);

        // フォームをbodyに追加して送信
        $('body').append($form);
        $form.submit();

    });
});
