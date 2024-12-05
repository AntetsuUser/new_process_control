let flag =1;
var selectedFilters = {};
$(function() {
    // ページが読み込まれたときに初期状態を設定
    window.onload = toggleRows;
    // Font Awesomeのアイコンを設定
    var $icon = $('<i class="fa fa-filter filter_button"></i>'); 
    $('#print_history_table thead tr').first().find('th').slice(1).each(function() {
        $(this).append($icon.clone());
    });

    // フィルターアイコンが選択されたら
    $(document).on('click', '.filter_button', function(e) {
        //連続で押せないようにする
        e.stopPropagation();

        // 既存のドロップダウンを非表示にする
        $('.filter-dropdown').remove();

        //テーブルの値を取得してくる
        var table_arr = table_loading();
        //選択されたアイコンの要素を取得
        var $clickedIcon = $(this);
        let allFilterButtons = $('.filter_button');
        var selected_index = allFilterButtons.index($clickedIcon);

        //選択されたアイコンの親のthの要素を取得する
        var $th = $clickedIcon.closest('th');
        //thの親のindexを取得
        var columnIndex = $th.index();
        //フィルターのareaを作成する
        var $filterDropdown = createDropdown();
        const addedItems = new Set();
        //テーブルの値をフィルタにのリストに追加する
        console.log(table_arr);
        table_arr.forEach(function(value) {
            const item = value[columnIndex];
            if (!addedItems.has(item)) {
                addListItem($filterDropdown, item);
                addedItems.add(item);
            }
        });


        if (selectedFilters[columnIndex]) {
            $filterDropdown.find('input[type="checkbox"]').each(function() {
                var checkboxValue = $(this).val();
                $(this).prop('checked', selectedFilters[columnIndex].includes(checkboxValue));
            });
        }
        //アイコンが押されたらフィルターリストを追加する
        var iconOffset = $clickedIcon.offset();
        $filterDropdown.css({
            top: iconOffset.top + $clickedIcon.height(),
            left: iconOffset.left
        }).appendTo('body').fadeIn();

        //フィルターのOKボタンが押されたら
        $('.filter-ok').off('click').on('click', function() {
            var selectedValues = [];
            var values = [];

            $filterDropdown.find('input[type="checkbox"]:checked').not('.select-all').each(function() {
                selectedValues.push($(this).val());
            });

            $filterDropdown.find('input[type="checkbox"]').each(function() {
                values.push($(this).val());
            });
            var isEntered = document.getElementById('entered').checked;
            console.log(values);
            selectedFilters[columnIndex] = selectedValues;
            hide_selected_line(columnIndex, selectedValues,isEntered);

            var allChecked = selectedValues.length === values.length -1;
            $clickedIcon.css('background-color', allChecked ? '' : 'yellow');
            $clickedIcon.addClass("yellow_s");

            // from_invisible_to_display(columnIndex, selectedValues,$filterDropdown)

            // let hiddenElements = $('.entered_row:hidden');
            $filterDropdown.fadeOut(function() {
                $(this).remove();
            });
        });
    });

});

function table_loading() {
    let table_arr = [];  // 配列を初期化
    let tables = $('#print_history_table');
    
    $('#print_history_table thead').each(function(rowIndex, row) {
        $(row).find('th').each(function(cellIndex, cell) {
            table_arr[cellIndex] = $(cell).text();
        });
    });
    var isEntered = document.getElementById('entered').checked;
    console.log(isEntered);
    let data_arr = [];
    if (isEntered) {
        // console.log("未入力の行を表示");
        $('#print_history_table tr').not('[data-flag="false"]').each(function(rowIndex, row) {
            if (rowIndex === 0) return;
            let rowData = [];
            $(row).find('td').each(function(cellIndex, cell) {
                rowData[cellIndex] = $(cell).text();
            });
            data_arr.push(rowData);
        });
    } else {
        // console.log("入力のある行を表示");
        $('#print_history_table tr').not('[data-flag="true"]').each(function(rowIndex, row) {
            if (rowIndex === 0) return;
            let rowData = [];
            $(row).find('td').each(function(cellIndex, cell) {
                rowData[cellIndex] = $(cell).text();
            });
            data_arr.push(rowData);
        });
    }
    
    return data_arr;
}
// フィルターボタンを押したときに表示されるareaの作成
function createDropdown() {
    var $dropdown = $('<div class="filter-dropdown" style="display:none; position:absolute; background-color:white; border:1px solid #ccc; z-index:1000; max-height:200px; width:225px;">' +
                      '<div style="max-height:150px; overflow-y:auto;">' +
                      '<ul style="list-style:none; padding:10px; margin:0;">' +
                      '<li><input type="checkbox" id="select-all" class="select-all" checked> <label for="select-all">すべて選択</label></li>' +
                      '</ul>' +
                      '</div>' +
                      '<div style="padding:10px; text-align:right;">' +
                      '<button class="filter-ok" style="margin-right:5px;">OK</button>' +
                      '<button class="filter-cancel">閉じる</button>' +
                      '</div>' +
                      '</div>');
    //すべて
    var $selectAll = $dropdown.find('.select-all');
    $selectAll.on('change', function() {
        var isChecked = $(this).is(':checked');
        $dropdown.find('input[type="checkbox"]').not('.select-all').prop('checked', isChecked);
    });
    //すべて
    $dropdown.on('change', 'input[type="checkbox"]', function() {
        var allChecked = $dropdown.find('input[type="checkbox"]').not('.select-all').length === $dropdown.find('input[type="checkbox"]:checked').not('.select-all').length;
        $selectAll.prop('checked', allChecked);
    });
    //キャンセルが押されたとき
    $dropdown.find('.filter-cancel').on('click', function() {
        $dropdown.fadeOut(function() {
            $(this).remove();
        });
    });

    return $dropdown;
}

