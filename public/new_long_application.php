<?php
// temp_longinfosのテーブルをlonginfosにテーブルをコピー
// データベース接続情報
$host = 'localhost';
$dbname_source = 'temp_longinfo'; // 元データベース名
$dbname_dest = 'longinfos'; // 移動先データベース名
$username = 'root';
$password = 'andadmin';

$process_DB = 'laravel';

// temp_longinfoで在庫数量の引き当て
// 在庫数量取得
// PDOで元データベースに接続
$pdo_stock = new PDO("mysql:host=$host;dbname=$process_DB", $username, $password);
$pdo_stock->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// stockテーブルのデータをすべて取得
$query = $pdo_stock->query("SELECT * FROM stock");
$stock_arr = $query->fetchAll(PDO::FETCH_ASSOC);

// stock配列をループ
foreach ($stock_arr as $stock_key => $stock_value) {
    // 素材在庫と処理日時のキーを削除
    unset($stock_value["id"]);
    unset($stock_value["material_stock_1"]);
    unset($stock_value["material_stock_2"]);
    unset($stock_value["created_at"]);
    unset($stock_value["updated_at"]);
    // 品番を変数に格納してキーの削除
    $item_name = $stock_value["processing_item"];
    unset($stock_value["processing_item"]);

    // PDOでtemp_longinfoに接続
    $pdo_source = new PDO("mysql:host=$host;dbname=$dbname_source", $username, $password);
    $pdo_source->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // 品番テーブルのデータをすべて取得
    $query = $pdo_source->query("SELECT * FROM ".$item_name);
    $item_arr = $query->fetchAll(PDO::FETCH_ASSOC);

    // 工程ごとの在庫数量で引き当て処理作成
    foreach ($stock_value as $key => $quantity) {
        // 除去する文字列
        $removeString = "_stock";
        $serch_key = str_replace($removeString, "", $key);
        $clearing_quantity = $quantity;
        foreach ($item_arr as $item_key => $item_value) {
            if (isset($item_value[$serch_key])) {
                if ($item_value[$serch_key] == 0) {
                    continue;
                } elseif($clearing_quantity < $item_value[$serch_key]) {
                    $item_arr[$item_key][$serch_key] = $item_value[$serch_key] - $clearing_quantity;
                    $clearing_quantity = 0;
                }else{
                    $clearing_quantity = $clearing_quantity - $item_value[$serch_key];
                    $item_arr[$item_key][$serch_key] = 0;
                }
                if ($clearing_quantity == 0) {
                    break;
                }
            } else {
                break;
            }
        }
    }

    // echo "<pre>";
    // var_dump($item_arr);
    // echo "</pre>";
}


// temp_longinfoからlonginfosにデータの移動
try {
    // PDOで元データベースに接続
    $pdo_source = new PDO("mysql:host=$host;dbname=$dbname_source", $username, $password);
    $pdo_source->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo_dest = new PDO("mysql:host=$host;dbname=$dbname_dest", $username, $password);
    $pdo_dest->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // longinfoDBのすべてのテーブルを取得
    $info_tables = $pdo_dest->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($info_tables as $info_table_name) {
        // データベースからテーブルを削除
        $pdo_dest->exec("DROP TABLE $info_table_name");
        echo "元データベースのテーブル $info_table_name を削除しました。\n";
    }

    // 元データベースのすべてのテーブルを取得
    $tables = $pdo_source->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table_name) {
        // テーブル構造を取得
        $query = $pdo_source->query("SHOW CREATE TABLE $table_name");
        $row = $query->fetch(PDO::FETCH_ASSOC);
        $create_table_sql = $row['Create Table'];

        // 新しいデータベースにテーブルを作成
        $pdo_dest->exec($create_table_sql);

        // データをコピー
        $insert_sql = "INSERT INTO $dbname_dest.$table_name SELECT * FROM $dbname_source.$table_name";
        $pdo_dest->exec($insert_sql);

        echo "テーブル $table_name を $dbname_source から $dbname_dest に移動しました。\n";

        // 元データベースからテーブルを削除
        $pdo_source->exec("DROP TABLE $table_name");
        echo "元データベースのテーブル $table_name を削除しました。\n";
    }
    // Connect to the process database using PDO
    $pdo_process = new PDO("mysql:host=$host;dbname=$process_DB", $username, $password);
    $pdo_process->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Delete data from temp_long_term_date
    $sql_delete = "DELETE FROM long_term_date";
    $pdo_process->exec($sql_delete);

    // Replace data from temp_long_term_date to long_term_date
    $sql_insert = "REPLACE INTO long_term_date SELECT * FROM temp_long_term_date";
    $pdo_process->exec($sql_insert);

    // Delete data from temp_long_term_date
    $sql_delete = "DELETE FROM temp_long_term_date";
    $pdo_process->exec($sql_delete);

} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
}

// 接続を閉じる
$pdo_source = null;
$pdo_dest = null;
$pdo_process = null;

?>
