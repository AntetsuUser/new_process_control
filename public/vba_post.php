<?php
// POSTされたJSONデータを受け取る
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 生のPOSTデータを取得
    $jsonData = file_get_contents('php://input');
    
    // JSONデータを連想配列に変換
    $data = json_decode($jsonData, true);
    
    // データベース接続情報
    $host = 'localhost';  // データベースサーバー
    $dbname = 'laravel';  // データベース名
    $username = 'root';  // データベースのユーザー名
    $password = 'andadmin';  // データベースのパスワード
    
    try {
        // PDOでデータベース接続
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // material_stockテーブルのmaterial_nameカラムを取得
        $sql = "SELECT material_name FROM material_stock";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        // material_nameの値を配列に格納
        $validItemCodes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $validItemCodes[] = $row['material_name'];  // material_nameの値を配列に追加
        }

        // material_nameが存在するデータだけをフィルタリング
        $filteredData = [];
        foreach ($data as $key => $value) {
            if (in_array($value['item_code'], $validItemCodes)) {
                $filteredData[] = $value;  // 存在するitem_codeを持つデータを追加
            }
        }

        // 各データをループして、データベースに挿入
        $sql = "INSERT INTO material_arrival (arrival_date, item_code, quantity, note, is_matched, status) 
                VALUES (:arrival_date, :item_code, :quantity, :note, :is_matched, :status)";
        
        // SQL文を準備
        $stmt = $pdo->prepare($sql);
        
        // フィルタリングされたデータをループして挿入
        foreach ($filteredData as $item) {
            $stmt->bindParam(':arrival_date', $item['arrival_date']);
            $stmt->bindParam(':item_code', $item['item_code']);
            $stmt->bindParam(':quantity', $item['quantity']);
            $stmt->bindParam(':note', $item['note']);
            $is_matched = "matched";
            $status = "no";
            $stmt->bindParam(':is_matched', $is_matched);
            $stmt->bindParam(':status', $status);

            
            // SQLの実行
            $stmt->execute();
        }

        // 成功した場合のレスポンス
        echo json_encode(['success' => 'データが正常に挿入されました']);
        
    } catch (PDOException $e) {
        // エラーハンドリング
        echo json_encode(['error' => 'データベースエラー: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'POSTリクエストが送信されていません']);
}
?>
