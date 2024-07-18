<?php

// データベース接続
$host = '127.0.0.1:3306';
// データベース名
$dbname = 'laravel';
// ユーザー名
$dbuser = 'root';
// パスワード
$dbpass = 'andadmin';

$item_name = $_POST['item_name'];

// データベース接続クラスPDOのインスタンス$dbhを作成する
try {
    $dbh = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $dbuser, $dbpass);
    // PDOExceptionクラスのインスタンス$eからエラーメッセージを取得
} catch (PDOException $e) {
    // 接続できなかったらvar_dumpの後に処理を終了する
    var_dump($e->getMessage());
    exit;
}
//  受け取ったfactory_idで製造課を取得する
$sql = "SELECT * FROM process WHERE processing_item = '$item_name'";
// SQLをセット
$stmt = $dbh->prepare($sql);
// SQLを実行
$stmt->execute();

$process = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ヘッダーを指定することによりjsonの動作を安定させる
header('Content-type: application/json');
// htmlへ渡す配列をjsonに変換する
echo json_encode($process);