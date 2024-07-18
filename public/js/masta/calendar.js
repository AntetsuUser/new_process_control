
var date_array = [];

// 年の切り替えボタンがクリックされたとき
$('#year_select_btn').off('click').on('click', function () 
{
  console.log(date_array.length);
  console.log('ボタンがクリックされました。');
  if(date_array.length > 0)
  {
    var result = window.confirm('日付が選択されています。\n切り替えると、選択が解除されてしまいますが、よろしいですか？');
    console.log( result );
    if(result)
    {
      date_array = [];
      var selectedYear = $('select[name="yearsort"]').val();
      // 現在の年を取得
      // 選択された年が文字列型なので、parseIntを使って数値に変換
      var currentYear = parseInt(selectedYear, 10);
      // 次の年を計算
      var nextYear = currentYear + 1;
      $('.table_div').remove();
      displayCalendarTables(currentYear, nextYear);

      //年が変わって新しいテーブルにクリックイベントを追加
      // セルにクリックイベントを追加
      $('.row td').off('click').on('click', function () {
          var cellText = $(this).text();
          var cellDate = $(this).attr('id');
          if (cellText == '' || cellDate === undefined)
          {
            console.log('空白');
          }
          else
          {
            $(this).toggleClass("select_cell");
            if(date_array.indexOf(cellDate) > -1)
            {
                date_array.splice(date_array.indexOf(cellDate), 1);
            }
            else
            {
                date_array.push(cellDate);
            }
              console.log(date_array)
          }
      });
    }
  }
  else{
    date_array = [];
    var selectedYear = $('select[name="yearsort"]').val();
    // 現在の年を取得
    // 選択された年が文字列型なので、parseIntを使って数値に変換
    var currentYear = parseInt(selectedYear, 10);
    // 次の年を計算
    var nextYear = currentYear + 1;
    $('.table_div').remove();
    displayCalendarTables(currentYear, nextYear);

    //年が変わって新しいテーブルにクリックイベントを追加
    // セルにクリックイベントを追加
    $('.row td').off('click').on('click', function () {
        var cellText = $(this).text();
        var cellDate = $(this).attr('id');
        if (cellText === '')
        {
          console.log('空白');
        }
        else
        {
          $(this).toggleClass("select_cell");
          if(date_array.indexOf(cellDate) > -1)
          {
              date_array.splice(date_array.indexOf(cellDate), 1);
          }
          else
          {
              date_array.push(cellDate);
          }
            console.log(date_array)
        }
    });
  }
});
//更新ボタンが押されたとき
$('#update_btn').on('click', function () {
    console.log(date_array)
    if(date_array.length <= 0)
    {
      alert("日付が選択されていません");
    }
    else
    {
      var result = window.confirm(date_array +'が選択されています。\n更新しますか？');
      if(result)
      {
        var form = document.getElementById("calendar_form");
        var hiddenInput = form.querySelector("input[name='holiday']");
        // date_arrayをJSON形式にエンコードしてhidden inputのvalueに設定
        hiddenInput.value = JSON.stringify(date_array);
        form.submit(); // フォームを送信
      }
    }
});

// カレンダーテーブルを表示する関数
function displayCalendarTables(startYear, endYear) {
  for (let year = startYear; year <= endYear; year++) {
    // 今日の日付を取得
    const today = new Date();
    const today_year = today.getFullYear();
    const today_month = String(today.getMonth() + 1).padStart(2, '0');
    const today_day = String(today.getDate()).padStart(2, '0');
    const today_Date = `${today_year}-${today_month}-${today_day}`;
    let startMonth = 0;
    let endMonth = 0;

    if (year == startYear) {
      startMonth = 4;
      endMonth = 12;
    } else {
      startMonth = 1;
      endMonth = 3;
    }

    for (let month = startMonth; month <= endMonth; month++) {
      // カレンダーの開始日
      const startDate = new Date(year, month - 1, 1); // 月は0から始まるため、1を引く

      // カレンダーの終了日
      const endDate = new Date(year, month, 0); // 月の最後の日を取得

      // テーブルのHTML
      let tableHTML = '<div class="col-4 table_div">';
      tableHTML += '<table border="1">';

      // 表のヘッダ行
      tableHTML += '<tr><th colspan="7">';
      tableHTML += `${year}/${month}</th></tr>`;
      tableHTML += '<tr><th>日</th><th>月</th><th>火</th><th>水</th><th>木</th><th>金</th><th>土</th></tr>';

      // 日付を表示する行
      let dateRow = '<tr>';

      // 曜日を取得
      const firstDayOfWeek = startDate.getDay();

      // 空白で埋める
      for (let i = 0; firstDayOfWeek > 0 && i < firstDayOfWeek; i++) {
        dateRow += '<td class="past_date"></td>';
      }

      let currentDate = new Date(startDate);

      // 日付を埋め込んでいく
      while (currentDate <= endDate) {
        // 年、月、日を取得
        const year = currentDate.getFullYear();
        const month = (currentDate.getMonth() + 1).toString().padStart(2, '0'); // 月は0から始まるため+1する
        const day = currentDate.getDate().toString().padStart(2, '0');
        // yyyy-mm-dd形式に整形
        const formattedDate = `${year}-${month}-${day}`;

        if (formattedDate < today_Date) {
          dateRow += `<td class="past_date">${currentDate.getDate()}</td>`;

        } else if (formattedDate == today_Date) {
          // 休みの日の配列にあったらクラス追加したtdを追加
          if (holidayData.includes(formattedDate) == true) {
            dateRow += `<td id="${formattedDate}" class="holiday">${currentDate.getDate()}</td>`;
          } else {
            dateRow += `<td id="${formattedDate}">${currentDate.getDate()}</td>`;
          }
        } else {
          // 休みの日の配列にあったらクラス追加したtdを追加
          if (holidayData.includes(formattedDate) == true) {
            dateRow += `<td id="${formattedDate}" class="holiday">${currentDate.getDate()}</td>`;
          } else {
            dateRow += `<td id="${formattedDate}">${currentDate.getDate()}</td>`;
          }
        }

        // 土曜日まできたら次の行へ
        if (currentDate.getDay() === 6) {
          dateRow += '</tr>';
          tableHTML += dateRow;
          dateRow = '<tr>';
        }

        // 次の日に進める
        currentDate.setDate(currentDate.getDate() + 1);
      }

      // 最後の週を終える
      if (dateRow !== '<tr>') {
        while (currentDate.getDay() !== 0) {
          dateRow += '<td class="past_date"></td>';
          currentDate.setDate(currentDate.getDate() + 1);
        }

        dateRow += '</tr>';
        tableHTML += dateRow;
      }

      tableHTML += '</table>';
      tableHTML += '</div>';

      // テーブルを表示
      $('.row').append(tableHTML);
    }
    //初期の状態のクリックイベントを追加
    // セルにクリックイベントを追加
    $('.row td').off('click').on('click', function () 
    {
      var cellText = $(this).text();
      var cellDate = $(this).attr('id');
      if (cellText == '' || cellDate === undefined)
      {
        console.log('空白');
      }
      else
      {
        $(this).toggleClass("select_cell");
        if(date_array.indexOf(cellDate) > -1)
        {
            date_array.splice(date_array.indexOf(cellDate), 1);
        }
        else
        {
            date_array.push(cellDate);
        }
          console.log(date_array)
      }
    });
  }
}

// 初回表示
var selectedYear = $('select[name="yearsort"]').val();
var currentYear = parseInt(selectedYear, 10);
var nextYear = currentYear + 1;
displayCalendarTables(currentYear, nextYear);
