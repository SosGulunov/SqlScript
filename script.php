<?php
$DB_PATH = 'sqlite:database.db';
$INVALID_DATA_PATH = 'invalid_orders.txt';

try {
    // Создаем подключение к базе данных
    $conn = new PDO($DB_PATH);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Создание таблиц (если они не существуют)
    $conn->exec("CREATE TABLE IF NOT EXISTS clients (
        id INTEGER PRIMARY KEY,
        name TEXT NOT NULL
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS merchandise (
        id INTEGER PRIMARY KEY,
        name TEXT NOT NULL
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        item_id INTEGER,
        customer_id INTEGER,
        comment TEXT,
        status TEXT,
        order_date DATE DEFAULT (DATE('now')),
        FOREIGN KEY (item_id) REFERENCES merchandise(id),
        FOREIGN KEY (customer_id) REFERENCES clients(id)
    )");

    function importOrders($filePath) {
        global $conn, $INVALID_DATA_PATH;
        $file = fopen($filePath, 'r');
        $invalidFile = fopen($INVALID_DATA_PATH, 'w');

        while (($line = fgetcsv($file, 0, ";")) !== FALSE) {
            if (count($line) != 3) {
                fputcsv($invalidFile, $line, ";");
                continue;
            }

            list($item_id, $customer_id, $comment) = $line;

            if (!is_numeric($item_id) || !is_numeric($customer_id)) {
                fputcsv($invalidFile, $line, ";");
                continue;
            }

            $stmt = $conn->prepare('SELECT COUNT(*) FROM merchandise WHERE id=?');
            $stmt->execute([$item_id]);
            if ($stmt->fetchColumn() == 0) {
                fputcsv($invalidFile, $line, ";");
                continue;
            }

            $stmt = $conn->prepare('SELECT COUNT(*) FROM clients WHERE id=?');
            $stmt->execute([$customer_id]);
            if ($stmt->fetchColumn() == 0) {
                fputcsv($invalidFile, $line, ";");
                continue;
            }

            $stmt = $conn->prepare('
                INSERT INTO orders (item_id, customer_id, comment, status)
                VALUES (?, ?, ?, "new")
            ');
            $stmt->execute([$item_id, $customer_id, $comment]);
        }

        fclose($file);
        fclose($invalidFile);
    }

    importOrders('orders.txt');
    $conn = null;

} catch(PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
}
