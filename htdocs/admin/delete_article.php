<?php
// 开启错误显示，方便调试
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 启动会话
session_start();

// 引入数据库连接文件
require_once '../config/database.php';
// 引入认证函数
require_once '../functions/auth.php';

// 检查用户是否已登录
checkAuth();

$articleId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$articleId) {
    header('Location: /admin/index.php');
    exit;
}

$stmt = $pdo->prepare("DELETE FROM articles WHERE id = :id");
$stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
$stmt->execute();

header('Location: /admin/index.php');
exit;    