console.log(log_url);

    $(document).ready(function () {
        // CSRFトークンの設定
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // selectが選択されたときのイベントを補足
    $('select').on('change', function () {
        let selectId = $(this).attr('id'); // 変更されたselectのIDを取得
        let selectedValue = $(this).val(); // 選択された値を取得
        let selectedText = $(this).find('option:selected').text();
        let select_text = "";

        console.log(selectId);
        if(selectId == "factory"){
            select_text = "工場"
        }else if(selectId == "department"){   
            select_text = "製造課"
        }else if(selectId == "line"){
            select_text = "ライン"
        }else if(selectId == "numbers"){
            select_text = "番号"
        }else if(selectId == "workers"){
            select_text = "作業者"
        }
        // デフォルトの選択状態を無視する
        if (!selectedValue) {
            // AJAXでログ送信
            
            return;
        }

        // AJAXでログ送信
        $.ajax({
            url: log_url, // ログ記録用のルート
            method: 'POST',
            data: {
                select_id: selectId,
                selected_value: selectedValue,
                selected_text: selectedText,
                select_text: select_text
            },
            success: function (response) {
                console.log('ログ記録成功:', response.message);
            },
            error: function (error) {
                console.error('ログ記録エラー:', error);
            }
        });
    });


});
