<?php
require_once 'config.php';

function connectToDatabase() {
    try {
        $conn = new PDO('sqlite:'. DB_FILE);

        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("数据库连接失败: ". $e->getMessage());
    }
}
?>