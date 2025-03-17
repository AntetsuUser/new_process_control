$(document).ready(function() {

    

    // ページの準備が完了した後にテーブルのセルの色を付ける
    let production = $('input[name="production_section"]').val(); // input要素の値を取得
    let color_arr = []; // 配列を初期化
    console.log(production);
    if (production == "8") {
        color_arr = ["102NC/MC", "103NC/MC", "組立", "704MC"]; // 配列として定義
    }
    //背景色
    let today_color = "#FF4F50";
    //線の色
    let border_color = "#FF0000";
    //今日の日付を取得しthの行だけ色を付ける
    // th_day_color(today_color)
    //今日の日付を取得し一致したすべての行を色を付ける
    day_color(today_color)

    //週終わりの線を太くする
    weeks_end_line(border_color)

    //月替わりの線を破線にする

    // 自動スクロールのフラグ
    let isAutoScrolling = true; // 自動スクロールを初期化
    let scrollTimeout; // タイマーの初期化

    // テーブルの行ごとに処理
    $('#my_table tr').each(function(index, element) {
        let td_elements = $(element).find('td'); // 各行のtd要素を取得
        let process_text = $(element).find('td').eq(1).text();
        if (color_arr.includes(process_text)) {
            let td_count = td_elements.length; // td要素の数を取得
            for (let i = 3; i < td_count; i++) { // 2列目以降のセルを処理
                let td_text = $(element).find('td').eq(i).text();
                if (td_text !== "" && td_text.trim() !== "") {
                    break; // 空白でない場合は処理を終了
                } else {
                    // 条件に応じて色を付ける
                    if(process_text == "102NC/MC" || process_text == "103NC/MC") {
                        $(element).find('td').eq(i).addClass('background_color_blue');
                    } else if(process_text == "組立") {
                        $(element).find('td').eq(i).addClass('background_color_yellow');
                    } else if(process_text == "704MC") {
                        $(element).find('td').eq(i).addClass('background_color_red');
                    }
                }
            }
        }
    });

    // テーブルの自動スクロール処理
    let scrollDirection = 'down';
    let scrollSpeed = 1;
    let tableArea = $('.table_area')[0];

    function autoScroll() {
        let maxScrollTop = tableArea.scrollHeight - tableArea.clientHeight;

        if (isAutoScrolling) { // 自動スクロールが有効な場合のみ実行
            if (scrollDirection === 'down') {
                tableArea.scrollTop += scrollSpeed;

                if (tableArea.scrollTop >= maxScrollTop) {
                    isAutoScrolling = false; // スクロールを停止
                    scrollTimeout = setTimeout(function() {
                        scrollDirection = 'up'; // 上方向に変更
                        isAutoScrolling = true; // 自動スクロールを再開
                    }, 5000); // 5000ミリ秒（5秒）
                }
            } else {
                tableArea.scrollTop -= scrollSpeed;

                if (tableArea.scrollTop <= 0) {
                    isAutoScrolling = false; // スクロールを停止
                    scrollTimeout = setTimeout(function() {
                        scrollDirection = 'down'; // 下方向に変更
                        isAutoScrolling = true; // 自動スクロールを再開
                    }, 5000); // 5000ミリ秒（5秒）
                }
            }
        }
    }

    // マウス移動時に自動スクロールを一時停止
    $(document).on('mousemove', function() {
        isAutoScrolling = false; // 自動スクロールを停止

        // タイマーが存在する場合はクリア
        clearTimeout(scrollTimeout);

        // 3分後に自動スクロールを再開するためのタイマーをセット
        scrollTimeout = setTimeout(function() {
            isAutoScrolling = true; // 自動スクロールを再開
        }, 1000000000); 
    });

    // 60msごとにスクロール処理を実行
    setInterval(autoScroll, 60);
    // 現在時刻をチェック
    setInterval(checkTime, 60000); // 60000ms = 1分ごとにcheckTimeを呼び出し
});

