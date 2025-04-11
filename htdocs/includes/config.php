<?php
define('DB_DIR', __DIR__ . '/../database');
define('DB_FILE', DB_DIR . '/articles.db');

// 创建数据库目录（如果不存在）
if (!is_dir(DB_DIR)) {
    if (!mkdir(DB_DIR, 0755, true)) {
        die("无法创建数据库目录: ". DB_DIR);
    }
}
?>    