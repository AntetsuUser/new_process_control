$('.submit_btn').on('click', function (e) {
        e.preventDefault(); // デフォルトのフォーム送信を停止
        // Ajaxリクエストでlogコントローラーにデータを送信
        $.ajax({
            url: log_submit_url, // ルートで定義されたURL
            type: 'POST',
             data: {
                    data: "長期情報表示" // キーと値のペアで送信
                },
            dataType: 'json',
            success: function (response) {
                console.log('Log sent successfully:', response);

                // 成功時にフォームをサブミット
                $('form').submit();
            },
            error: function (xhr, status, error) {
                console.error('Error sending log:', error);
                alert('エラーが発生しました。再試行してください。');
            }
        });
    });