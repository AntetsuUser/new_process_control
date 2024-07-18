<?php

// データベース接続
$host = '127.0.0.1:3306';
// データベース名
$dbname = 'laravel';
// ユーザー名
$dbuser = 'root';
// パスワード
$dbpass = 'andadmin';

$factory_id = $_POST['factory_id'];
$department_id = $_POST['department_id'];
$line = $_POST['line'];

// データベース接続クラスPDOのインスタンス$dbhを作成する
try {
    $dbh = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $dbuser, $dbpass);
    // PDOExceptionクラスのインスタンス$eからエラーメッセージを取得
} catch (PDOException $e) {
    // 接続できなかったらvar_dumpの後に処理を終了する
    var_dump($e->getMessage());
    exit;
}

/*******製造課をデータベースで検索して製造課IDを取ってくる */
// データ取得用SQL
$sql = "SELECT * FROM equipment WHERE factory_id = '$factory_id' AND department_id = '$department_id' AND line = '$line'";
// SQLをセット
$stmt = $dbh->prepare($sql);

// SQLを実行
$stmt->execute();

// 受け取ったデータを配列に代入する
$numbers = $stmt->fetchAll(PDO::FETCH_ASSOC);






// ヘッダーを指定することによりjsonの動作を安定させる
header('Content-type: application/json');
// htmlへ渡す配列$machine_dataをjsonに変換する
echo json_encode($numbers);