<?php
try {
    // 数据库文件路径
    $dbPath = __DIR__ . '/database.db';
    // 创建 PDO 实例，连接到 SQLite 数据库

    $pdo = new PDO("sqlite:$dbPath");
    // 设置 PDO 错误模式为异常模式
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // 若连接失败，输出错误信息
    die("数据库连接失败: " . $e->getMessage());
}    