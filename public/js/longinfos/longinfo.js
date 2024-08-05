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
    LevelingPrediction();
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
        if (element['capture_date'] === th_day) {
            let delivery_date = element['delivery_date'];
            // 納期が遅延している場合の処理
            if (table.eq(3).text() === delivery_date) {
                col = 3;
            } else {
                // 納期が遅延していない場合の処理
                table.each(function (i) {
                    if (element['delivery_date'] === $(this).attr('id')) {
                        col = i ;
                    }
                });
            }


            // 親アイテム名とプロセスの一致を確認してクラスを追加
            table_itemname_row.each(function (i) {
                let item_name = $(this).text();
                if (item_name === element['parent_name']) {
                    item_row = i;
                    return false; // ループから抜ける
                }
            });

            if (item_row !== undefined) {
                let table_tr_index = $("#info_table tbody tr");
                let numberOfRows = table_tr_index.length;

                for (let index = item_row; index < numberOfRows; index++) {
                    let process = table_tr_index.eq(index).find("td:nth-child(2)").text();
                    if (process === element['process']) {
                        row = index;
                        break;
                    }
                }
            }

            if (row !== undefined && col !== undefined) {

                $("#info_table tbody tr").eq(row).find('td').eq(col).addClass('in_work');
            }
            unmatched = false;
        } else {
            // 長期作成日が違った場合
            unmatched = true;
            addToUnmatchedArr(unmatched_arr, element['parent_name'], element['process'], element['processing_quantity']);
        }
    });

    if (unmatched === true) {
        Object.keys(unmatched_arr).forEach(parent_name => 
        {
            // 親アイテム名とプロセスの一致を確認してクラスを追加
            table_itemname_row.each(function (i) {
                let item_name = $(this).text();
                if (item_name === parent_name) {
                    item_row = i;
                    return false; // ループから抜ける
                }
            });
            // console.log(item_row)
            Object.keys(unmatched_arr[parent_name]).forEach(process_text => 
            {
                if (item_row !== undefined) 
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
                }
                let col = 3;
                //ここで色を塗る
                let td_table = $("#info_table tbody tr").eq(item_row+1).find('td');
                //色を塗るtdy要素
                let color_td_table = $("#info_table tbody tr").eq(row).find('td');
                let rows = td_table.length;                    
                let remaining_quantity = Number(unmatched_arr[parent_name][process_text]);
                for (let i = col; i < rows; i++) {
                    let quantity = td_table.eq(i).text();
                    if (quantity !== "") {
                        quantity = Number(quantity);
                        if (isNaN(quantity)) continue;

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
// 選択できる場所の背景色を塗る
/////////////////////////////////////////////////////////////////
function selectable_area_coloring()
{

    let process_able_area_json = $('#process_able_area').val();
    var process_able_area = JSON.parse(process_able_area_json);

    let table_row = $("table tbody tr td:nth-child(2)");
    //テーブルの工程の列のfor
    table_row.each(function(i) {
        let process = $(this).text();
        //工程に/が含まれていたら
        if(process.includes('/'))
        {
            //工程の/を分解
            var process_arr = splitAndProcess(process);
            process_arr.forEach(element => {
                //工程と可能エリアが一致したらclassを付与
                if(process_able_area.includes(element))
                {
                    $(this).closest('tr').addClass('set_cel');
                    // 現在のtdにクラスを追加
                    $(this).addClass('set_cel');
                }
            });
        }else{
            //工程と可能エリアが一致したらclassを付与
            if(process_able_area.includes(process))
            {
                $(this).closest('tr').addClass('set_cel');
                // 現在のtdにクラスを追加
                $(this).addClass('set_cel');
            }
        }        
    });

}   


/////////////////////////////////////////////////////////////////
// 平準化のマスを黄色く塗る
/////////////////////////////////////////////////////////////////
function LevelingPrediction()
{

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
    //リードタイムチェック
    read_time_flag = read_time_check($(this))


    // セルのテキストが空でない場合かつ、'残'の文字が含まれている場合の処理
    if (cellText !== "" && cellText.includes('残') && rowClass == true && read_time_flag == false) {
        selection_elements = $(this);
        //長期数量を表示
        // Maxロットを取得し表示
        let maxlot = $(this).closest('tr').find('td:first-child').text().trim();
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
        }
        else
        {
            $('#num_decision').val(0); 
            $("#info_num").text(cellText);
            $("#lot_number").text(maxlot);
        }
        selection_elements.closest('tr').find('td:first-child p').text(maxlot);
        //モーダルを表示する
        modal.css('display', 'block');
    }
});

/////////////////////////////////////////////////////////////////
// Maxボタンが押されたら
/////////////////////////////////////////////////////////////////
$('#max_btn').on('click',function(){
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
	// input_countの値を<input>要素に設定
    $('#num_decision').val(input_count);
});
/////////////////////////////////////////////////////////////////
// モーダルの決定ボタンが押されたとき
/////////////////////////////////////////////////////////////////
$('#decision_btn').on('click',function()
{

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
    let lot =  selection_elements.closest('tr').find('td:first-child').text().trim();

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
            }
        }
        else
        {
            //maxロットが長期数量より小さい場合
            if(max_lot < input_value || input_value < 1)
            {
                alert("入力した値を確認してください");
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
        }
    }
})

/////////////////////////////////////////////////////////////////
// モーダルの取り消しボタンが押されたとき
/////////////////////////////////////////////////////////////////
$('#cancel_btn').on('click',function(){
   // モーダルを非表示にする
    let cellText = selection_elements.text().trim();
    let maxlot =  selection_elements.closest('tr').find('td:first-child').text().trim();
    if (cellText.includes('(') && cellText.includes(')')) 
    {
        let cancel_lot = cellText.match(/\((.+)\)/)[1]
        cancel_lot = parseInt(cancel_lot)
        var match = cellText.match(/残\d+/);

        selection_elements.text(match);
        selection_elements.toggleClass('selected');
        $('#easyModal').css('display', 'none');
    }
})

/////////////////////////////////////////////////////////////////
// モーダルキャンセルボタンが押されたら　モーダルを閉じる
/////////////////////////////////////////////////////////////////
$('#close_btn').on('click', function() {
    // モーダルを非表示にする
    let cellText = selection_elements.text().trim();
    let maxlot =  selection_elements.closest('tr').find('td:first-child').text().trim();
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
});

/////////////////////////////////////////////////////////////////
// 印刷ボタンを押したとき
/////////////////////////////////////////////////////////////////
$('#print').on('click',function(){
    var allHaveClass = false;
    var className = 'selected'; // 確認したいクラス名
    let select_arr = [];
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
            //加工数-----------------------------------------
            let cellText  = $(this).html();
            let processing_quantity = cellText.match(/\((.+)\)/)[1]
            processing_quantity = parseInt(processing_quantity)
            //長期全数--------------------------------------------
            let  long_term_all;
            for (let index = tr_index; index > 0; index--) {
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
            switch (true) {
                case process.toString().includes('102') && process.toString().includes('MC'):
                    process_number = 2;
                    break;
                case process.toString().includes('102'):
                    process_number = 1;
                    break;
                case process.toString().includes('103') && process.toString().includes('MC'):
                    process_number = 4;
                    break;
                case process.toString().includes('103'):
                    process_number = 3;
                    break;
                case process.toString().includes('組立'):
                    process_number = 5;
                    break;
                case process.toString().includes('704'):
                    process_number = 6;
                    break;
                default:
                    process_number = 0; // エラー処理など、デフォルトの場合の設定
                    break;
            }


            // 配列に格納
            //[品目コード、工程、納期、着手日、加工数、今まで何個加工したか、長期数量、設備番号、作業者id,工程番号]
            select_arr.push([item_code,process,delivery_date,formattedDate,processing_quantity,processing_all,long_term_all,lineNumbers,workersid,process_number]);
        } 
    });
    if(allHaveClass)
    {
        let form = $('<form>', {
            action: '/longinfo/print',
            method: 'post'
        });
        // CSRFトークンをフォームに追加
        let csrfToken = $('meta[name="csrf-token"]').attr('content');
        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: csrfToken
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