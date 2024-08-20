<?php
ini_set('display_errors', "On");
// temp_longinfosのテーブルをlonginfosにテーブルをコピー
// データベース接続情報
$host = 'localhost';
$dbname_source = 'temp_longinfo'; // 元データベース名
$dbname_dest = 'longinfos'; // 移動先データベース名
$username = 'root';
$password = 'andadmin';

$process_DB = 'laravel';

//jsonファイルを確認する
//new_data.jsonとdata.jsonがあったなかった場合処理しない
//あった場合data.jsonを消してnew_data.jsonをdata.jsonとして保存する

// ファイルパスの指定
$newDataFile = 'new_data.json';
$dataFile = 'data.json';

// ファイルの存在確認
if (!file_exists($newDataFile) || !file_exists($dataFile)) {
    echo "必要なファイルが見つかりません。処理を中止します。";
    exit; // スクリプトを終了
}

// data.jsonを削除
if (file_exists($dataFile)) {
    unlink($dataFile);
}

// new_data.jsonをdata.jsonとして保存
rename($newDataFile, $dataFile);

echo "ファイルの更新が完了しました。";



function databaseExists($host, $username, $password, $dbname) {
    try {
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
        return $query->rowCount() > 0;
    } catch (PDOException $e) {
        echo "データベース接続エラー: " . $e->getMessage();
        return false;
    }
}
// 必要なテーブルが存在するか確認する関数
function tableExists($host, $username, $password, $dbname, $tablename) {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("データベース接続に失敗しました: " . $conn->connect_error);
    }
    
    $result = $conn->query("SHOW TABLES LIKE '$tablename'");
    $conn->close();
    
    return $result && $result->num_rows > 0;
}

// データベースの存在確認と接続
if (!databaseExists($host, $username, $password, $dbname_source)) {
    echo "データベース '$dbname_source' は存在しません。";
    exit; // スクリプトを終了
}



// 各データベース内の必要なテーブルの存在確認
$requiredTables = ['77AN704H0R15', '77AN704W0R15', '77AN704G0R15']; // 必要なテーブルのリスト

foreach ($requiredTables as $table) {
    if (!tableExists($host, $username, $password, $dbname_source, $table)) {
        echo "データベース '$dbname_source' に必要なテーブル '$table' が存在しません。";
        exit; // スクリプトを終了
    }
    

}


// temp_longinfoで在庫数量の引き当て
// 在庫数量取得
// PDOで元データベースに接続
$pdo_source = new PDO("mysql:host=$host;dbname=$dbname_source", $username, $password);
$pdo_source->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


//stockの工程の在庫を取得する
// stockテーブルのデータをすべて取得
$pdo_stock = new PDO("mysql:host=$host;dbname=$process_DB", $username, $password);
$pdo_stock->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$query = $pdo_stock->query("SELECT processing_item, process1_stock, process2_stock, process3_stock, process4_stock, process5_stock, process6_stock, process7_stock, process8_stock, process9_stock, process10_stock FROM stock");
$stock_arr = $query->fetchAll(PDO::FETCH_ASSOC);
$item_process_stock =[];
foreach ($stock_arr as $stock_key =>  $stock_data) {
    foreach ($stock_data as $key => $value) 
    {
       if (preg_match('/_stock$/', $key)) {
            // `_stock`を削除した新しいキーを作成
            $new_key = preg_replace('/_stock$/', '', $key);
            $item_process_stock[$stock_data["processing_item"]][$new_key] = $value;
        }
    }
}
//在庫から進捗を更新
foreach ($item_process_stock as $item_name => $process) {
    foreach ($process as $key => $stock_value) 
    {
        try {
            $pdo_source = new PDO("mysql:host=$host;dbname=$dbname_source", $username, $password);
            $pdo_source->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // カラムの存在を確認
            $query = $pdo_source->query("SHOW COLUMNS FROM $item_name LIKE '$key'");
            $column_exists = $query->rowCount() > 0;

            if ($column_exists) {
                // カラムが存在する場合の処理
                $query = $pdo_source->query("SELECT $key FROM $item_name");
                $item_arr = $query->fetchAll(PDO::FETCH_ASSOC);

                // データが取得できた場合のみ処理を実行
                if (!empty($item_arr)) {
                    if ($key == "process6") {
                        $remaining = max(0, $process["process6"]);
                    } else if ($key == "process5") {
                        $remaining = max(0, $process["process6"]) + max(0, $process["process5"]);
                    } else if ($key == "process4") {
                        $remaining = max(0, $process["process6"]) + max(0, $process["process5"]) + max(0, $process["process4"]);
                    } else if ($key == "process3") {
                        $remaining = max(0, $process["process6"]) + max(0, $process["process5"]) + max(0, $process["process4"]) + max(0, $process["process3"]);
                    } else if ($key == "process2") {
                        $remaining = max(0, $process["process6"]) + max(0, $process["process5"]) + max(0, $process["process2"]);
                    } else if ($key == "process1") {
                        $remaining = max(0, $process["process6"]) + max(0, $process["process5"]) + max(0, $process["process2"]) + max(0, $process["process1"]);
                    }
                    foreach ($item_arr as $item => $val) {

                        if($remaining <= 0)
                        {
                            
                            break;
                        }
                        else {
                            if($remaining > $item_arr[$item][$key])
                            {

                                $remaining = $remaining - $item_arr[$item][$key];
                                $item_arr[$item][$key] = 0;
                            }
                            elseif($remaining <= $item_arr[$item][$key])
                            {   
                                $item_arr[$item][$key] = $item_arr[$item][$key] - $remaining;
                                $remaining = 0;
                                break;
                            }
                        }
                    }
                    // DBの更新処理
                    foreach ($item_arr as $item => $val) {

                        $DB_id = $item +1;
                        // SQLの準備
                        $stmt = $pdo_source->prepare("UPDATE $item_name SET $key = :value WHERE id = :id");
                        // データベースの更新
                        $stmt->execute([':value' => $val[$key], ':id' => $DB_id]);
                    }
                }
            }
        } catch (PDOException $e) {
            echo "データベースエラー: " . $e->getMessage();
        }
    }
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
