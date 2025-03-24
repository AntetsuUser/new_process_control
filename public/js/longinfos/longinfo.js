/////////////////////////////////////////////////////////////////
// ページが読み込まれたら
/////////////////////////////////////////////////////////////////
$(function()
{   
        
    //リードタイムの色を塗る
    lead_time_coloring();
    //選択可能な行に色を塗る
    selectable_area_coloring();
    let selection_elements;
    //作業中のマスを青く
    in_work_cell();
    //平準化のマスを黄色く
    LevelingPrediction(base_ability);
    let local_ip= "111.111.111.111";
    getLocalIP().then(ip => {
        // console.log('ローカルIPアドレス:', ip);
        local_ip = ip;
        $('#local_ip').val(local_ip)
    }).catch(error => {
        // console.error('エラーが発生しました:', error);
        $('#local_ip').val(local_ip)
    });
    
});

/////////////////////////////////////////////////////////////////
// 作業中のマスを青色に塗る
/////////////////////////////////////////////////////////////////
function in_work_cell() {
    //コントローラーからの値を読み取る
    var dataContainer = $('#data-container');
    var dataFromController = JSON.parse(dataContainer.attr('data-data'));
    //長期の始めの日を取得する
    //thのelement
    let table = $("#info_table tr:first th");
    let table_itemname_row = $("#info_table tbody tr td:nth-child(1)");
    let th_day = table.eq(4).attr('id'); // eq(4)を使って5番目のth要素を取得 

    let unmatched = false;
    let unmatched_arr = {};
    let item_row, row, col;
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
            let select_process = "";
            if (item_row != undefined && item_flag ==true) {
                let table_tr_index = $("#info_table tbody tr");
                let numberOfRows = table_tr_index.length;

                for (let index = item_row; index < numberOfRows; index++) {
                    let process = table_tr_index.eq(index).find("td:nth-child(2)").text();
                    console.log(process);
                    console.log(element['process']);
                    if (process == element['process']) {
                        row = index;
                        select_process = process;
                        break;
                    }
                    
                }
            }

            if (row != undefined && col != undefined && item_flag ==true && select_process == element['process']) {

                $("#info_table tbody tr").eq(row).find('td').eq(col).addClass('in_work');
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
                    let table_tr_index = $("#info_table tbody tr");
                    let numberOfRows = table_tr_index.length;

                    for (let index = item_row; index < numberOfRows; index++) {
                        let process = table_tr_index.eq(index).find("td:nth-child(2)").text();
                        if (process === process_text) {
                            row = index;
                            break;
                        }
                    }
                    let col = 3;
                    //ここで色を塗る
                    let td_table = $("#info_table tbody tr").eq(item_row+1).find('td');
                    //色を塗るtdy要素
                    let color_td_table = $("#info_table tbody tr").eq(row).find('td');
                    let rows = td_table.length;                    
                    let remaining_quantity = Number(unmatched_arr[parent_name][process_text]);
                    for(let i = rows - 1; i >= col; i--){
                        let quantity = td_table.eq(i).text();
                        let item_quantity = color_td_table.eq(i).text().replace('残', '');
                        if(quantity != "" &&  (Number(quantity) != Number(item_quantity)))
                        {
                            quantity = Number(quantity);
                            if (remaining_quantity -= quantity >= 0) {

                                color_td_table.eq(i).addClass('in_work');
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



/////////////////////////////////////////////////////////////////
// リードタイム
/////////////////////////////////////////////////////////////////
function lead_time_coloring() {
    // 今日の日付と一致する日を見つける
    const today = new Date(); 
    const todayJST = new Date(today.toLocaleString('en-US', { timeZone: 'Asia/Tokyo' }));
    const base_day = todayJST.toISOString().split('T')[0];

    // テーブルに今日の日にちがあるかを探す
    let table = $("#info_table tr:first th");
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
    let table_row = $("#info_table tbody tr td:nth-child(2)");
    let table_color = $("#info_table tbody tr");
    table_row.each(function(i) {
        let process = $(this).text();
        let read_time_day;

        if (process.includes("MC")) {
            if (process.includes("704MC")) {
                read_time_day = 3;
            } else {
                read_time_day = 4;
            }
        } else if (process.includes("組立")) {
            read_time_day = 3;
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
            //今日の日付より前に数量がある場所にいろを塗る
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
// 選択できる場所の背景色を塗る
/////////////////////////////////////////////////////////////////
function selectable_area_coloring()
{
    let line_number = line+numbers;
    // console.log(line_number);
    let process_able_area_json = $('#process_able_area').val();
    var process_able_area = JSON.parse(process_able_area_json);
    // console.log(process_able_area);
    let table_row_1 = $("table tbody tr td:nth-child(1)");
    table_row_1.each(function(i) {
        let td = $(this); // 現在の <td> 要素
        let item_name = td.text().trim(); // 文字列の前後の空白を削除

        // 文字があり、かつ info_item クラスが含まれている場合
        if (item_name.length > 0 && td.hasClass("info_item")) {

             // item_name が selectitem のキーに存在しているかチェック
            if (selectitem.hasOwnProperty(item_name)) {
                // info_backcolorが見つかるまで下に行く
                let table_row_2 = $("table tbody tr td:nth-child(2)");
                let item_values = selectitem[item_name];
                // 各行を順に処理して、info_backcolor クラスがついているかをチェック
                table_row_2.slice(i+2).each(function(j) {
                    let current_td = $(this);
                    let current_td_text = current_td.text().trim(); // 文字列の前後の空白を削除
                    // console.log(current_td_text);

                    // info_backcolorクラスがついているかをチェック
                    if (current_td.hasClass("info_backcolor")) {
                        // current_td_text
                          return false; // info_backcolorが見つかったらループを抜ける
                    }
                    else
                    {
                        if(current_td_text.includes('/'))
                        {   
                            //工程の/を分解
                            var process_arr = splitAndProcess(current_td_text);
                            process_arr.forEach(function(element) {
                                // process_arr 内の各要素が item_values に含まれているかチェック
                                // console.log(element);
                                // console.log(item_values);
                                if (item_values.includes(element)) {
                                    // console.log("一致しました: ", element);  // 一致した場合に表示
                                    // console.log($(this));
                                    current_td.closest('tr').addClass('set_cel'); // 親の行にクラスを追加
                                    current_td.addClass('set_cel'); // 現在の <td> にクラスを追加
                                }
                            });
                            
                        }else{
                            //工程と可能エリアが一致したらclassを付与
                            if(item_values.includes(current_td_text))
                            {
                                $(this).closest('tr').addClass('set_cel');
                                // 現在のtdにクラスを追加
                                $(this).addClass('set_cel');
                            }
                        }  
                    }   
                });
            }
        }
    });
}   


/////////////////////////////////////////////////////////////////
// 平準化のマスを黄色く塗る
/////////////////////////////////////////////////////////////////
function LevelingPrediction(base_ability) {

    console.log(base_ability);
    let today = new Date(); 
    let formattedDate = today.getFullYear() + '-' 
                        + (today.getMonth() + 1).toString().padStart(2, '0') + '-' 
                        + today.getDate().toString().padStart(2, '0');
    // console.log(formattedDate);  // 例: 2025-01-29

    //日付けと一致するthのcolumnindexを求める
    // テーブルに今日の日にちがあるかを探す
    let table = $("#info_table tr:first th");
    let match_th_col = 0;
    for (let j = table.length - 1; j >= 0; j--) {
        let th_day = $(table[j]).attr('id');
        let th_date = new Date(th_day);
        let formattedth_date = th_date.getFullYear() + '-' 
                        + (th_date.getMonth() + 1).toString().padStart(2, '0') + '-' 
                        + th_date.getDate().toString().padStart(2, '0');
        //  console.log(formattedDate,formattedth_date);
        if (formattedDate >= formattedth_date) {
            match_th_col = (formattedDate == formattedth_date) ? j : j + 1;
            break; // ループを終了
        }
    }
    // console.log(match_th_col);
    let baseability = 0;
    let process =0;
    Object.entries(base_ability).forEach(([process, items]) => {
        // processによって処理を分ける
        if (process === "704MC") {
            Object.entries(items).forEach(([item_name, ability]) => {
                end = match_th_col + 3

                processItem(item_name, ability,end,process) 
            });
        } else if (process === "102NC/MC") {
            // 終了日付今日から4日後
            end = match_th_col + 4
            Object.entries(items).forEach(([item_name, ability]) => {
                processItem(item_name, ability,end,process) 
            });

        } else if (process === "103NC/MC") {
            // 終了日付今日から4日後
            end = match_th_col + 4
            Object.entries(items).forEach(([item_name, ability]) => {
                processItem(item_name, ability,end,process) 
            });
            
        } else if (process === "組立") {
            // 他のprocessの場合の処理
            end = match_th_col + 3
            Object.entries(items).forEach(([item_name, ability]) => {
                processItem(item_name, ability,end,process) 
            });
        } else {
            // その他の処理
        }
    });
}

function processItem(item_name, ability,end,process) 
{

    let table_row_arr = [];

    // 品番の能力
    let baseability = ability;

    // console.log(`Item: ${item_name}, Ability: ${ability}`);
    let tbody = $("#info_table tbody");

    // 品番を検索してヒットしたindexを保存
    tbody.find('tr').each((index, row) => {
        // 最初の<td>の内容を取得
        let firstTd = $(row).find('td').first();

        // firstTdが存在し、item_nameが含まれている場合
        if (firstTd.length && firstTd.text().includes(item_name)) {
            // console.log(`品番 '${item_name}' 行番号 ${index + 1}`);

            if(process == "組立" || process == "704MC")
            {
                // 2番目の列を取得
                // ヒットしたindexから下に進めてset_celクラスのあるtrを検索
                tbody.find('tr').slice(index + 1).each((j, nextRow) => {
                    // set_celクラスが付いている行をチェック
                    let current_row = $(nextRow);
                    // console.log(current_row);
                    let secondTd = current_row.find("td:nth-child(2)").text().trim();
                    // console.log(secondTd);
                    // console.log(process);
                    if (process == secondTd) {
                        table_row_arr.push(index + j +1);
                        return false; // `each` ループを抜けるs
                    }
                });
            }else
            {
                table_row_arr.push(index + 1);
            }
        }
    });

    // console.log(table_row_arr);
    // 日付のIDが含まれている列番号を取得
    let columnIndexes = [];  // 列番号を格納する配列
    let column_id = [];
    $('#info_table thead th').each(function(index) {
        let th = $(this);
        let thId = th.attr('id');  // id属性の取得
        if (thId) {  // idが存在する場合
            columnIndexes.push(index);  // 列番号を配列に追加
            column_id.push(thId);　//id
        }
    });
    let  col_total = 0;
    // 列番号（columnIndexes）を外側のループにし、行番号（table_row_arr）を内側のループに変更
    for (let colIndex of columnIndexes.reverse()) {
        //列番号の合計
        let thId = $("#info_table thead th").eq(colIndex).attr('id');
        
        if (end == colIndex) {
            // console.log(col_total);
            // console.log(`colIndexとthIdが一致しました。ループを終了します。`);
            //ここでcol_totalが残っていた場合
            if(col_total > 0)
            {
                // console.log("間に合いませんよ");
                // console.log(col_total);
                break;  // ループを終了
            }
            break;  // ループを終了
        }
        // console.log(`列番号: ${colIndex + 1}, thのid: ${thId}`);
        
        // 行番号（table_row_arr）のループ
        table_row_arr.forEach((rowIndex) => {

            // tbody内の指定された行と列に対応する<td>を取得
            let td = $("#info_table tbody tr").eq(rowIndex).find("td").eq(colIndex);
            // <td>が存在する場合、そのテキスト内容を取得
            if (td.length && td.hasClass("tap_day")) {
                let textContent = td.text().trim(); // 空白を除去
                if (textContent.length > 0) { // 文字が入っているか確認
                    let numberMatch = textContent.match(/\d+/); // 数値部分を取得
                    let number = numberMatch ? parseInt(numberMatch[0], 10) : null; // 数値に変換
                    if (number !== null) {
                        col_total = parseInt(col_total) + parseInt(numberMatch);
                    }
                }
            }
        });
        let ability_result = 0;
        let carryOver = 0; // 持ち越し用の変数
        // 能力 - 一日の合計
        ability_result = col_total - baseability;
        // 持ち越しが必要な場合
        if (ability_result >= 0) {
            carryOver = ability_result;  // 余った分を持ち越しに保存
            col_total = carryOver;       // col_totalには持ち越しを適用
            // console.log("持ち越し: " + carryOver);
        } else {
            col_total = 0;  // 負の値の場合は 0 にリセット
            carryOver = 0;   // 持ち越しは 0 にリセット
        }

    }
    if(col_total > 0)
    {
        console.log("間に合ってない品番");
        console.log(item_name);
        console.log(table_row_arr);
        console.log(columnIndexes.reverse());
        console.log(end);
        // columnIndexes
    }

}


/////////////////////////////////////////////////////////////////
// テーブルセルがタップされたら
/////////////////////////////////////////////////////////////////
$(document).on('click', 'table tbody tr td:nth-child(n+4)', function() 
{
    //モーダル
    const modal = $('#easyModal');
    // セルのテキストを取得
    let cellText = $(this).text().trim();
    //trにset_celは含まれているか？
    let rowClass = $(this).closest('tr').hasClass('set_cel');
    let read_time_flag ;
    let remaining_count_flag ;
    //リードタイムチェック
    read_time_flag = read_time_check($(this))

    remaining_count_flag = remaining_count_check($(this))
        

    // セルのテキストが空でない場合かつ、'残'の文字が含まれている場合の処理
    if (cellText !== "" && cellText.includes('残') && rowClass == true && read_time_flag == false && remaining_count_flag == false) {
        selection_elements = $(this);
        console.log("if");
        //長期数量を表示
        // Maxロットを取得し表示
        let maxlot = $(this).closest('tr').find('td:first-child p').text().trim();
        //選択したところに()が付いているか
        if (cellText.includes('(') && cellText.includes(')')) 
        {
            //残数に()ですでに入力されてる数字があるとき
            //()の中の数字をinoutのvalueに
            let cancel_lot = cellText.match(/\((.+)\)/)[1]
            cancel_lot = parseInt(cancel_lot)
            var match = cellText.match(/残\d+/);
            $("#info_num").text(match);

            let parts = maxlot.split('/');
            let input_lot = parts[0].trim(); // スラッシュの前の部分
            let max = parts[1].trim();  // スラッシュの後の部分
            input_lot = input_lot - cancel_lot
            //maxlot =  maxlot[0] - cancel_lot 
            maxlot = `${input_lot} / ${max}`;

            $("#lot_number").text(maxlot);
            $('#num_decision').val(cancel_lot); 
            console.log("数量："+ input_lot　);
        }
        else
        {
            $('#num_decision').val(0); 
            $("#info_num").text(cellText);
            $("#lot_number").text(maxlot);
        }

        selection_elements.closest('tr').find('td:first-child p').text(maxlot);

        //------------ログに入れるために工程と品番を取得してくる---------------------
        /** 工程*/
         let table_th_row = $("#info_table tr:first th");
        let tappedElement = selection_elements[0];
        // 行 (tr) を取得
        let row = $(tappedElement).closest('tr');
        // 同じ行の2列目のセルを取得
        let secondColumn = row.find('td').eq(1);
         // セルのテキストを取得
        let secondColumnText = secondColumn.text().trim();
        let item_code ;
        // 現在の行のインデックスを取得
        let tr_index = row.index();
        // tr内のtdのインデックスを取得
        let tappedElementJQ = $(selection_elements[0]);
        let td_index = tappedElementJQ.index();

        // 1行目までループして検索
        for (let index = tr_index; index >= 0; index--) {
            // 現在の行を取得
            let currentRow = row.parent().find('tr').eq(index);
            // 1列目のセルを取得
            let element = currentRow.find('td').eq(0);

            // elementが存在することを確認
            if (element.length) {
                // クラスをチェック
                if (element.hasClass('info_item')) {
                    item_code = element.text().trim();
                    break; // 条件を満たしたらループを終了
                }
            }
        }
        let delivery_date ;
        //選択されたセルの納期を取得
        if(table_th_row.eq(td_index).text() == "遅延")
        {
            delivery_date = "遅延"
        }else{
            delivery_date = table_th_row.eq(td_index).attr('id');
        }
        console.log(item_code);
        //工程
        console.log(secondColumnText);
        //長期の残り数量
        console.log(cellText);
        //選択されているロット/Maxロット
        console.log(maxlot);
        console.log(delivery_date);

        cellinfo_log(item_code,secondColumnText,delivery_date,cellText,maxlot,log_selectcell_url)
        //secondColumnText
        modal.css('display', 'block');
        modal_log("数量選択","表示されました",log_modal_url);
        //ここでタップした内容を取得してくる
        //選択した内容「品番」「工程」「納期」「長期数量」「ロット数」

        
    }
});

/////////////////////////////////////////////////////////////////
// Maxボタンが押されたら
/////////////////////////////////////////////////////////////////
$('#max_btn').on('click',function(){
    btnlog("Max",log_submit_url)
    //入力された数
    let input_value = $('#num_decision').val(); 
	//ロット数を表示
	let lot_number = $('#lot_number').text(); 
	//長期の残り数量
	let info_num  =  $('#info_num').text(); 
    // "info_num" から "残" を削除
    info_num = info_num.replace('残', '').trim();
    // スラッシュで文字列を分割
    let parts = lot_number.split('/');
	let input_lot = parts[0].trim(); // スラッシュの前の部分
	let max_lot = parts[1].trim();  // スラッシュの後の部分
    //数字に変換
	input_lot = parseInt(input_lot)
	max_lot = parseInt(max_lot)
	info_num  = parseInt(info_num)

    //後どれだけ入力できるか計算する
	let count_value = max_lot - input_lot
    if(info_num != input_value)
	{
		if(count_value >= info_num)
		{	
			input_count = info_num
		}
		else
		{
			input_count = count_value
		}
	}

    //MAXの合値をlogに書き込む
    maxlog(input_count,log_maxbtn_url);
	// input_countの値を<input>要素に設定
    $('#num_decision').val(input_count);
});
/////////////////////////////////////////////////////////////////
// モーダルの決定ボタンが押されたとき
/////////////////////////////////////////////////////////////////
$('#decision_btn').on('click',function()
{
    btnlog("決定",log_submit_url);
    //入力された数
    let input_value = $('#num_decision').val(); 
    //長期の残り数量
	let info_num  =  $('#info_num').text(); 
    // "info_num" から "残" を削除
    info_num = info_num.replace('残', '').trim();
    if (info_num.includes('(') && info_num.includes(')')) 
    {
        info_num = info_num.match(/残\d+/);
    }
    //選択中の要素の最初のtd
    let lot =  selection_elements.closest('tr').find('td:first-child p').text().trim();

    
    let parts = lot.split('/');
	let input_lot = parts[0].trim(); // スラッシュの前の部分
    let max_lot = parts[1].trim();  // スラッシュの後の部分
    //数字に変換
	input_lot = parseInt(input_lot);
    input_value = parseInt(input_value);
    max_lot = parseInt(max_lot);
    info_num  = parseInt(info_num)
    let stock_check_flag = stock_check(selection_elements,input_value);
    //在庫数を入力した値が超えていないか
    if(stock_check_flag == false)
    {
        alert("在庫がありません確認してください");
    }
    else
    {
        //入力された数字がMaxを超えていないか確認
        if(max_lot >= info_num)
        {
            //maxロットが長期数量よりデカい場合
            //残りの入力できる数を計算する
            if(info_num < input_value || input_value < 1)
            {
                alert("入力した値を確認してください");
            }else
            {
                input_lot = input_lot + input_value
                let new_lot_number = `${input_lot}/${max_lot}`;
                selection_elements.closest('tr').find('td:first-child p').text(new_lot_number);
                let info_value = $('#info_num').text(); 
                selection_elements.text(info_value + "(" + input_value + ")");
                if (!selection_elements.hasClass('selected')) {
                    selection_elements.addClass('selected');
                }
                $('#easyModal').css('display', 'none');
                modal_log("数量選択","非表示になりました",log_modal_url);
            }
        }
        else
        {
            //maxロットが長期数量より小さい場合
            if(max_lot < input_value || input_value < 1)
            {
                alert("入力した値を確認してください");
                modal_log("数量選択","非表示になりました",log_modal_url);
            }
            else
            {
                input_lot = input_lot + input_value
                let new_lot_number = `${input_lot}/${max_lot}`;
                selection_elements.closest('tr').find('td:first-child p').text(new_lot_number);
                let info_value = $('#info_num').text(); 
                selection_elements.text(info_value + "(" + input_value + ")");
                if (!selection_elements.hasClass('selected')) {
                    selection_elements.addClass('selected');
                }
            }
            $('#easyModal').css('display', 'none');
            modal_log("数量選択","非表示になりました",log_modal_url);
        }
    }
})

/////////////////////////////////////////////////////////////////
// モーダルの取り消しボタンが押されたとき
/////////////////////////////////////////////////////////////////
$('#cancel_btn').on('click',function(){
    btnlog("取り消し",log_submit_url);
   // モーダルを非表示にする
    let cellText = selection_elements.text().trim();
    let maxlot =  selection_elements.closest('tr').find('td:first-child p').text().trim();
    if (cellText.includes('(') && cellText.includes(')')) 
    {
        let cancel_lot = cellText.match(/\((.+)\)/)[1]
        cancel_lot = parseInt(cancel_lot)
        var match = cellText.match(/残\d+/);

        selection_elements.text(match);
        selection_elements.toggleClass('selected');
        $('#easyModal').css('display', 'none');
        modal_log("数量選択","非表示になりました",log_modal_url);
    }
})

/////////////////////////////////////////////////////////////////
// モーダルキャンセルボタンが押されたら　モーダルを閉じる
/////////////////////////////////////////////////////////////////
$('#close_btn').on('click', function() {
    btnlog("キャンセル",log_submit_url);
    // モーダルを非表示にする
    let cellText = selection_elements.text().trim();
    let maxlot =  selection_elements.closest('tr').find('td:first-child p').text().trim();
    if (cellText.includes('(') && cellText.includes(')')) 
    {
        let cancel_lot = cellText.match(/\((.+)\)/)[1]
        cancel_lot = parseInt(cancel_lot)
        var match = cellText.match(/残\d+/);
        $("#info_num").text(match);

        let parts = maxlot.split('/');
        let input_lot = parts[0].trim(); // スラッシュの前の部分
        let max = parts[1].trim();  // スラッシュの後の部分
        //数字に変換
        input_lot = parseInt(input_lot);
        cancel_lot = parseInt(cancel_lot);
        max = parseInt(max);
        input_lot = input_lot + cancel_lot
        //maxlot =  maxlot[0] - cancel_lot 
        maxlot = `${input_lot}/${max}`;

        selection_elements.closest('tr').find('td:first-child p').text(maxlot);
    }
    $('#easyModal').css('display', 'none');
    modal_log("数量選択","非表示になりました",log_modal_url);
});

/////////////////////////////////////////////////////////////////
// 印刷ボタンを押したとき
/////////////////////////////////////////////////////////////////
$('#print').on('click',function(){
    btnlog("印刷",log_submit_url)
    var allHaveClass = false;
    var className = 'selected'; // 確認したいクラス名
    let select_arr = [];
    let ip = $('#local_ip').val()
    $('#info_table td').each(function(index) {
        if ($(this).hasClass(className)) {
            allHaveClass = true;
            arr = [];
            //テーブルのth   
            let table_th_row = $("#info_table tr:first th");
            //テーブルのtbodyのtr
            let table_row = $("#info_table tbody tr");
            let select_cell = $(this);
            // このtdが属しているtrを取得
            let parent_tr = select_cell.closest('tr');
            // table内のtrのインデックスを取得
            let tr_index = parent_tr.index();
            // tr内のtdのインデックスを取得
            let td_index = select_cell.index();

            //品目コード-----------------------------------------
            let item_code
            for (let index = tr_index; index > 0; index--) {
                let element = table_row.eq(index-1).find('td').eq(0);
                // elementがjQueryオブジェクトであることを確認
                if (element.length) {
                    // クラスをチェック
                    if (element.hasClass('info_item')) {
                        item_code = element.text();
                        break;
                    }
                }
            }
            //工程---------------------------------------------
            //選択されたセルの工程を取得
            let process = table_row.eq(tr_index).find('td').eq(1).text()
            // '設備番号'の値を取得
            let lineNumbers = $('input[name="line_numbers"]').val();

            // '作業者'の値を取得
            let workersid = $('input[name="workers"]').val();
            //着手日
            // 現在の日付を取得
            let today = new Date();
            // 年、月、日を取得
            let year = today.getFullYear();
            let month = ('0' + (today.getMonth() + 1)).slice(-2); // 月は0から始まるため+1
            let day = ('0' + today.getDate()).slice(-2);
            // 日付をYYYY-MM-DD形式にフォーマット
            let formattedDate = `${year}-${month}-${day}`;

            //工程番号---------------------------------------------
            let process_number;
            let for_count = 6;
            switch (true) {
                case process.toString().includes('102') && process.toString().includes('MC'):
                    process_number = 2;
                    for_count = 2;
                    break;
                case process.toString().includes('102'):
                    process_number = 1;
                    for_count = 1;
                    break;
                case process.toString().includes('103') && process.toString().includes('MC'):
                    process_number = 4;
                    for_count = 4;
                    break;
                case process.toString().includes('103'):
                    process_number = 3;
                    for_count = 3;
                    break;
                case process.toString().includes('組立'):
                    process_number = 5;
                    for_count = 5;
                    break;
                case process.toString().includes('704'):
                    process_number = 6;
                    for_count = 6;
                    break;
                default:
                    process_number = 0; // エラー処理など、デフォルトの場合の設定
                    break;
            }



        
            //加工数-----------------------------------------
            let cellText  = $(this).html();
            let processing_quantity = cellText.match(/\((.+)\)/)[1]
            processing_quantity = parseInt(processing_quantity)
            //長期全数--------------------------------------------
            let long_term_all = 0; // デフォルト値を設定
            for (let index = tr_index; index > 0 && index > tr_index - for_count -1; index--) {
                let element = table_row.eq(index).find('td').eq(td_index);
                // elementがjQueryオブジェクトであることを確認
                if (element.length) {
                    // クラスをチェック
                    if (element.hasClass('info_count')) {
                        long_term_all = element.text();
                        break;
                    }
                }
            }



            // 加工した数--------------------------------------------
            let count = cellText.substring(0, cellText.indexOf("("))
            count = count.replace("残", "");
            let processing_all = long_term_all - count + processing_quantity
            //納期--------------------------------------------
            //選択されたセルの納期を取得
            if(table_th_row.eq(td_index).text() == "遅延")
            {
                delivery_date = "遅延"
            }else{
                delivery_date = table_th_row.eq(td_index).attr('id');
            }

            


            // 配列に格納
            //[品目コード、工程、納期、着手日、加工数、今まで何個加工したか、長期数量、設備番号、作業者id,工程番号]
            // select_arr.push([item_code,process,delivery_date,formattedDate,processing_quantity,processing_all,long_term_all,lineNumbers,workersid,process_number]);
            //[品目コード、工程、納期、着手日、加工数、今まで何個加工したか、長期数量、設備番号、作業者id,工程番号]
            select_arr.push([item_code,process,delivery_date,formattedDate,processing_quantity,long_term_all,lineNumbers,workersid,process_number,ip]);
            //指示書にする情報をログに送信する
            
        }

    });
    if(allHaveClass)
    {
        let form = $('<form>', {
            action: '/longinfo/print_post',
            method: 'post'
        });
        // CSRFトークンをフォームに追加
        let csrfToken = $('meta[name="csrf-token"]').attr('content');
        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: csrfToken
        }));
        // 隠しフィールドをフォームに追加
        form.append($('<input>', {
            type: 'hidden',
            name: 'line',
            value: line
        }));
        form.append($('<input>', {
            type: 'hidden',
            name: 'numbers',
            value: numbers
        }));
        form.append($('<input>', {
            type: 'hidden',
            name: 'factory',
            value: factory
        }));
        form.append($('<input>', {
            type: 'hidden',
            name: 'department',
            value: department
        }));
        // データ配列をフォームに追加
    
        select_arr.forEach(function(item, index) {
            form.append($('<input>', {
                type: 'hidden',
                name: 'data[]',
                value: item
            }));
        });

        // フォームをボディに追加して送信
        form.appendTo('body').submit();
    }
    else
    {
        alert("選択されていません");
    }

})
/////////////////////////////////////////////////////////////////
// 工程の/で分解する関数
/////////////////////////////////////////////////////////////////
function splitAndProcess(input) {
    // 正規表現パターンにマッチする部分を抽出
    var regex = /(\d+)([A-Za-z]+)/g;
    var matches = regex.exec(input);

    if (matches && matches.length === 3) {
        // 数字をMCの後ろに追加して配列に格納して返す
        var arr = [
            matches[1] + matches[2], // "102NC" => "102NC"
            matches[1] + "MC"       // "MC" => "102MC"
        ];
        return arr;
    } else {
        return null; // マッチしなかった場合はnullを返すなどの処理が必要かもしれません
    }
}
/////////////////////////////////////////////////////////////////
// タップした前の日にリードタイムクラスがあるか調べる
//タップされた箇所の要素が渡される
/////////////////////////////////////////////////////////////////
function read_time_check(selection_elements) {
    var previousTds = selection_elements.prevAll('td');
    
    var hasReadTimeBefore = previousTds.filter('.read_time').length > 0;
    
    if (selection_elements.hasClass('selected')) {
        return false;
    }
    
    if (hasReadTimeBefore) {
        return !previousTds.filter('.read_time').first().hasClass('selected');
    }
    
    return hasReadTimeBefore;
}
/////////////////////////////////////////////////////////////////
// タップした前の日に残りの数があるか調べる
//タップされた箇所の要素が渡される
/////////////////////////////////////////////////////////////////
function remaining_count_check(selection_elements) {
    var previousTds = selection_elements.prevAll('td');

    // 最後の3つを除外
    var filteredTds = previousTds.slice(0, Math.max(previousTds.length - 3, 0));
    

    // テキストが文字以上入っている td を取得
    var nonEmptyTds = filteredTds.filter(function() 
    {
        return $(this).text().trim().length > 0; // 3文字以上のテキストを持つ td
    });

    console.log(nonEmptyTds.length);

    if(nonEmptyTds.length >= 1)
    {
        return true;
    }
    else
    {
        return false;
    }
}

/////////////////////////////////////////////////////////////////
//一つ目の工程の在庫をチェックして選択できるか判断する
//在庫チェック
/////////////////////////////////////////////////////////////////
function stock_check(selection_elements, input_value) {
    let result = true; // 結果を保持する変数

    $(selection_elements).each(function() {
        var parent_tr = $(this).closest('tr'); // 現在のtdの親trを取得
        var second_column_value = $(this).parent().find('td:eq(1)').text(); // 2列目のテキストを取得

        if (second_column_value == '704MC') {
            // 704MCなら
            var stock_text = parent_tr.prev().find('td:eq(2)').text(); // 一つ上のtrの2列目の在庫のテキストを取得
            let stock_quantity = text_conversion(stock_text);
            if (stock_quantity < input_value) {
                result = false; // 在庫が足りない場合は結果をfalseに設定
                return false; // eachのループを終了
            }
        } else if (second_column_value == '組立') {
            result = checkStockAvailability(parent_tr, input_value);
            console.log(result);
            if (!result) {
                return false; // eachのループを終了
            }
        } else {
            // 102 or 103のNC/MC
            if (second_column_value.includes('/')) {
                // if (second_column_value.includes("102")) {
                //     var stock_text = parent_tr.prev().prev().find('td:eq(2)').text();
                //     let stock_quantity = text_conversion(stock_text);
                //     if (stock_quantity < input_value) {
                //         result = false; // 在庫が足りない場合は結果をfalseに設定
                //         return false; // eachのループを終了
                //     }
                // } else if (second_column_value.includes("103")) {
                //     var stock_text = parent_tr.prev().prev().find('td:eq(2)').text();
                //     let stock_quantity = text_conversion(stock_text);
                //         console.log(stock_quantity,input_value);
                //     if (stock_quantity < input_value) {
                //         result = false; // 在庫が足りない場合は結果をfalseに設定
                //         return false; // eachのループを終了
                //     }
                // }
            } else {
                console.log("/含みません");
                if (second_column_value.includes("102")) {
                    var stock_text = parent_tr.prev().prev().find('td:eq(2)').text();
                    let stock_quantity = text_conversion(stock_text);
                    if (stock_quantity < input_value) {
                        result = false; // 在庫が足りない場合は結果をfalseに設定
                        return false; // eachのループを終了
                    }
                } else if (second_column_value.includes("103")) {
                    var stock_text = parent_tr.prev().prev().find('td:eq(2)').text();
                    let stock_quantity = text_conversion(stock_text);
                        console.log(stock_quantity,input_value);
                    if (stock_quantity < input_value) {
                        result = false; // 在庫が足りない場合は結果をfalseに設定
                        return false; // eachのループを終了
                    }
                }
            }
        }
    });

    return result; // 最終的な結果を返す
}

/////////////////////////////////////////////////////////////////
//テキストから在庫数だけを取って返す
/////////////////////////////////////////////////////////////////
function text_conversion(text)
{
    var colonIndex = text.indexOf("：");
    var colonIndex_var2 = text.indexOf(":");
    // コロンの位置が文字列内にある場合、右側の文字列を削除
    if (colonIndex !== -1) {
        var previous_stock = text.substring(colonIndex + 1);
        previous_stock = Number(previous_stock)
    }
    else if(colonIndex_var2!== -1)
    {
        var previous_stock = text.substring(colonIndex_var2 + 1);
        previous_stock = Number(previous_stock)
    }
    return previous_stock
}
/////////////////////////////////////////////////////////////////
//組立の場合　テキストから在庫数
/////////////////////////////////////////////////////////////////
function checkStockAvailability(parent_tr,input_value) 
{
    console.log(parent_tr);
    var oneAboveTr = parent_tr.prev('tr');
    var twoAboveTr = parent_tr.prev('tr').prev('tr');
    var secondTdText = oneAboveTr.find('td').eq(2).text();
    var secondTdTextTwoAbove = twoAboveTr.find('td').eq(2).text();

    console.log(secondTdText,secondTdTextTwoAbove);
    // 在庫数量を探す関数
    function findStockQuantity(prevRows, code) {
        var codeStr = String(code);  // code を文字列に変換
        // code と 'MC' を含む2番目の <td> を見つける
        var found_td = prevRows.find('td:eq(1)').filter(function() {
            return $(this).text().includes(codeStr) && $(this).text().includes('MC');
        }).first();

        // 見つかった場合は、同じ <tr> 内の3番目の <td> のテキストを取得
        if (found_td.length > 0) {
            var stock_text = found_td.closest('tr').find('td:eq(2)').text();
            return text_conversion(stock_text);
        }

        // 見つからない場合は 0 を返す
        return 0;
    }

    var stock_quantity_102 = findStockQuantity(oneAboveTr, "103");
    var stock_quantity_103 = findStockQuantity(twoAboveTr, "102");
    console.log(stock_quantity_102, stock_quantity_103);
    if (stock_quantity_102 < input_value || stock_quantity_103 < input_value) {
        return false;
    }

    return true;
}
////色のア悦明モーダル
var open = $('#help_btn'),
	close = $('.help_modal-close'),
	container = $('.help_modal-container');

//開くボタンをクリックしたらモーダルを表示する
open.on('click',function(){	
    btnlog("色説明",log_submit_url)
	container.addClass('active');
    modal_log("色説明","表示されました",log_modal_url);
	return false;
});

//閉じるボタンをクリックしたらモーダルを閉じる
close.on('click',function(){	
	container.removeClass('active');
    modal_log("色説明","非表示になりました",log_modal_url);
});

//モーダルの外側をクリックしたらモーダルを閉じる
$(document).on('click',function(e) {
	if(!$(e.target).closest('.help_modal-body').length) {
         if (container.hasClass('active')) {
            // ここにremoveClassが呼ばれる前の処理を書く
            console.log('activeクラスが削除される前の処理');
            modal_log("色説明","非表示になりました",log_modal_url);
        }
		container.removeClass('active');
	}
});


// モーダルが開いたり閉じたりしたときにlogに送る
//引数
//1　どこのモーダルか
//2　開いたか閉じたか
// モーダルが開いたり閉じたりしたときにlogに送る
function modal_log(modal_location,status,log_modal_url)
{
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    console.log(modal_location,status,log_modal_url);
    // Ajaxリクエストでlogコントローラーにデータを送信
    $.ajax({
        url: log_modal_url, // ルートで定義されたURL
        type: 'POST',
            data: {
                modal_location: modal_location, // キーと値のペアで送信
                status: status, // キーと値のペアで送信
            },
        dataType: 'json',
        success: function (response) {
            console.log('Log sent successfully:', response);
        },
        error: function (xhr, status, error) {
            console.error('Error sending log:', error);
            alert('エラーが発生しました。再試行してください。');
        }
    });

}
//選択されたテーブルのセルの情報のログをajaxで記録する
//引数
//1　品番
//2　工程
//3　納期
//4　長期数量
//5　ロット
//6　ajaxURL
function cellinfo_log(item_code,secondColumnText,delivery_date,cellText,maxlot,log_selectcell_url)
{
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    // Ajaxリクエストでlogコントローラーにデータを送信
    $.ajax({
        url: log_selectcell_url, // ルートで定義されたURL
        type: 'POST',
            data: {
                item_code: item_code, // キーと値のペアで送信
                secondColumnText: secondColumnText, // キーと値のペアで送信
                delivery_date: delivery_date, // キーと値のペアで送信
                cellText: cellText, // キーと値のペアで送信
                maxlot: maxlot, // キーと値のペアで送信
            },
        dataType: 'json',
        success: function (response) {
            console.log('Log sent successfully:', response);
        },
        error: function (xhr, status, error) {
            console.error('Error sending log:', error);
            alert('エラーが発生しました。再試行してください。');
        }
    });

}

//ボタンが押された時のログをajaxで記録する
function btnlog(location,log_submit_url)
{
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    console.log(log_submit_url);
    $.ajax({
        url: log_submit_url, // ルートで定義されたURL
        type: 'POST',
            data: {
                data: location // キーと値のペアで送信
            },
        dataType: 'json',
        success: function (response) {
            console.log('Log sent successfully:', response);
        },
        error: function (xhr, status, error) {
            console.error('Error sending log:', error);
            alert('エラーが発生しました。再試行してください。');
        }
    });
}
function maxlog(input_count,log_maxbtn_url)
{
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    console.log(log_submit_url);
    $.ajax({
        url: log_maxbtn_url, // ルートで定義されたURL
        type: 'POST',
            data: {
                max_count: input_count // キーと値のペアで送信
            },
        dataType: 'json',
        success: function (response) {
            console.log('Log sent successfully:', response);
        },
        error: function (xhr, status, error) {
            console.error('Error sending log:', error);
            alert('エラーが発生しました。再試行してください。');
        }
    });
}




////////////////////////////////
//IPを取得
///////////////////////////////
async function getLocalIP() {
    return new Promise((resolve, reject) => {
        const peerConnection = new RTCPeerConnection({
            iceServers: []
        });

        peerConnection.createDataChannel('');

        peerConnection.onicecandidate = (event) => {
            if (event.candidate && event.candidate.candidate) {
                const candidate = event.candidate.candidate;
                const ipMatch = candidate.match(/(\d{1,3}\.){3}\d{1,3}/);

                if (ipMatch) {
                    resolve(ipMatch[0]);
                    peerConnection.close();
                }
            }
        };

        peerConnection.createOffer()
            .then((offer) => peerConnection.setLocalDescription(offer))
            .catch((error) => reject(error));
    });

    
}