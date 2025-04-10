<?php
// 开启错误显示，方便调试
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 引入数据库连接文件
require_once '../config/database.php';
// 引入安全输出 HTML 函数
require_once '../functions/helpers.php';

// 获取文章 ID
$articleId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($articleId) {
    // 查询文章详情
    $sql = "SELECT articles.*, users.username, categories.name as category_name 
            FROM articles 
            JOIN users ON articles.author_id = users.id 
            LEFT JOIN categories ON articles.category_id = categories.id 
            WHERE articles.id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
    $stmt->execute();
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($article) {
        $title = $article['title'];
        $content = $article['content'];
        $author = $article['username'];
        $createdAt = $article['created_at'];
        $categoryName = $article['category_name'] ? $article['category_name'] : '未分类';
        $image = $article['image'];
    } else {
        // 文章不存在，重定向到首页
        header("Location: index.php");
        exit;
    }
} else {
    // 未提供文章 ID，重定向到首页
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= safe_html($title) ?></title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <!-- 导航栏 -->
    <nav class="bg-white shadow">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <a href="index.php" class="text-xl font-bold text-gray-800">文章列表</a>
            </div>
        </div>
    </nav>
    <!-- 文章内容 -->
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2"><?= safe_html($title) ?></h1>
        <p class="text-gray-600 text-sm mb-4">作者：<?= safe_html($author) ?> | 发布时间：<?= $createdAt ?> | 分类：<?= safe_html($categoryName) ?></p>
        <?php if ($image): ?>
            <div class="flex justify-center mb-4">
        	<img src="<?= $image ?>" alt="<?= safe_html($title) ?>" class="max-w-full h-auto">
        	<!-- 调试输出图片路径 -->
        	<p>图片路径: <?= $image ?></p>
            </div>
        <?php endif; ?>
        <div class="bg-white shadow p-6 rounded-md">
            <p class="text-gray-700 leading-relaxed"><?= safe_html($content) ?></p>
        </div>
    </div>
</body>

</html>    