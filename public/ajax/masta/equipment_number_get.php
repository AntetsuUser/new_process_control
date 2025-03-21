<?php

// データベース接続
$host = '127.0.0.1:3306';
// データベース名
$dbname = 'laravel';
// ユーザー名
$dbuser = 'root';
// パスワード
$dbpass = 'andadmin';

$line = $_POST['line'];
$department_id = $_POST['department_id'];


// データベース接続クラスPDOのインスタンス$dbhを作成する
try {
    $dbh = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $dbuser, $dbpass);
    // PDOExceptionクラスのインスタンス$eからエラーメッセージを取得
} catch (PDOException $e) {
    // 接続できなかったらvar_dumpの後に処理を終了する
    var_dump($e->getMessage());
    exit;
}

if($line != "store")
{
    //  受け取ったdepartment_idでラインを取得する
    $sql = "SELECT equipment_id FROM equipment WHERE line = :line AND department_id = :department_id";
    // SQLをセット
    $stmt = $dbh->prepare($sql);
    // パラメータをバインド
    $stmt->bindParam(':line', $line, PDO::PARAM_STR);
    // パラメータをバインド
    $stmt->bindParam(':department_id', $department_id, PDO::PARAM_INT);
    // SQLを実行
    $stmt->execute();

    $lines = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else {
    $sql = "SELECT store FROM store WHERE department_id = :department_id";
    // SQLをセット
    $stmt = $dbh->prepare($sql);
    // パラメータをバインド
    $stmt->bindParam(':department_id', $department_id, PDO::PARAM_INT);
    // SQLを実行
    $stmt->execute();

    $lines = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ヘッダーを指定することによりjsonの動作を安定させる
header('Content-type: application/json');
// htmlへ渡す配列をjsonに変換する
echo json_encode($lines);
