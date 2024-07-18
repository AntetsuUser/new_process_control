$(function() {
    lead_time_coloring();
    selectable_area_coloring();
    let selection_elements;
});

// リードタイムの色を塗る
function lead_time_coloring() {
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
    let table_row = $("table tbody tr td:nth-child(2)");
    let table_color = $("table tbody tr");
    table_row.each(function(i) {
        let process = $(this).text();
        let read_time_day;

        if (process.includes("MC")) {
            read_time_day = process.includes("704MC") ? 3 : 5;
        } else if (process.includes("組立")) {
            read_time_day = 4;
        } else if (process.includes("NC")) {
            read_time_day = 5;
        }

        if (read_time_day !== undefined) {
            for (let index = 0; index <= read_time_day; index++) {
                let cell = table_color.eq(i).find('td').eq(match_th_col + index);
                if (cell.text().trim() !== "") {
                    cell.addClass('read_time');
                }
            }
            for (let col = match_th_col; col >= 3; col--) {
                let cell = table_color.eq(i).find('td').eq(col);
                if (cell.text().trim() !== "") {
                    cell.addClass('read_time');
                }
            }
        }
    });
}

// 選択できる場所の背景色を塗る
function selectable_area_coloring() {
    let process_able_area_json = $('#process_able_area').val();
    let process_able_area = JSON.parse(process_able_area_json);
    let table_row = $("table tbody tr td:nth-child(2)");

    table_row.each(function(i) {
        let process = $(this).text();

        if (process.includes('/')) {
            let process_arr = splitAndProcess(process);
            process_arr.forEach(element => {
                if (process_able_area.includes(element)) {
                    $(this).closest('tr').addClass('set_cel');
                    $(this).addClass('set_cel');
                }
            });
        } else {
            if (process_able_area.includes(process)) {
                $(this).closest('tr').addClass('set_cel');
                $(this).addClass('set_cel');
            }
        }
    });
}

// テーブルセルがタップされたら
$(document).on('click', 'table tbody tr td:nth-child(n+4)', function() {
    const modal = $('#easyModal');
    let cellText = $(this).text().trim();
    let rowClass = $(this).closest('tr').hasClass('set_cel');
    let read_time_flag = read_time_check($(this));

    if (cellText !== "" && cellText.includes('残') && rowClass == true && read_time_flag == false) {
        selection_elements = $(this);
        let maxlot = $(this).closest('tr').find('td:first-child').text().trim();

        if (cellText.includes('(') && cellText.includes(')')) {
            let cancel_lot = cellText.match(/\((.+)\)/)[1];
            cancel_lot = parseInt(cancel_lot);
            var match = cellText.match(/残\d+/);
            $("#info_num").text(match);

            let parts = maxlot.split('/');
            let input_lot = parts[0].trim();
            let max = parts[1].trim();
            input_lot = input_lot - cancel_lot;
            maxlot = `${input_lot} / ${max}`;

            $("#lot_number").text(maxlot);
            $('#num_decision').val(cancel_lot);
        } else {
            $('#num_decision').val(0);
            $("#info_num").text(cellText);
            $("#lot_number").text(maxlot);
        }
        selection_elements.closest('tr').find('td:first-child p').text(maxlot);
        modal.css('display', 'block');
    }
});

// Maxボタンが押されたら
$('#max_btn').on('click', function() {
    let input_value = $('#num_decision').val();
    let lot_number = $('#lot_number').text();
    let info_num = $('#info_num').text().replace('残', '').trim();
    let parts = lot_number.split('/');
    let input_lot = parts[0].trim();
    let max_lot = parts[1].trim();

    input_lot = parseInt(input_lot);
    max_lot = parseInt(max_lot);
    info_num = parseInt(info_num);

    let count_value = max_lot - input_lot;
    let input_count = info_num != input_value ? Math.min(count_value, info_num) : input_value;

    $('#num_decision').val(input_count);
});

// モーダルの決定ボタンが押されたとき
$('#decision_btn').on('click', function() {
    let input_value = $('#num_decision').val();
    let info_num = $('#info_num').text().replace('残', '').trim();
    if (info_num.includes('(') && info_num.includes(')')) {
        info_num = info_num.match(/残\d+/);
    }
    let lot = selection_elements.closest('tr').find('td:first-child').text().trim();
    let parts = lot.split('/');
    let input_lot = parts[0].trim();
    let max_lot = parts[1].trim();

    input_lot = parseInt(input_lot);
    input_value = parseInt(input_value);
    max_lot = parseInt(max_lot);
    info_num = parseInt(info_num);

    if (max_lot >= info_num) {
        if (info_num < input_value || input_value < 1) {
            alert("入力した値を確認してください");
        } else {
            input_lot += input_value;
            let new_lot_number = `${input_lot}/${max_lot}`;
            selection_elements.closest('tr').find('td:first-child p').text(new_lot_number);
            let info_value = $('#info_num').text();
            selection_elements.text(info_value + "(" + input_value + ")");
            if (!selection_elements.hasClass('selected')) {
                selection_elements.addClass('selected');
            }
            $('#easyModal').css('display', 'none');
        }
    } else {
        if (max_lot < input_value || input_value < 1) {
            alert("入力した値を確認してください");
        } else {
            input_lot += input_value;
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
});

// モーダルの取り消しボタンが押されたとき
$('#cancel_btn').on('click', function() {
    let cellText = selection_elements.text().trim();
    if (cellText.includes('(') && cellText.includes(')')) {
        let cancel_lot = cellText.match(/\((.+)\)/)[1];
        cancel_lot = parseInt(cancel_lot);
        var match = cellText.match(/残\d+/);
        selection_elements.text(match);
        selection_elements.toggleClass('selected');
        $('#easyModal').css('display', 'none');
    }
});

// モーダルキャンセルボタンが押されたら　モーダルを閉じる
$('#close_btn').on('click', function() {
    let cellText = selection_elements.text().trim();
    if (cellText.includes('(') && cellText.includes(')')) {
        let cancel_lot = cellText.match(/\((.+)\)/)[1];
        cancel_lot = parseInt(cancel_lot);
        var match = cellText.match(/残\d+/);
        $("#info_num").text(match);

        let parts = selection_elements.closest('tr').find('td:first-child').text().trim().split('/');
        let input_lot = parseInt(parts[0].trim());
        let max = parseInt(parts[1].trim());
        input_lot += cancel_lot;
        selection_elements.closest('tr').find('td:first-child p').text(`${input_lot}/${max}`);
    }
    $('#easyModal').css('display', 'none');
});

// 工程の/で分解する関数
function splitAndProcess(input) {
    var regex = /(\d+)([A-Za-z]+)/g;
    var matches = regex.exec(input);
    if (matches && matches.length === 3) {
        return [matches[1] + matches[2], matches[1] + "MC"];
    } else {
        return null;
    }
}

// read_timeクラスがついているか確認する関数
function read_time_check(selection_elements) {
    return selection_elements.prevAll('td').hasClass('read_time');
}
