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

// データベース接続クラスPDOのインスタンス$dbhを作成する
try {
    $dbh = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $dbuser, $dbpass);
} catch (PDOException $e) {
    // 接続できなかった場合の処理
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(array('error' => $e->getMessage()));
    exit;
}

//*******製造課をデータベースで検索して製造課IDを取ってくる */
// データ取得用SQL
$sql = "SELECT * FROM department WHERE factory_id = :factory_id";

// SQLをセット
$stmt = $dbh->prepare($sql);

// プレースホルダに値をバインド
$stmt->bindParam(':factory_id', $factory_id, PDO::PARAM_STR);

// クエリを実行
if (!$stmt->execute()) {
    // クエリの実行エラーが発生した場合の処理
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(array('error' => 'データの取得に失敗しました'));
    exit;
}

// データを取得
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// データをJSON形式で出力
header('Content-Type: application/json');
echo json_encode($data);

?>