//今日の日付でthの行だけ色を付ける
function th_day_color(today_color)
{
    //今日の日付の色を付ける
    const today = new Date();

    // 年、月、日を取得
    const year = today.getFullYear();      // 年
    const month = String(today.getMonth() + 1).padStart(2, '0'); // 月を2桁に
    const day = String(today.getDate()).padStart(2, '0');        // 日を2桁に
    let id_day = year + "-" + month + "-" +  day;
    console.log(id_day);
    // 全てのthタグのidを取得
    const thElements = document.querySelectorAll('#my_table th.th_text');
    // id_dayと一致するthとその下の曜日thに色を付ける
    thElements.forEach((th, index) => {
        if (th.id === id_day) {
            // 一致した日付のthに色を付ける
            th.style.backgroundColor = today_color;

            // 下の行の対応する曜日のthを取得
            const parentRow = th.closest('tr'); // 日付が含まれる行
            const nextRow = parentRow.nextElementSibling; // 次の行
            if (nextRow) {
                const thsInNextRow = nextRow.querySelectorAll('th.th_text'); // 次の行のthを全て取得
                const correspondingTh = thsInNextRow[index-1]; // 同じインデックスのthを取得
                if (correspondingTh) {
                    correspondingTh.style.backgroundColor = today_color; // 色を変更
                }
            }
        }
    });
}

//今日の日付でthの行だけ色を付ける
function day_color(today_color)
{
    //今日の日付の色を付ける
    const today = new Date();

    // 年、月、日を取得
    const year = today.getFullYear();      // 年
    const month = String(today.getMonth() + 1).padStart(2, '0'); // 月を2桁に
    const day = String(today.getDate()).padStart(2, '0');        // 日を2桁に
    let id_day = year + "-" + month + "-" +  day;
    $('#my_table th.th_text').each(function(index) {
        const th = $(this);
        // idが一致する<th>を見つける
        if (th.attr('id') === id_day) {
            // 一致した<th>の色を変更
            th.css('background-color', today_color);

            // 下の行の対応する<th>を取得して色を変更
            const parentRow = th.closest('tr'); // 現在の<th>の親行を取得
            const nextRow = parentRow.next('tr'); // 次の<tr>を取得
            if (nextRow.length) {
                const thsInNextRow = nextRow.find('th.th_text'); // 次の行の<th>を取得
                const correspondingTh = thsInNextRow.eq(index - 1); // 同じインデックスの<th>を取得
                if (correspondingTh.length) {
                    correspondingTh.css('background-color', today_color);
                }
            }

            // <tbody>内の<tr>を取得して<td>に色を付ける
            $('#my_table tbody tr').each(function () {
                const tds = $(this).find('td'); // 現在の<tr>内の<td>
                // 各<td>をループして内容を確認
                tds.each(function (index) {
                    const td = $(this); // 個々の<td>要素
                    const classList = td.attr('class'); // <td>のクラス属性を取得

                    // class属性にid_dayが含まれているかをチェック
                    if (classList && classList.indexOf(id_day) !== -1) {
                        const $thistd = $(this); // 個々の<td>要素
                        // $thistd.css('background-color', today_color);
                    }
                });
            });
        }
    });
}

// 現在時刻と指定時刻が一致するか確認
function checkTime() {
    const targetHours = [6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24];

    const now = new Date(); // 現在時刻を取得
    const currentHour = now.getHours(); // 現在の時間（0-23）
    const currentMinute = now.getMinutes(); // 現在の分（0-59）
    console.log(currentHour,currentMinute);
    // 現在の時間がターゲットの時間リストに含まれているかを確認
   if (targetHours.includes(currentHour) && currentMinute === 0) {
        sendForm(); // 0分の時にフォームを送信
    }
}
// フォームの送信処理
function sendForm() {
    const form = document.getElementById('ip_form'); // フォームのIDを指定
    if (form) {
        form.submit(); // フォームを送信
    }
}

