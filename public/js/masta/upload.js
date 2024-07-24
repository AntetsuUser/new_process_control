$(function(){ 

})

$('.delete_button').on('click', function() {
    //どこのボタンが押されたか取得
    var row_index = $(this).closest("tr").index();
    // console.log(row_index)
    let id = $('#id_' + row_index).val();
    console.log(id);
     // Ajaxリクエストでサーバーに削除要求を送信
    $.ajax({
        url: '../ajax/masta/shipment_info_deletion.php', // PHPスクリプトのURLを指定
        type: 'POST',
        data: { id: id },
        dataType: 'json', // 期待するデータ形式をJSONに指定
        success: function(response) {
            console.log(response)
            if (response.success) {
                alert('削除が完了しました');
               // 成功したらページをリロード
                location.reload();
            } else {
                alert('削除に失敗しました: ' + response.message);
            }
        },
        error: function(xhr) {
            alert('削除に失敗しました');
        }
    });
});
