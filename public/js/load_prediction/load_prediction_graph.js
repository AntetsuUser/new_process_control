$(document).ready(function () {
    let item_name = $("#name");
    let process_number = $("#process_number");

    // テーブル分ループ
    for (let i = 0; i < 3; i++) {
        let table = $("#table" + i);
        let days = table.find("thead tr th").length - 1; // 列数を取得
        let totalSum = 0;

        // テーブル全体の合計を計算
        table.find("tbody tr").each(function () {
            $(this)
                .find("td")
                .each(function (index) {
                    if (index > 0) {
                        // 1列目以降の値を取得
                        let value = parseInt($(this).text()) || 0;
                        totalSum += value;
                    }
                });
        });

        // 全体の合計を列数で割る
        let dailyAverage = Math.ceil(totalSum / days);

        // tbodyの末尾に新しい行を追加
        let trElem = $("<tr></tr>").appendTo(table.find("tbody"));
        trElem.append($("<td></td>").text("週5日当たり"));

        // 各列に日平均値を表示
        for (let k = 1; k <= days; k++) {
            trElem.append($("<td></td>").text(dailyAverage.toString()));
        }
    }
});