// チェックボックスをフィルターボタンを押したときに表示されるareaに追加
function addListItem($dropdown, value) {

    var id = 'checkbox-' + value.replace(/\s+/g, '-');
    $dropdown.find('ul').append('<li><label for="' + id + '"><input type="checkbox" id="' + id + '" value="' + value + '" checked> ' + value + '</label></li>');

}

//未入力、入力済みのラジオボタンの切替でテーブルの表示するところを変える
function toggleRows() {

    var isEntered = document.getElementById('entered').checked;
    var rows = document.querySelectorAll('.entered_row');
    let allFilterButtons = $('.filter_button');
    allFilterButtons.css('background-color', '');
    console.log(isEntered);
    rows.forEach(function(row) {
        var a_flag = row.getAttribute('data-flag') === 'true';
        if ((isEntered && a_flag) || (!isEntered && !a_flag)) {
            row.classList.remove('tr_hide');
        } else {
            row.classList.add('tr_hide');
        }
    });
}
//非表示にする所
function hide_selected_line(columnIndex, selectedValues,isEntered) {
    //表示している行を非表示にする
    $('#print_history_table tbody tr').not('.tr_hide').each(function() {
        //今どちらのボタンが押されているか取得
        ///////////////////////////////////////////////////////////////////////////////////////////////////////
        var $td = $(this).find('td').eq(columnIndex);
        var cellValue = $td.text().trim();
        if (!selectedValues.includes(cellValue)) {
            $(this).addClass('filter_tr_hide');
            $(this).addClass('filter_tr_hide' + columnIndex);
        }
    });
    //非表示にしている行に表示しなければならなかったら標示する
    $('#print_history_table tbody tr').filter('.filter_tr_hide' + columnIndex).each(function() {
        //今どちらのボタンが押されているか取得
    
        var $td = $(this).find('td').eq(columnIndex);
        console.log(selectedValues);
        var cellValue = $td.text().trim();
        if (selectedValues.includes(cellValue)) {
            //インデクスのクラスをはずして
            $(this).removeClass('filter_tr_hide' + columnIndex);
            //ほかにfilter_tr_hide(1~11)のクラスがすいていなかったらclassを外す
            if(!$(this).attr('class').match(/filter_tr_hide[1-9]|filter_tr_hide10|filter_tr_hide11/))
            {
                $(this).removeClass('filter_tr_hide');
            }
        }
    });

}
//表示をリセットする
function reset()
{
    //フィルターボタンの確認
    let allFilterButtons = $('.filter_button');
    allFilterButtons.css('background-color', '');
    let rows = $('#print_history_table tbody tr');
    // rows.show();  // Use jQuery's show() method to unhide the rows
    toggleRows()
    // $('#searchInput').val('');
    selectedFilters = {};
    // 消したいクラスを配列として定義
    const classesToRemove = ['filter_tr_hide', 'tr_hide'];

    // 各クラスについて、全ての該当要素を取得し、クラスを削除
    classesToRemove.forEach(className => {
        document.querySelectorAll(`.${className}`).forEach(element => {
            element.classList.remove(className);
        });
    });

    // 正規表現に一致するクラスを削除
    const regex = /filter_tr_hide[1-9]|filter_tr_hide10|filter_tr_hide11/;
    document.querySelectorAll('*').forEach(element => {
        Array.from(element.classList).forEach(className => {
            if (regex.test(className)) {
            element.classList.remove(className);
            }
        });
    });
    toggleRows()
}
//非表示の部分を表示する
function from_invisible_to_display(columnIndex, selectedValues,$filterDropdown)
{
    let list_arr = [];
    $filterDropdown.find('input[type="checkbox"]').not('.select-all').each(function() {
            list_arr.push($(this).val());
        });
    console.log(list_arr);
}

