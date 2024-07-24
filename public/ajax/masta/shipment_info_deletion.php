<?php

// データベース接続
$host = '127.0.0.1:3306';
$dbname = 'laravel';
$dbuser = 'root';
$dbpass = 'andadmin';

$id = $_POST['id'];

try {
    $dbh = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $dbuser, $dbpass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'データベース接続に失敗しました: ' . $e->getMessage()]);
    exit;
}

try {
    // 受け取ったIDでレコードを削除する
    $sql = "DELETE FROM shipping_info WHERE id = :id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // 削除された行数を確認する
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'レコードを削除しました']);
    } else {
        echo json_encode(['success' => false, 'message' => '指定されたIDのレコードが見つかりません']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '削除に失敗しました: ' . $e->getMessage()]);
}
