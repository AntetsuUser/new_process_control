<?php

ini_set('display_errors', "On");


$C_id = $_POST['characteristic_id'];

$database_address   = "127.0.0.1:3306";		// データベースアドレス
$database_name      = "laravel";			// データベース名
$database_username  = "root";				// データベースユーザ名
$database_password  = "andadmin";				// データベースパスワード

$pdo = new PDO("mysql:host=".$database_address.";dbname=".$database_name, $database_username, $database_password);

// 静的プレースホルダを指定する
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
// エラー発生時に例外を投げる
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = 'SELECT * FROM print_history WHERE characteristic_id = :characteristic_id';
$stmt = $pdo->prepare($sql);

$stmt->bindValue(':characteristic_id', $C_id);

$stmt->execute();
$res = array();

// 取得したデータを出力
foreach ($stmt as $row) 
{
	$data = array();
	$data['id'] = $row['id'];
	$data['characteristic_id'] = $row['characteristic_id'];
	$data['input_complete_flag'] = $row['input_complete_flag'];
	$res[]= $data;
}

echo json_encode($res);

?>