$(document).ready(function() {

    //ajaxで使う
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    let　URL = logRoute;
    let production = $('#production').val();



    // アイテムセクション（プロセスセクションに似せる）
    let itemTextBox = $('#select_item_name');
    let itemCheckBoxDiv = $('#item_checkbox_area .check_box');
    let itemCheckboxes = $('#item_checkbox_area .item-checkbox');
    let allItemCheckbox = itemCheckboxes.filter('[value="すべて"]'); // "すべて"のチェックボックス

    // アイテムテキストボックスがクリックされたとき
    itemTextBox.on('click', function() {
        let checkedItems = itemCheckboxes.filter(':checked').map(function() {
            return $(this).val();
        }).get();
        if (checkedItems.length == 0) {
            itemTextBox.val("すべて");
            allItemCheckbox.prop('checked', true);
        }

        itemCheckBoxDiv.show(); // アイテムチェックボックスを表示
    });

    // アイテムチェックボックスの状態が変更されたとき
    itemCheckboxes.on('change', function() {
        // チェックされたアイテムの値を取得
        let checkedItems = itemCheckboxes.filter(':checked').map(function() {
            return $(this).val();
        }).get();

        let clickedValue = $(this).val(); // クリックされたチェックボックスの値
        console.log(clickedValue);
        // チェックボックスが「すべて」の場合
        if (clickedValue === "すべて") {
            if (checkedItems.includes("すべて") && checkedItems.length > 1) {
                itemCheckboxes.prop('checked', false);
                allItemCheckbox.prop('checked', true);
                itemTextBox.val("すべて");
            }
        } else {
            // 「すべて」が選択されていない場合
            if (checkedItems.includes("すべて")) {
                allItemCheckbox.prop('checked', false);
            }

            // チェックされたアイテムをテキストボックスに追加
            let currentText = itemTextBox.val();
            let updatedText = checkedItems.join(', '); // チェックされた値をカンマ区切りで結合

            // テキストボックスに新しい値を設定
            itemTextBox.val(updatedText);
        }

        // チェックボックスが外れた場合、その値をテキストボックスから削除する処理
        itemCheckboxes.each(function() {
            if (!$(this).is(':checked')) {
                let valueToRemove = $(this).val();
                let currentText = itemTextBox.val();
                let updatedText = currentText.split(', ').filter(function(item) {
                    return item !== valueToRemove; // 外す値をフィルタリング
                }).join(', ');

                itemTextBox.val(updatedText); // 更新されたテキストを設定
            }
        });
    });


    // プロセスセクション
    let processTextBox = $('#select_process');
    let processCheckBoxDiv = $('#process_checkbox_area .check_box');
    let processCheckboxes = $('#process_checkbox_area .process-checkbox');
    let allProcessCheckbox = processCheckboxes.filter('[value="すべて"]'); // "すべて"のチェックボックス

    // プロセステキストボックスがクリックされたとき
    processTextBox.on('click', function() {
        let checkedProcesses = processCheckboxes.filter(':checked').map(function() {
            return $(this).val();
        }).get();
        if (checkedProcesses.length == 0) {
            processTextBox.val("すべて"); 
            allProcessCheckbox.prop('checked', true);
        }

        processCheckBoxDiv.show(); // プロセスチェックボックスを表示
    });

    // プロセスチェックボックスの状態が変更されたとき
    processCheckboxes.on('change', function() {
        // チェックされたプロセスの値を取得
        let checkedProcesses = processCheckboxes.filter(':checked').map(function() {
            return $(this).val();
        }).get();

        let clickedValue = $(this).val(); // クリックされたチェックボックスの値

        // チェックボックスが「すべて」の場合
        if (clickedValue === "すべて") {
            if (checkedProcesses.includes("すべて") && checkedProcesses.length > 1) {
                processCheckboxes.prop('checked', false);
                allProcessCheckbox.prop('checked', true);
                processTextBox.val("すべて"); 
            }
        } else {
            // 「すべて」が選択されていない場合
            if (checkedProcesses.includes("すべて")) {
                allProcessCheckbox.prop('checked', false);
            }

            // チェックされたプロセスをテキストボックスに追加
            let currentText = processTextBox.val();
            let updatedText = checkedProcesses.join(', '); // チェックされた値をカンマ区切りで結合

            // テキストボックスに新しい値を設定
            processTextBox.val(updatedText);
        }

        // チェックボックスが外れた場合、その値をテキストボックスから削除する処理
        processCheckboxes.each(function() {
            if (!$(this).is(':checked')) {
                let valueToRemove = $(this).val();
                let currentText = processTextBox.val();
                let updatedText = currentText.split(', ').filter(function(item) {
                    return item !== valueToRemove; // 外す値をフィルタリング
                }).join(', ');

                processTextBox.val(updatedText); // 更新されたテキストを設定
            }
        });
    });

    // ドキュメントがクリックされたときにチェックボックスを非表示にする
   $(document).on('click', function(event) {
    // チェックボックスが表示されている状態で、チェックボックスやテキストボックスがクリックされた場合には非表示にしない
    if (!itemTextBox.is(event.target) && !processTextBox.is(event.target) &&
        !itemCheckBoxDiv.is(event.target) && !itemCheckBoxDiv.has(event.target).length &&
        !processCheckBoxDiv.is(event.target) && !processCheckBoxDiv.has(event.target).length) {
        itemCheckBoxDiv.hide(); // 外側をクリックしたらアイテムチェックボックスを非表示
        processCheckBoxDiv.hide(); // 外側をクリックしたらプロセスチェックボックスを非表示
    }
});

    // 更新ボタンが押されたときの処理
    $("#update_button").click(function() {
        //ロード画面を表示させる
        $(".wrapper_div").fadeIn();
        $('#dataBody').empty();
        let items = checkbox_confirmation(".item-checkbox");
        console.log(items);
        if (items.length === 0) {
            alert("品目集約が選択されていません");
            return; // 処理を終了
        }
        let process = checkbox_confirmation(".process-checkbox");
        // 何も選択されていなかったらすべてに選択する
        if (process.length === 0　|| process[0] == "すべて") {
            let allprocess = $(".process-checkbox").map(function() {
                return $(this).val(); // 各チェックボックスの値を取得
            }).get(); // jQueryオブジェクトを配列に変換
            process = allprocess;
            process = process.filter(function(item) {
                return item !== "すべて";
            });

        }
        //URLと製造課と品目集約と工程をajaxに渡す
        ajax(URL,production,items,process)
    });
});

