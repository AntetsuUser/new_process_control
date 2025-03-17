<?php
// POSTされたJSONデータを受け取る
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 生のPOSTデータを取得
    $jsonData = file_get_contents('php://input');

    // JSONデータを連想配列に変換
    $data = json_decode($jsonData, true);

    // JSONデコードに失敗した場合のエラーチェック
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['error' => 'JSONの解析に失敗しました'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // データベース接続情報
    $host = 'localhost';  // データベースサーバー
    $dbname = 'laravel';  // データベース名
    $username = 'root';  // データベースのユーザー名
    $password = 'andadmin';  // データベースのパスワード

    try {
        // PDOでデータベース接続
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // stockテーブルのprocessing_itemカラムを取得
        $sql = "SELECT processing_item FROM stock";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        // processing_itemの値を配列に格納
        $validItemCodes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $validItemCodes[] = $row['processing_item'];
        }

        // フィルタリング処理
        $filteredData = [];
        foreach ($data as $key => $value) {
            if (!isset($value['item_code'])) {
                error_log("Error: item_code is missing in data: " . json_encode($value));
                continue;
            }

            $item_name = $value['item_code']; // 初期化

            // 数字が最初に出現する位置を取得
            $number_position = strcspn($value['item_code'], '0123456789');

            // "X" または "J" を数字の前に含むか確認
            $contains_X_before_number = strpos(substr($value['item_code'], 0, $number_position), 'X') !== false;
            $contains_J_before_number = strpos(substr($value['item_code'], 0, $number_position), 'J') !== false;

            if ($contains_X_before_number) {
                $item_name = substr($value['item_code'], strpos($value['item_code'], 'X'));
            } elseif ($contains_J_before_number) {
                if (strpos($value['item_code'], '77AN') !== false) {
                    $item_name = substr($value['item_code'], $number_position);
                } else {
                    
                    $item_name = substr($value['item_code'], strpos($value['item_code'], 'J'));
                }
            } else {
                $item_name = substr($value['item_code'], $number_position);
            }

            // 空になった場合のデフォルト値
            if (empty($item_name)) {
                $item_name = $value['item_code'];
            }

            $value['item_code'] = $item_name; // 更新

            if (in_array($value['item_code'], $validItemCodes)) {
                $filteredData[] = $value;
            }
        }

        echo json_encode(['success' => $filteredData], JSON_UNESCAPED_UNICODE);
        // 挿入処理
        $sql = "INSERT INTO product_shipping 
            (product_code, product_text, purchase_order_number, requested_delivery_date, delivered_quantity, 
             material_number_1, material_number_2, material_number_3, material_number_4, material_number_5,status) 
            VALUES (:product_code, :product_text, :purchase_order_number, :requested_delivery_date, :delivered_quantity, 
                    :material_number_1, :material_number_2, :material_number_3, :material_number_4, :material_number_5,:status)";
        
        $stmt = $pdo->prepare($sql);

        foreach ($filteredData as $item) {
            // product_code の NULL チェック
            if (!isset($item['item_code']) || empty($item['item_code'])) {
                error_log("Error: product_code is NULL or empty for item: " . json_encode($item));
                continue;
            }

            $stmt->bindValue(':product_code', $item['item_code']);
            $stmt->bindValue(':product_text', $item['item_text'] ?? '');
            $stmt->bindValue(':purchase_order_number', $item['purchase_order_no'] ?? '');
            $stmt->bindValue(':requested_delivery_date', $item['request_delivery_date'] ?? NULL);
            $stmt->bindValue(':delivered_quantity', $item['order_quantity'] ?? 0);
            $stmt->bindValue(':material_number_1', $item['material_code_1'] ?? NULL);
            $stmt->bindValue(':material_number_2', $item['material_code_2'] ?? NULL);
            $stmt->bindValue(':material_number_3', $item['material_code_3'] ?? NULL);
            $stmt->bindValue(':material_number_4', $item['material_code_4'] ?? NULL);
            $stmt->bindValue(':material_number_5', $item['material_code_5'] ?? NULL);
            $stmt->bindValue(':status', "no");

            try {
                $stmt->execute();
            } catch (PDOException $e) {
                error_log("Insert Error: " . $e->getMessage());
            }
        }

        echo json_encode(['success' => 'データが正常に挿入されました'], JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'データベースエラー: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode(['error' => 'POSTリクエストが送信されていません'], JSON_UNESCAPED_UNICODE);
}
?>
