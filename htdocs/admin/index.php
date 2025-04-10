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

// 获取分类 ID（如果有）
$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;

// 构建 SQL 查询条件
$whereClause = '';
$params = [];
if ($categoryId) {
    $whereClause = "WHERE articles.category_id = :category_id";
    $params[':category_id'] = $categoryId;
}

// 查询文章列表（包含分类筛选）
$sql = "SELECT articles.*, users.username, categories.name as category_name 
        FROM articles 
        JOIN users ON articles.author_id = users.id 
        LEFT JOIN categories ON articles.category_id = categories.id 
        $whereClause 
        ORDER BY articles.created_at DESC";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 分页逻辑（新增：在分页链接中添加 category_id 参数）
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// 计算总文章数（包含分类筛选）
$countSql = "SELECT COUNT(articles.id) as total 
             FROM articles 
             JOIN users ON articles.author_id = users.id 
             LEFT JOIN categories ON articles.category_id = categories.id 
             $whereClause";
$countStmt = $pdo->prepare($countSql);
foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}
$countStmt->execute();
$total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($total / $limit);

// 查询当前页的文章（包含分类筛选和分页）
$sql = "SELECT articles.id, articles.title, articles.created_at, users.username, categories.name as category_name 
        FROM articles 
        JOIN users ON articles.author_id = users.id 
        LEFT JOIN categories ON articles.category_id = categories.id 
        $whereClause 
        ORDER BY articles.created_at DESC 
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 分页导航（在链接中添加 category_id 参数）
$categoryParam = $categoryId ? "&category_id={$categoryId}" : '';
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文章管理</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <style>
        /* 侧边栏默认隐藏 */
       .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }

        /* 侧边栏显示状态 */
       .sidebar.show {
            transform: translateX(0);
        }

        /* 在中大型屏幕上显示侧边栏 */
        @media (min-width: 768px) {
           .sidebar {
                transform: translateX(0);
            }
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- 汉堡菜单按钮 -->
    <button id="menu-toggle" class="md:hidden fixed top-4 left-4 z-50 text-gray-700 focus:outline-none">
        <i class="fa-solid fa-bars"></i>
    </button>
    <!-- 侧边栏 -->
    <div class="sidebar fixed left-0 top-0 h-full w-64 bg-white shadow flex flex-col z-40 md:block">
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
                <li class="mb-2">
                    <a href="import_dramas.php"
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
        <h2 class="text-2xl font-bold text-gray-800 mb-4">文章列表</h2>
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
                            标题
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            作者
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            分类
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
                    <?php foreach ($articles as $article): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= $article['id'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <!-- 修改标题链接为文章详情页 -->
                            <a href="../public/article.php?id=<?= $article['id'] ?>" class="text-blue-500 hover:underline">
                                <?= safe_html($article['title']) ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= safe_html($article['username']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= $article['category_name'] ? safe_html($article['category_name']) : '未分类' ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= $article['created_at'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="edit_article.php?id=<?= $article['id'] ?>" class="text-yellow-500 hover:text-yellow-700">
                                <i class="fa-solid fa-pen-to-square"></i>
                                编辑
                            </a>
                            <a href="delete_article.php?id=<?= $article['id'] ?>" class="text-red-500 hover:text-red-700 ml-3"
                                onclick="return confirm('确定要删除这篇文章吗？')">
                                <i class="fa-solid fa-trash"></i>
                                删除
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <!-- 分页导航 -->
        <div class="mt-6 px-6">
            <nav aria-label="分页导航">
                <ul class="flex items-center space-x-2">
                    <?php if ($page > 1): ?>
                        <li><a href="?page=<?= $page - 1 ?><?= $categoryParam ?>" class="px-3 py-2 text-sm text-gray-700 rounded hover:bg-gray-100">上一页</a></li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li><a href="?page=<?= $i ?><?= $categoryParam ?>" class="px-3 py-2 text-sm <?= $i === $page ? 'bg-blue-500 text-white' : 'text-gray-700' ?> rounded hover:bg-gray-100"><?= $i ?></a></li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <li><a href="?page=<?= $page + 1 ?><?= $categoryParam ?>" class="px-3 py-2 text-sm text-gray-700 rounded hover:bg-gray-100">下一页</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.querySelector('.sidebar');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });
    </script>
</body>

</html>    