// チェックボックスの値を確認する関数
function checkbox_confirmation(class_name) {
    let checkedValues = [];

    $(class_name + ":checked").each(function() {
        checkedValues.push($(this).val());
    });

    return checkedValues;
}

//ajax
function ajax(URL,production,item_names,process)
{
    
    $.ajax({
        url: URL,  // リクエストのURL
        type: 'POST',           // リクエストメソッド
        data: {
            production: production,
            item_names: item_names,
            process:process,
        },
        success: function(response) {
            console.log(response);
            let target_data = response[0];
            let info_data = response[1];
            let stock_data = response[2];   
            let display_array = response[3];   
            let display_stock_array = response[4];  
            let material_mark_arr = response[5];  
            let material_stock =   response[6];  
            Object.keys(info_data).forEach(key => {
                // 新しい行を作成
                let row = $('<tr></tr>');

                let idCell = $('<td rowspan="2" class="blue_line black_line"></td>').text(key); // プロパティのキーを使用
                row.append(idCell);
                let space = $('<td class="blue_line"></td>').text(" "); // プロパティのキーを使用
                row.append(space);
                let stock = $('<td class="blue_line"></td>').text("102材料在庫：" + material_stock[key]["102"]); // プロパティのキーを使用
                row.append(stock);
                for(let i = 0;  i < dateArray.length; i++)
                {
                    let stock2 = $('<td class="blue_line"></td>').text(material_mark_arr[key]["102"][i]); // プロパティのキーを使用
                    row.append(stock2);
                    let stock3 = $('<td class="blue_line"></td>').text(material_mark_arr[key]["103"][i]); // プロパティのキーを使用
                    row.append(stock3);
                }
                $('#dataBody').append(row);
                row = $('<tr></tr>');
                space = $('<td  class="black_line"></td>').text(" "); // プロパティのキーを使用
                row.append(space);
                stock = $('<td  class="black_line"></td>').text("103材料在庫:"+material_stock[key]["103"]); // プロパティのキーを使用
                row.append(stock);
                for(let i = 0;  i < dateArray.length; i++)
                {
                    let target_quantity = target_data[key][i];
                    let stock2
                    if(target_quantity == 0)
                    {
                        stock2 =  $('<td colspan="2"  class="black_line"></td>').text("　"); // プ
                    }
                    else
                    {
                        stock2 =  $('<td colspan="2"  class="black_line"></td>').text(target_quantity); // プ
                    }
                    row.append(stock2);
                }
                $('#dataBody').append(row);
                Object.keys(info_data[key]).forEach((element,index )=> {
                    row = $('<tr></tr>');
                    space = $('<td></td>').text(" "); // プロパティのキーを使用
                    row.append(space);
                    //選択されている工程
                    let process = $('<td></td>').text(display_array[index]); // プロパティのキーを使用
                    row.append(process);

                    //工程在庫
                    let process_stock =  $('<td></td>').text(display_stock_array[index]+":"+stock_data[key][element]); // プ
                    row.append(process_stock);
                    for (let a = 0; a < dateArray.length; a++) {
                        let  info_data_quantity = info_data[key][element][a];
                        let info
                        if(info_data_quantity == 0)
                        {
                            info =  $('<td colspan="2"></td>').text("　"); // プ
                        }
                        else
                        {
                            info =  $('<td colspan="2"></td>').text("残" + info_data_quantity); // プ
                        }
                        row.append(info);
                    }
                    
                    //追加
                    $('#dataBody').append(row);
                });
            });   
            lead_time_coloring()
            in_work_cell()
            //ロード画面を非表示にする
            setTimeout(function() {
                // 処理が終わったら非表示にする
                $(".wrapper_div").fadeOut();
            }, 100); // 3秒後に非表示
        },
        error: function(xhr, status, error) {
            console.error('Error Status:', status); // エラーのステータス
            console.error('Error Message:', error);  // エラーメッセージ
            console.error('Response Text:', xhr.responseText); // サーバーからのレスポンステキスト
        }
    });
}
/////////////////////////////////////////////////////////////////
// リードタイム
/////////////////////////////////////////////////////////////////
function lead_time_coloring() {
    // 今日の日付と一致する日を見つける
    const today = new Date();
    const todayJST = new Date(today.toLocaleString('en-US', { timeZone: 'Asia/Tokyo' }));
    const base_day = todayJST.toISOString().split('T')[0];

    // テーブルに今日の日にちがあるかを探す
    let table = $("#my_table tr:first th");
    let match_th_col;
    table.each(function(j) {
        let th_day = $(this).attr('id');
        let th_date = new Date(th_day);
        let base_date = new Date(base_day);
        if (base_date <= th_date) {

            match_th_col = (base_date == th_date) ? j : j - 1;
            return false; // eachループを終了
        } 
    });
    // 工程ごとにリードタイムをつける
    let table_row = $("#my_table tbody tr td:nth-child(2)");
    let table_color = $("#my_table tbody tr");
    table_row.each(function(i) {
        let process = $(this).text();
        let read_time_day;

        if (process.includes("MC")) {
            if (process.includes("704MC")) {
                read_time_day = 3;
            } else {
                read_time_day = 5;
            }
        } else if (process.includes("組立")) {
            read_time_day = 4;
        } else if (process.includes("NC")) {
            read_time_day = 5;
        }
        //リードタイムの所を塗る
        if (read_time_day !== undefined) {
            for (let index = 0; index <= read_time_day; index++) {
                let cell = table_color.eq(i).find('td').eq(match_th_col + index);
                if (cell.text().trim() !== "") {
                    cell.addClass('read_time');
                }
            }
            //今日の日付より前にっ数量がある場所にいろを塗る
            for(let col = match_th_col; col >= 3; col--)
            {
                let cell = table_color.eq(i).find('td').eq(col);
                if (cell.text().trim() !== "") {
                    cell.addClass('read_time');
                }
            }
        }
    });
}
/////////////////////////////////////////////////////////////////
// 作業中のマスを青色に塗る
/////////////////////////////////////////////////////////////////
function in_work_cell() {
    //コントローラーからの値を読み取る
    var dataContainer = $('#data-container');
    var dataFromController = JSON.parse(dataContainer.attr('data-data'));
    //長期の始めの日を取得する
    //thのelement
    let table = $("#my_table tr:first th");
    let table_itemname_row = $("#my_table tbody tr td:nth-child(1)");
    let th_day = table.eq(4).attr('id'); // eq(4)を使って5番目のth要素を取得 

    let unmatched = false;
    let unmatched_arr = {};
    let item_row, row, col;
    //dataFromController
    dataFromController.forEach(element => {
        if (element['capture_date'] == th_day) {
            let delivery_date = element['delivery_date'];
            // 納期が遅延している場合の処理
            if (table.eq(3).text() == delivery_date) {
                col = 3;
            } else {
                // 納期が遅延していない場合の処理
                table.each(function (i) {
                    if (element['delivery_date'] == $(this).attr('id')) {
                        col = i ;
                    }
                });
            }


            // 親アイテム名とプロセスの一致を確認してクラスを追加
            let item_flag = false;
            table_itemname_row.each(function (i) {
                let item_name = $(this).text();
                if (item_name == element['parent_name']) {
                    item_row = i;
                    item_flag  = true
                    return false; // ループから抜ける
                }
            });

            if (item_row != undefined && item_flag ==true) {
                let table_tr_index = $("#my_table tbody tr");
                let numberOfRows = table_tr_index.length;

                for (let index = item_row; index < numberOfRows; index++) {
                    let process = table_tr_index.eq(index).find("td:nth-child(2)").text();
                    if (process == element['process']) {
                        row = index;
                        break;
                    }
                }
            }

            if (row != undefined && col != undefined && item_flag ==true) {

                $("#my_table tbody tr").eq(row).find('td').eq(col).addClass('in_work');
            }
            unmatched = false;
        } else {
            // 長期作成日が違った場合
            unmatched = true;
            addToUnmatchedArr(unmatched_arr, element['parent_name'], element['process'], element['processing_quantity']);
        }
    });
    if (Object.keys(unmatched_arr).length > 0) {
        Object.keys(unmatched_arr).forEach(parent_name => 
        {
            let item_flag = false;
            // 親アイテム名とプロセスの一致を確認してクラスを追加
            table_itemname_row.each(function (i) {
                let item_name = $(this).text();
                if (item_name == parent_name) {
                    item_flag = true;
                    item_row = i;
                    return false; // ループから抜ける
                }
            });

            // console.log(item_row)
            Object.keys(unmatched_arr[parent_name]).forEach(process_text => 
            {
                if (item_row !== undefined && item_flag == true) 
                {
                    let table_tr_index = $("#my_table tbody tr");
                    let numberOfRows = table_tr_index.length;
                    // console.log(numberOfRows);
                    for (let index = item_row; index < numberOfRows; index++) {

                        let process = table_tr_index.eq(index).find("td:nth-child(2)").text();
                        if (process === process_text) {
                            row = index;

                            break;
                        }
                    }
                    let col = 3;
                    // //ここで色を塗る
                    let td_table = $("#my_table tbody tr").eq(item_row+1).find('td');
                    // //色を塗るtdy要素
                    let color_td_table = $("#my_table tbody tr").eq(row).find('td');
                    let rows = td_table.length;                    
                    let remaining_quantity = Number(unmatched_arr[parent_name][process_text]);
                    for(let i = rows - 1; i >= col; i--){
                        let quantity = td_table.eq(i).text();
                        let item_quantity = color_td_table.eq(i+1).text().replace('残', '');
                        // let item_quantity = color_td_table.eq(i+1).text();
                        if(quantity != "" &&  (Number(quantity) != Number(item_quantity)))
                        {
                            quantity = Number(quantity);
                            if (remaining_quantity -= quantity >= 0) {

                                color_td_table.eq(i+1).addClass('in_work');
                            }
                            remaining_quantity -= quantity;
                            // 残りの数量が0未満になったらループを終了する場合は以下のブロックをアンコメントしてください
                            if (remaining_quantity < 0) {
                                break;
                            }
                        }
                    }
                }
            });
        });
    }

}
function addToUnmatchedArr(unmatched_arr, parent_name, process, processing_quantity) {
    // 親名がunmatched_arrに存在するかどうかを確認
    if (!unmatched_arr.hasOwnProperty(parent_name)) {
        unmatched_arr[parent_name] = {};
    }
    
    // プロセスが親名の下に存在するかどうかを確認
    if (!unmatched_arr[parent_name].hasOwnProperty(process)) {
        unmatched_arr[parent_name][process] = 0;
    }
    
    // 処理数量を追加
    unmatched_arr[parent_name][process] += processing_quantity;
}