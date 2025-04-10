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

// 获取当前登录用户信息
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch();

// 获取文章 ID
$articleId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($articleId) {
    // 查询文章详情
    $sql = "SELECT * FROM articles WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
    $stmt->execute();
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        // 文章不存在，重定向到文章列表页
        header("Location: index.php");
        exit;
    }
} else {
    // 未提供文章 ID，重定向到文章列表页
    header("Location: index.php");
    exit;
}

// 获取所有分类
$sql = "SELECT * FROM categories";
$stmt = $pdo->query($sql);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $content = $_POST["content"];
    $categoryId = $_POST["category_id"];
    $image = $article['image']; // 默认使用原图片

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $filePath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
            // 获取当前脚本所在的目录，去除 'admin' 目录
            $baseUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . str_replace('/admin', '', dirname($_SERVER['SCRIPT_NAME']));
            // 转换为绝对路径
            $image = $baseUrl . '/uploads/' . $fileName;
        }
    }

    if (!empty($title) && !empty($content)) {
        try {
            $sql = "UPDATE articles SET title = :title, content = :content, category_id = :category_id, image = :image WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':content', $content, PDO::PARAM_STR);
            $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
            $stmt->bindParam(':image', $image, PDO::PARAM_STR);
            $stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
            $stmt->execute();
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $error = "更新文章失败: " . $e->getMessage();
        }
    } else {
        $error = "标题和内容不能为空";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>编辑文章</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <!-- 侧边栏 -->
    <div class="fixed left-0 top-0 h-full w-64 bg-white shadow flex flex-col md:block hidden">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-800">管理中心</h1>
        </div>
        <nav class="flex-grow">
            <ul class="px-4">
                <li class="mb-2">
                    <a href="index.php"
                        class="flex items-center px-4 py-2 rounded-md text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                        <i class="fa-solid fa-newspaper mr-3"></i>
                        文章列表
                    </a>
                </li>
                <li class="mb-2">
                    <a href="add_article.php"
                        class="flex items-center px-4 py-2 rounded-md text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                        <i class="fa-solid fa-plus mr-3"></i>
                        发布文章
                    </a>
                </li>
                <li class="mb-2">
                    <a href="category_management.php"
                        class="flex items-center px-4 py-2 rounded-md text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                        <i class="fa-solid fa-tags mr-3"></i>
                        文章分类管理
                    </a>
                </li>
                <li class="mb-2">
                    <a href="category_list.php"
                        class="flex items-center px-4 py-2 rounded-md text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                        <i class="fa-solid fa-list mr-3"></i>
                        文章分类列表
                    </a>
                </li>
            </ul>
        </nav>
        <div class="p-6 border-t border-gray-200">
            <a href="logout.php"
                class="flex items-center px-4 py-2 rounded-md text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                <i class="fa-solid fa-sign-out mr-3"></i>
                退出登录
            </a>
        </div>
    </div>
    <!-- 主内容区 -->
    <div class="ml-0 md:ml-64 p-8">
        <a href="../public/index.php" class="text-blue-500 hover:underline mb-4 inline-block">查看首页</a>
        <h1 class="text-3xl font-bold text-gray-800 mb-6">欢迎，<?= safe_html($user['username']) ?></h1>
        <h2 class="text-2xl font-bold text-gray-800 mb-4">编辑文章</h2>
        <?php if ($error): ?>
            <p class="text-red-500 mb-4"><?= $error ?></p>
        <?php endif; ?>
        <form action="edit_article.php?id=<?= $articleId ?>" method="post" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="title" class="block text-gray-700 text-sm font-bold mb-2">标题</label>
                <input type="text" id="title" name="title"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    value="<?= safe_html($article['title']) ?>" required>
            </div>
            <div class="mb-4">
                <label for="content" class="block text-gray-700 text-sm font-bold mb-2">内容</label>
                <textarea id="content" name="content" rows="10"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    required><?= safe_html($article['content']) ?></textarea>
            </div>
            <div class="mb-4">
                <label for="category_id" class="block text-gray-700 text-sm font-bold mb-2">文章分类</label>
                <select id="category_id" name="category_id"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= $category['id'] == $article['category_id'] ? 'selected' : '' ?>>
                            <?= safe_html($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="image" class="block text-gray-700 text-sm font-bold mb-2">文章图片</label>
                <?php if ($article['image']): ?>
                    <div class="mb-2">
                        <img src="<?= $article['image'] ?>" alt="<?= safe_html($article['title']) ?>" class="max-w-full h-auto">
                    </div>
                <?php endif; ?>
                <input type="file" id="image" name="image"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <button type="submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                保存修改
            </button>
        </form>
    </div>
</body>

</html>    