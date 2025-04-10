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
// 引入安全输出 HTML 函数
require_once '../functions/helpers.php';

// 检查用户是否已登录
checkAuth();

if (isset($_GET['id'])) {
    $categoryId = $_GET['id'];
    try {
        // 先检查该分类下是否有文章
        $checkSql = "SELECT COUNT(*) as count FROM articles WHERE category_id = :category_id";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $checkStmt->execute();
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] == 0) {
            // 若该分类下没有文章，则删除分类
            $sql = "DELETE FROM categories WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            echo "该分类下有文章，不能删除。";
        }
    } catch (PDOException $e) {
        echo "删除分类失败: " . $e->getMessage();
    }
}

header("Location: category_management.php");
exit;