function weeks_end_line(border_color) {
    let prev_value;
    $('#my_table th.th_text').each(function(index) {
        const th = $(this);
        let th_id = th.attr('id');
        
        if (th_id !== "遅延" && th_id !== undefined && th_id !== null) {
            // th_id が「遅延」、undefined、null でない場合の処理
            // Dateに変換する
            let date = new Date(th_id);
            date.setHours(0, 0, 0, 0); 
            // 週が変わったか判定
            let weekChanged = weeks_judge(date, prev_value);
            // 月が変わったか判定
            let monthChanged = month_judge(date, prev_value);

            console.log("週" + weekChanged);

            console.log("月" + monthChanged);
            
            // 週と月が両方変わった場合は、月の方を優先する
            if (monthChanged) {
                // 月が変わった場合は破線
                th.css({'border-left': '2px dashed '+ border_color});
                const prev_th = th.prev('th.th_text'); // 対象の th の前の th 要素を取得
                // もし前の th が存在すれば、border-right を削除する
                if (prev_th.length) {
                    prev_th.css({'border-right': 'none'}); // 1つ前のthの右側の線を削除
                }
            } else if (weekChanged) {
                // 週が変わった場合は実線
                th.css({'border-left': '2px solid '+ border_color});
                const prev_th = th.prev('th.th_text'); // 対象の th の前の th 要素を取得
                // もし前の th が存在すれば、border-right を削除する
                if (prev_th.length) {
                    prev_th.css({'border-right': 'none'}); // 1つ前のthの右側の線を削除
                }
            }

            // 下の行の対応する<th>を取得して色を変更
            const parentRow = th.closest('tr'); // 現在の<th>の親行を取得
            const nextRow = parentRow.next('tr'); // 次の<tr>を取得
            if (nextRow.length) {
                const thsInNextRow = nextRow.find('th.th_text'); // 次の行の<th>を取得
                const correspondingTh = thsInNextRow.eq(index - 1); // 同じインデックスの<th>を取得
                if (correspondingTh.length) {
                    if (monthChanged) {
                        correspondingTh.css({'border-left': '2px dashed '+ border_color}); // 月が変わった場合は破線
                        const prev_th = correspondingTh.prev('th.th_text'); // 対象の th の前の th 要素を取得
                        // もし前の th が存在すれば、border-right を削除する
                        if (prev_th.length) {
                            prev_th.css({'border-right': 'none'}); // 1つ前のthの右側の線を削除
                        }

                    } else if (weekChanged) {
                        correspondingTh.css({'border-left': '2px solid '+ border_color}); // 週が変わった場合は実線
                        const prev_th = correspondingTh.prev('th.th_text'); // 対象の th の前の th 要素を取得
                        // もし前の th が存在すれば、border-right を削除する
                        if (prev_th.length) {
                            prev_th.css({'border-right': 'none'}); // 1つ前のthの右側の線を削除
                        }
                    }
                }
                
            }

            // <tbody>内の<tr>を取得して<td>に色を付ける
            $('#my_table tbody tr').each(function () {
                const tds = $(this).find('td'); // 現在の<tr>内の<td>
                // 各<td>をループして内容を確認
                tds.each(function (index) {
                    // class属性にth_idと"cellend"が含まれているかをチェック
                    if ($(this).attr('class') && $(this).attr('class').indexOf(th_id) !== -1 && $(this).attr('class').indexOf('cellend') !== -1) {
                        const $thistd = $(this); // 個々の<td>要素
                        const $prevTd = $thistd.prev(); // 一つ前の<td>要素

                        if (monthChanged) {
                            $thistd.css({'border-left': '2px dashed '+ border_color}); // 月が変わった場合は破線
                            $prevTd.css({'border-right': 'none'});
                        } else if (weekChanged) {
                            $thistd.css({'border-left': '2px solid '+ border_color}); // 週が変わった場合は実線
                            $prevTd.css({'border-right': 'none'});
                        }
                    }
                });
            });

            // prev_value を更新
            prev_value = date;
        }
        
    });
}

// 週の判定
function weeks_judge(date, prev_value) {
    if (prev_value) {
        let getStartOfWeek = (date) => {
            let newDate = new Date(date);
            let day = newDate.getDay();
            let diff = newDate.getDate() - day + (day == 0 ? -6 : 1); // 月曜日基準
            newDate.setDate(diff);
            newDate.setHours(0, 0, 0, 0);
            return newDate;
        };

        return getStartOfWeek(date).getTime() !== getStartOfWeek(prev_value).getTime();
    }
    return false;
}

// 月の判定
function month_judge(date, prev_value) {
    if (prev_value) {
        return date.getMonth() !== prev_value.getMonth();
    }
    return false;
}
