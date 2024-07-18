// ページの読み込みが終わってからjQuery実行
$(document).ready(function() 
{
    // 編集のとき
    let select_factory_id = $('#factory_id').val();
    if(select_factory_id)
    {
        // inputタグに設定
        $('#factory_name').val(select_factory_id);
        
        let select_department_id = $('#department_id').val();
        if (select_department_id)
        {
            $('#department_name').val(select_department_id);
        }

        ajax(select_factory_id);
    }

    // 編集のときに工場・製造課を変更しなかったときに初期値を渡すようにする
    let init_factory_text = $('#factory_id option:selected').text();
    if (init_factory_text)
    {
        $('#factory_name').val(init_factory_text);

        let init_depertment_text = $('#department_id option:selected').text();
        if (init_depertment_text)
        {
            $('#department_name').val(init_depertment_text);
        }
    }

    // 工場を選んだら
    $('#factory_id').change(function() 
    {
        // 工場を選択するたびに部署のoptionタグの中身を初期化する
        // option:not(:first-child) -> optionタグの最初の子要素以外
        $("#department_id option:not(:first-child)").remove();

        // 選択されたら最初の要素のテキストを初期化
        $("#factory_id :first-child").text('--- 選択してください ---');
        $("#department_id :first-child").text('--- 選択してください ---');
        $("#department_id :first-child").val(' ');
    
        // selected 属性を削除
        $('#factory_id option').removeAttr('selected');
        $('#department_id option').removeAttr('selected');

        // 選択した工場のテキストを取得
        var factory_str = $('#factory_id').find('option:selected').text();
        var factory_div = $('#factory_id').find('option:selected').val();

        // inputタグに設定
        $('#factory_name').val(factory_str);

        ajax(factory_div);
    });

    // 部署を選んだら
    $('#department_id').change(function() 
    {
        // 選択した部署のテキストを取得
        var department_div = $('#department_id').find('option:selected').text();

        // inputタグに設定
        $('#department_name').val(department_div);

        console.log("選択した部署:", $('#department_name').val());
    });

    // // 初期化
    // var url = window.location.href;
    // url = url.replace("http://192.168.3.96:8000/", "");
    // if (url == "masta/worker_edit") 
    // {
    //     // 編集のときに何故かエラーになるので、新規追加のときだけ初期化する
        // set_factory_id()
    // }

    // $("#department_id option:not(:first-child)").remove();
    //  
    // $("#factory_id :first-child").text('--- 選択してください ---');
    // $("#department_id :first-child").text('--- 選択してください ---');
    // $("#department_id :first-child").val(' ');

    // // すべてのオプションから selected 属性を削除
    // $('#factory_id option').removeAttr('selected');
});

// function set_factory_id()
// {

// }

function ajax(str)
{
    let factory_div = str;
    console.log(factory_div);

    $.ajax({
        url: "https://192.168.3.96/ajax/masta/worker.php", 

        type: "POST",
        dataType: "json",
        data: 
        {
            factory_div: factory_div,
        },
    })
    .done(function (data) 
    {
        console.log(data);
        $.each(data, function(index, element) 
        {
            // console.log(element.id);

            // 選択した工場に対応した部署をoptionタグを設定する
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

// let factory_select = document.querySelector('[name="factory_id"]');
// factory_select.onchange = (event) => 
// {
//     var department_id = document.getElementById("department_id");
//     if(department_id != null)
//     {
//         for (let i = department_id.options.length - 1; i > 0; i--) 
//         {
//             department_id.remove(i);
//         }
//     }
//     let factory_div = factory_select.value;
//     console.log(factory_div);

//     $.ajax({
//         url: "./ajax/mastas/worker.php",
//         type: "POST",
//         dataType: "json",
//         data: {
//             factory_div: factory_div,
//         },
//     })
//     .done(function (data) 
//     {
//         console.log(data)
//             data.forEach((element) => {
//                 console.log(element['name']);
//                 // // optionタグを作成する
//                 var option = document.createElement("option");
//                 // // optionタグのテキストを4に設定する
//                 option.text = element["name"];
//                 // // // optionタグのvalueを4に設定する
//                 option.value = element["name"];
//                 department_id.appendChild(option);
//             });
//         })
//         .fail(function (XMLHttpRequest, textStatus, errorThrown) {
//             window.alert(
//                 "DB接続に失敗しました。\nシステム担当にご連絡ください。"
//             );
//             console.log("Ajax,失敗");
//             console.log("XMLHttpRequest : " + XMLHttpRequest);
//             console.log("textStatus : " + textStatus);
//             console.log("errorThrown : " + errorThrown);
//         });
// }


// //------------------フィルター-------------------------
// //filtering_btn
// // let mytable = document.getElementById("workersTable"); //html側のtableについてるidで要素を取得
// // let col3 = document.getElementById("col3");
// // let  count =  1
// //     for (var j=0; j < mytable.rows[0].cells.length; j++) 
// //     {
// //         if(mytable.rows[0].cells[j].getAttribute('filtering_btn')!== null)
// //         {
// //             console.log(  "(" + 0 + "," + j + ") : " + mytable.rows[0].cells[j].innerHTML  );
// //                 // --- ボタンを追加（画像はsvgを使用） ------------------
// //             var wAddBtn = '<div class="thlist" id="thlist_'+j+'" onclick="tFilterCloseOpen('+j+')">■</div>';
// //             mytable.rows[0].cells[j].innerHTML = mytable.rows[0].cells[j].innerHTML+wAddBtn;
// //             let new_element = document.createElement('div');
// //                 new_element.id = "list_value_" +j; 
// //                 new_element.style.display = "none";
// //                 new_element.style.position= "absolute";
// //                 new_element.style.backgroundColor= "#dadada";
// //                 col3.appendChild(new_element);
// //             for(var i=0; i < mytable.rows.length; i++)
// //             {
// //                 let list_value = document.getElementById("list_value_"+j);
// //                 list_name = mytable.rows[i+1].cells[j].innerHTML
// //                 console.log(col3)
// //                 // // 新しいHTML要素を作成

// //                 let label = document.createElement('label'); // ラベル要素を作成
// //                 let checkbox = document.createElement('input'); // チェックボックス要素を作成
// //                 checkbox.type = "checkbox"; // チェックボックスの属性設定
// //                 label.textContent = list_name; // ラベルのテキストコンテンツ設定
// //                 label.appendChild(checkbox); // チェックボックスをラベル内に追加
// //                 list_value.appendChild(label); // ラベルをlist_value内に追加
// //                 console.log(list_value)
// //             }
// //         }

// //     }

// // function tFilterCloseOpen(tap_column)
// // {
    
// //     let list_value = document.getElementById("list_value_"+tap_column);
// //     console.log("Hello" + tap_column);
// //     console.log(count);
// //     if(count % 2 === 0 )
// //     {
// //         list_value.style.display = "none";
// //     }
// //     else
// //     {
// //         list_value.style.display = "inline-block";
// //     }
// //     count++

// // }