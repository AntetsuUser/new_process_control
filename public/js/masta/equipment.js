// ページの読み込みが終わってからjQuery実行
$(document).ready(function() 
{
    let select_factory_id = $('#factory_id').val();
    console.log(select_factory_id);
    if(select_factory_id)
    {
        //select_factory_idがある場合
        $('#factory_name').val(select_factory_id);

        let select_department_id = $('#department_id').val();
        if (select_department_id)
        {
            $('#department_name').val(select_department_id);
        }

        ajax(select_factory_id);
    }

    //selectが変更されたとき
    $('#factory_id').change(function() 
    {   
        $("#department_id option:not(:first-child)").remove();

        // 選択されたら最初の要素のテキストを初期化
        $("#factory_id :first-child").text('--- 選択してください ---');
        $("#department_id :first-child").text('--- 選択してください ---');
        $("#department_id :first-child").val(' ');
    
        // selected 属性を削除
        $('#factory_id option').removeAttr('selected');
        $('#department_id option').removeAttr('selected');

        let select_factory_id = $(this).val();
        let select_factory_text = $('#factory_id').find('option:selected').text();
        console.log("選択した工場：" + select_factory_text);

        // inputタグに設定
        $('#factory_name').val(select_factory_text);
        
        ajax(select_factory_id);
    });

     //selectが変更されたとき
    $('#department_id').change(function() 
    {  
         // 選択した部署のテキストを取得
        var department_name = $('#department_id').find('option:selected').text();
        $('#department_name').val(department_name);

        console.log("選択した部署：" + department_name);
    });
});

function ajax(id)
{

    $.ajax({
        url: "https://192.168.3.96/ajax/masta/department_get.php", 
        type: "POST",
        dataType: "json",
        data: 
        {
            factory_id: id,
        },
    })
    .done(function (data) 
    {
        console.log(data);
        data.forEach(element => {
            $("<option>").text(element["name"]).val(element["id"]).appendTo($("#department_id")); 
        });
    })
    .fail(function (jqXHR, textStatus, errorThrown) 
    {
        window.alert("DB接続に失敗しました。\nシステム担当にご連絡ください。");
        console.log("Ajax,失敗");
        console.log("jqXHR : " + jqXHR);
        console.log("textStatus : " + textStatus);
        console.log("errorThrown : " + errorThrown);
    });
}