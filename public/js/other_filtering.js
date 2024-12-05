
var selectedFilters = {};
$(function() {
    var $icon = $('<i class="fa fa-filter filter_button"></i>'); 
    //thの数だけフィルターボタンを追加する
    $('.filtering_table thead tr').first().find('th').each(function() {
        let heading_cell = $(this).text();
        //thに文字があるならフィルターボタンを追加する
        if(heading_cell)
        {       
            $(this).append($icon.clone());
        }
    });
    //フィルターアイコンがクリックされたら
    $(document).on('click', '.filter_button', function(e) {
        console.log("クリックされたよ");
        //連続で押せないようにする
        e.stopPropagation();
        //テーブルの値を取得してくる
        let table_data =　table_loading();
        console.log(table_data);
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

        table_data.forEach(function(value) {
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
            selectedFilters[columnIndex] = selectedValues;
            hide_selected_line(columnIndex, selectedValues);

            var allChecked = selectedValues.length === values.length -1;
            $clickedIcon.css('background-color', allChecked ? '' : 'yellow');
            $clickedIcon.addClass("yellow_s");

            //フィルターをとじる
            $filterDropdown.fadeOut(function() {
                $(this).remove();
            });
        });
    });
});

//テーブル
function table_loading()
{
    let table_arr = [];  // 配列を初期化
    let data_arr = [];
    let tables = $('.filtering_table');
    //thの情報を取得してくる
    $('.filtering_table thead').each(function(rowIndex, row) {
        $(row).find('th').each(function(cellIndex, cell) {
            table_arr[cellIndex] = $(cell).text();
        });
    });

    $('.filtering_table tr').each(function(rowIndex, row) {
        if (rowIndex === 0) return;
        let rowData = [];
        $(row).find('td').each(function(cellIndex, cell) {
            rowData[cellIndex] = $(cell).text();
        });
        data_arr.push(rowData);
    });
    return data_arr;

}

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

function hide_selected_line(columnIndex, selectedValues) {
    //表示している行を非表示にする
    $('.filtering_table tbody tr').not('.tr_hide').each(function() {
        //今どちらのボタンが押されているか取得
        
        var $td = $(this).find('td').eq(columnIndex);
        var cellValue = $td.text().trim();
        if (!selectedValues.includes(cellValue)) {
            $(this).addClass('filter_tr_hide');
            $(this).addClass('filter_tr_hide' + columnIndex);
        }
    });
    //非表示にしている行に表示しなければならなかったら標示する
    $('.filtering_table tbody tr').filter('.filter_tr_hide' + columnIndex).each(function() {
        //今どちらのボタンが押されているか取得
    
        var $td = $(this).find('td').eq(columnIndex);
        console.log(selectedValues);
        var cellValue = $td.text().trim();
        if (selectedValues.includes(cellValue)) {
            //インデクスのクラスをはずして
            $(this).removeClass('filter_tr_hide' + columnIndex);
            //ほかにfilter_tr_hide(1~11)のクラスがすいていなかったらclassを外す
            if(!$(this).attr('class').match(/filter_tr_hide[0-9]|filter_tr_hide10|filter_tr_hide11|filter_tr_hide12|filter_tr_hide13/))
            {
                $(this).removeClass('filter_tr_hide');
            }
        }
    });
    
}