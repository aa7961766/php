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

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $categoryName = $_POST["category_name"];
    if (!empty($categoryName)) {
        try {
            $sql = "INSERT INTO categories (name) VALUES (:name)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $categoryName, PDO::PARAM_STR);
            $stmt->execute();
            header("Location: category_management.php");
            exit;
        } catch (PDOException $e) {
            $error = "添加分类失败: " . $e->getMessage();
        }
    } else {
        $error = "分类名称不能为空";
    }
}

// 获取所有分类
$sql = "SELECT * FROM categories";
$stmt = $pdo->query($sql);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文章分类管理</title>
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
        <a href="../index.php" class="text-blue-500 hover:underline mb-4 inline-block">查看首页</a>
        <h1 class="text-3xl font-bold text-gray-800 mb-6">欢迎，<?= safe_html($user['username']) ?></h1>
        <h2 class="text-2xl font-bold text-gray-800 mb-4">文章分类管理</h2>
        <?php if ($error): ?>
            <p class="text-red-500 mb-4"><?= $error ?></p>
        <?php endif; ?>
        <form action="category_management.php" method="post" class="mb-4">
            <div class="mb-4">
                <label for="category_name" class="block text-gray-700 text-sm font-bold mb-2">分类名称</label>
                <input type="text" id="category_name" name="category_name"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    required>
            </div>
            <button type="submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                添加分类
            </button>
        </form>
        <div class="bg-white shadow overflow-x-auto rounded-md">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            编号
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            分类名称
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            创建时间
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            操作
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= $category['id'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="index.php?category_id=<?= $category['id'] ?>" class="text-blue-500 hover:underline">
			<?= safe_html($category['name']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= $category['created_at'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="delete_category.php?id=<?= $category['id'] ?>"
                                class="text-red-500 hover:text-red-700"
                                onclick="return confirm('确定要删除这个分类吗？')">
                                <i class="fa-solid fa-trash"></i>
                                删除
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>    