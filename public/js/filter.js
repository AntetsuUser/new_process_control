$(document).ready(function() {
    console.log("よばれました");
    var $icon = $('<i class="fa fa-filter filter_button"></i>'); // Font Awesomeのアイコンクラス
    $('#print_history_table thead tr').first().find('th').slice(1).each(function() {
        $(this).append($icon.clone());
    });

    // アイコンがタップされたときの処理
    $('#print_history_table').on('click', '.filter_button', function(e) {
        e.stopPropagation(); // イベントのバブリングを防止

        // 既存のドロップダウンを非表示にする
        $('.filter-dropdown').remove();

        // クリックされたアイコンの背景色をリセット
        $('.filter_button').css('background-color', '');

        var $clickedIcon = $(this); // クリックされたアイコン
        $clickedIcon.css('background-color', 'yellow'); // 背景色を黄色に

        var $th = $clickedIcon.closest('th'); // タップされたアイコンの親の<th>を取得
        var columnIndex = $th.index(); // 列インデックスを取得

        // タップされた列のすべての<td>要素を取得
        var values = [];
        $('#print_history_table tbody tr').each(function() {
            var $td = $(this).find('td').eq(columnIndex);
            var value = $td.text().trim();
            if (value && !values.includes(value)) {
                values.push(value);
            }
        });

        // リストボックスのHTMLを作成
        var $filterDropdown = createDropdown();

        // タップされた列の<td>要素の値をリストに追加
        values.forEach(function(value) {
            addListItem($filterDropdown, value);
        });

        // クリックしたアイコンの位置を取得してリストボックスをその下に表示
        var iconOffset = $clickedIcon.offset();
        $filterDropdown.css({
            top: iconOffset.top + $clickedIcon.height(),
            left: iconOffset.left
        }).appendTo('body').fadeIn();

    });
});

function createDropdown() {
    // ドロップダウンのHTMLを作成
    var $dropdown = $('<div class="filter-dropdown" style="display:none; position:absolute; background-color:white; border:1px solid #ccc; z-index:1000; max-height:200px; width:200px;">' +
                      '<div style="max-height:150px; overflow-y:auto;">' + // リスト部分のスタイル
                      '<ul style="list-style:none; padding:10px; margin:0;">' +
                      '<li><input type="checkbox" id="select-all" class="select-all" checked> <label for="select-all">すべて選択</label></li>' +
                      '</ul>' +
                      '</div>' +
                      '<div style="padding:10px; text-align:right;">' + // ボタン部分のスタイル
                      '<button class="filter-ok" style="margin-right:5px;">OK</button>' +
                      '<button class="filter-cancel">キャンセル</button>' +
                      '</div>' +
                      '</div>');

    // 「[すべて選択]」オプションのチェックボックスにイベントを追加
    var $selectAll = $dropdown.find('.select-all');
    $selectAll.on('change', function() {
        var isChecked = $(this).is(':checked');
        $dropdown.find('input[type="checkbox"]').not('.select-all').prop('checked', isChecked);
    });

    // リストアイテムのチェックボックスの変更時に「すべて選択」チェックボックスの状態を更新
    $dropdown.on('change', 'input[type="checkbox"]', function() {
        var allChecked = $dropdown.find('input[type="checkbox"]').not('.select-all').length === $dropdown.find('input[type="checkbox"]:checked').not('.select-all').length;
        $selectAll.prop('checked', allChecked);
    });

    // OKボタンのクリックイベントハンドラー
    $dropdown.find('.filter-ok').on('click', function() {
        // // 選択された値を取得する処理を追加
        // var selectedValues = $dropdown.find('input[type="checkbox"]').not('.select-all').filter(':checked').map(function() {
        //     return $(this).val();
        // }).get();
        // console.log('選択された値:', selectedValues);

        // 選択されていない値を取得する処理を追加
        var unselectedValues ;

        $dropdown.find('input[type="checkbox"]').not('.select-all').filter(':not(:checked)').each(function() {
            var checkboxIndex = $dropdown.find('input[type="checkbox"]').not('.select-all').index(this); // チェックボックスのインデックスを取得
            unselectedValues = checkboxIndex + 1 // インデックスを1ベースに変更
        });

        console.log('選択されていない値とチェックボックスの番号:', unselectedValues);


        // アイコンの背景色を変更
        var $clickedIcon = $('.filter_button[style*="yellow"]');
        var allChecked = $dropdown.find('input[type="checkbox"]').not('.select-all').length === $dropdown.find('input[type="checkbox"]:checked').not('.select-all').length;
        if (allChecked) {
            $clickedIcon.css('background-color', 'white');
        } else {
            $clickedIcon.css('background-color', 'yellow');
        }

        // ドロップダウンを閉じる
        $dropdown.fadeOut(function() {
            $(this).remove();
        });
    });

    // キャンセルボタンのクリックイベントハンドラー
    $dropdown.find('.filter-cancel').on('click', function() {

         // 選択されていない値を取得する処理を追加
        var unselectedValues ;

        $dropdown.find('input[type="checkbox"]').not('.select-all').filter(':not(:checked)').each(function() {
            var checkboxIndex = $dropdown.find('input[type="checkbox"]').not('.select-all').index(this); // チェックボックスのインデックスを取得
            unselectedValues = checkboxIndex + 1 // インデックスを1ベースに変更
        });

        console.log('選択されていない値とチェックボックスの番号:', unselectedValues);
        
        // ドロップダウンを閉じる
        $dropdown.fadeOut(function() {
            $(this).remove();
        });
    });

    return $dropdown;
}

function addListItem($dropdown, value) {
    // リストアイテムを追加
    var id = 'checkbox-' + value.replace(/\s+/g, '-'); // 一意のIDを生成
    $dropdown.find('ul').append('<li><label for="' + id + '"><input type="checkbox" id="' + id + '" value="' + value + '" checked> ' + value + '</label></li>');
}

//リストボックスで選択されていないテーブルの行を削除する
function hide_selected_line()
{

}