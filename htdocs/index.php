<?php
// 引入数据库连接配置
require_once 'config/database.php';
// 引入辅助函数
require_once 'functions/helpers.php';

// 搜索关键字处理
$search = isset($_GET['search']) ? $_GET['search'] : '';

// 分页配置
$isMobile = preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
$articlePerPage = $isMobile ? 1 : 3; // 移动端显示 1 篇文章，PC 端显示 3 篇文章
$dramaPerPage = 50; // 短剧每页显示 50 条
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;

// 查询当天的短剧总数（含搜索条件）
$today = date('Y-m-d');
$todayDramaCountSql = "SELECT COUNT(id) as total FROM dramas WHERE substr(created_at, 1, 10) = :today";
if (!empty($search)) {
    $todayDramaCountSql .= " AND (title LIKE :search OR link LIKE :search)";
}
$todayDramaCountStmt = $pdo->prepare($todayDramaCountSql);
$todayDramaCountStmt->bindParam(':today', $today, PDO::PARAM_STR);
if (!empty($search)) {
    $searchParam = '%' . $search . '%';
    $todayDramaCountStmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
}

try {
    $todayDramaCountStmt->execute();
    $todayDramaTotal = $todayDramaCountStmt->fetch(PDO::FETCH_ASSOC)['total'];

} catch (PDOException $e) {

    if (!empty($search)) {
       
    }
    $todayDramaTotal = 0;
}

// 查询总文章数（含搜索条件）
$countSql = "SELECT COUNT(articles.id) as total 
             FROM articles 
             JOIN users ON articles.author_id = users.id";
if (!empty($search)) {
    $countSql .= " WHERE articles.title LIKE :search OR articles.intro LIKE :search";
}
$countStmt = $pdo->prepare($countSql);
if (!empty($search)) {
    $countStmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
}
try {
    $countStmt->execute();
    $articleTotal = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    echo "查询总文章数时出错: ". $e->getMessage(). "<br>";
    $articleTotal = 0;
}

// 查询所有 tags 包含“短剧”的短剧总数（含搜索条件）
$allDramaCountSql = "SELECT COUNT(id) as total FROM dramas WHERE tags LIKE '%短剧%'";
if (!empty($search)) {
    $allDramaCountSql .= " AND (title LIKE :search OR link LIKE :search)";
}
$allDramaCountStmt = $pdo->prepare($allDramaCountSql);
if (!empty($search)) {
    $allDramaCountStmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
}
try {
    $allDramaCountStmt->execute();
    $allDramaTotal = $allDramaCountStmt->fetch(PDO::FETCH_ASSOC)['total'];

} catch (PDOException $e) {

    if (!empty($search)) {

    }
    $allDramaTotal = 0;
}

$todayDramaTotalPages = ceil($todayDramaTotal / $dramaPerPage);
$articleTotalPages = ceil($articleTotal / $articlePerPage);

// 总页数取文章和当天短剧的最大值
$totalPages = max($articleTotalPages, $todayDramaTotalPages);

// 查询当前页文章列表（含搜索条件）
$articleOffset = ($page - 1) * $articlePerPage;
$sql = "SELECT 'article' as type, articles.id, articles.title, users.username, articles.created_at, articles.intro, articles.image 
        FROM articles 
        JOIN users ON articles.author_id = users.id";
if (!empty($search)) {
    $sql .= " WHERE articles.title LIKE :search OR articles.intro LIKE :search";
}
$sql .= " ORDER BY articles.created_at DESC 
        LIMIT :articlePerPage OFFSET :articleOffset";
$stmt = $pdo->prepare($sql);
if (!empty($search)) {
    $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
}
$stmt->bindParam(':articlePerPage', $articlePerPage, PDO::PARAM_INT);
$stmt->bindParam(':articleOffset', $articleOffset, PDO::PARAM_INT);
try {
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "查询文章列表时出错: ". $e->getMessage(). "<br>";
    $articles = [];
}

// 查询当前页当天短剧列表（含搜索条件）
$dramaOffset = ($page - 1) * $dramaPerPage;
$todayDramaSql = "SELECT 'drama' as type, id, title, link FROM dramas WHERE substr(created_at, 1, 10) = :today";
if (!empty($search)) {
    $todayDramaSql .= " AND (title LIKE :search OR link LIKE :search)";
}
$todayDramaSql .= " LIMIT :dramaPerPage OFFSET :dramaOffset";
$todayDramaStmt = $pdo->prepare($todayDramaSql);
$todayDramaStmt->bindParam(':today', $today, PDO::PARAM_STR);
if (!empty($search)) {
    $todayDramaStmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
}
$todayDramaStmt->bindParam(':dramaPerPage', $dramaPerPage, PDO::PARAM_INT);
$todayDramaStmt->bindParam(':dramaOffset', $dramaOffset, PDO::PARAM_INT);
try {
    $todayDramaStmt->execute();
    $todayDramas = $todayDramaStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "查询当天短剧列表时出错: ". $e->getMessage(). "<br>";
    $todayDramas = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>主页 - 短剧分享</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <style>
        /* 基础样式 */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.5;
            color: #374151;
            background-color: #f3f4f6;
            padding-bottom: 60px; /* 为移动端底部导航栏留出空间 */
            padding-top: 60px; /* 为移动端顶部标题留出空间 */
        }

        a {
            text-decoration: none;
            color: #3b82f6;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #2563eb;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* 导航栏样式 */
        nav {
            background-color: white;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        /* 文章和短剧列表样式 */
        .item {
            background-color: white;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .item:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .item-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        /* 分页样式 */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination a {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            border: 1px solid #e5e7eb;
            color: #6b7280;
            background-color: white;
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }

        .pagination a.active {
            background-color: #3b82f6;
            border-color: #3b82f6;
            color: white;
            font-weight: 500;
        }

        .pagination a:disabled {
            color: #d1d5db;
            background-color: #f3f4f6;
            cursor: not-allowed;
        }

        /* 搜索框样式（关键部分） */
        .search-container {
            width: 100%;
            max-width: 800px;
            margin: 2rem auto;
        }

        .search-form {
            display: flex;
            width: 100%;
            border-radius: 0.375rem;
            overflow: hidden;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .search-input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 1px solid #e5e7eb;
            font-size: 0.9375rem;
            background-color: white;
        }

        .search-btn {
            padding: 0.75rem 1.5rem;
            background-color: #3b82f6;
            color: white;
            border: 1px solid #3b82f6;
            font-size: 0.9375rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .search-btn:hover {
            background-color: #2563eb;
        }

        /* 移动端底部导航栏样式 */
        @media (max-width: 767px) {
            .mobile-nav {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background-color: white;
                box-shadow: 0 -1px 3px 0 rgba(0, 0, 0, 0.1);
                display: flex;
                justify-content: space-around;
                padding: 10px 0;
            }

            .mobile-nav a {
                display: flex;
                flex-direction: column;
                align-items: center;
                color: #6b7280;
                font-size: 12px;
            }

            .mobile-nav a i {
                font-size: 20px;
                margin-bottom: 5px;
            }

            .mobile-nav a:hover {
                color: #3b82f6;
            }

            .mobile-header {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                background-color: white;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
                padding: 10px 0;
                text-align: center;
            }

            .mobile-header h1 {
                font-size: 1.25rem;
                margin: 0;
            }
        }

        /* PC 端导航栏添加图标样式 */
        @media (min-width: 768px) {
            .desktop-nav .nav-links {
                display: flex;
                gap: 20px;
            }

            .desktop-nav .nav-links a {
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .desktop-nav .nav-links a i {
                font-size: 18px;
            }
        }
    </style>
</head>

<body>
    <!-- 桌面端导航栏 -->
    <nav class="py-4 md:block hidden desktop-nav">
        <div class="container">
            <div class="flex justify-between items-center">
                <h1 class="text-xl font-bold text-gray-800">短剧分享</h1>
                <div class="nav-links">
                    <a href="index.php">
                        <i class="fa-solid fa-house"></i>
                        首页
                    </a>
                    <a href="#">
                        <i class="fa-solid fa-info"></i>
                        关于我们
                    </a>
                    <a href="#">
                        <i class="fa-solid fa-envelope"></i>
                        联系我们
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- 移动端顶部标题 -->
    <div class="mobile-header md:hidden">
        <h1>短剧分享</h1>
    </div>

    <!-- 主内容区 -->
    <div class="container py-8">
        <!-- 搜索框 -->
        <div class="search-container">
            <form action="" method="get" class="search-form">
                <input type="text"
                    name="search"
                    placeholder="搜索文章或短剧"
                    value="<?= htmlspecialchars($search) ?>"
                    class="search-input"
                    aria-label="搜索文章">
                <button type="submit" class="search-btn">
                    搜索
                </button>
            </form>
        </div>

        <h2 class="text-2xl font-bold text-gray-900 mb-6">最新文章</h2>

        <?php if (empty($articles)): ?>
            <p class="text-gray-600">暂无相关文章</p>
        <?php else: ?>
            <ul class="grid grid-cols-1 md:grid-cols-<?= $isMobile ? 1 : 3 ?> gap-6">
                <?php foreach ($articles as $article): ?>
                    <li class="item">
                        <?php if ($article['image']): ?>
                            <img src="<?= $article['image'] ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="item-image">
                        <?php endif; ?>
                        <div class="p-6">
                            <h3 class="text-base font-bold mb-2">
                                <a href="public/article_detail.php?id=<?= $article['id'] ?>" class="text-gray-900 hover:text-blue-600">
                                    <?= htmlspecialchars($article['title']) ?>
                                </a>
                            </h3>
                            <p class="text-sm text-gray-600">
                                作者: <?= htmlspecialchars($article['username']) ?> | 发布时间: <?= $article['created_at'] ?>
                            </p>
                            <p class="text-sm text-gray-600">
                                <?= htmlspecialchars($article['intro']) ?>
                            </p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <!-- 短剧列表 -->
        <div class="drama-list">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                <span class="text-red-500">
                    今日短剧更新 <?= $todayDramaTotal ?> 条<br/> 共有短剧( <?= $allDramaTotal ?> 条)
                </span>
            </h2>
            <?php if (empty($todayDramas)): ?>
                <p class="text-gray-600">暂无当天短剧</p>
            <?php else: ?>
                <ul>
                    <?php
                    $index = 1;
                    foreach ($todayDramas as $drama):
                    ?>
                        <li class="item">
                            <div class="p-6">
                                <h3 class="text-base font-bold mb-2">
                                    <span><?= $index ?>. </span>
                                    <a href="<?= $drama['link'] ?>" target="_blank" class="text-gray-900 hover:text-blue-600">
                                        <?= htmlspecialchars($drama['title']) ?>
                                    </a>
                                </h3>
                            </div>
                        </li>
                    <?php $index++; endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- 分页导航 -->
        <div class="pagination">
            <a href="<?= $page > 1 ? "?page=" . ($page - 1) . ($search ? "&search=" . urlencode($search) : "") : "#" ?>"
                <?= $page === 1 ? "disabled" : "" ?>
                class="pagination-link">
                <<
            </a>
            <?php
            $maxDisplayPages = 5;
            $startPage = max(1, $page - floor($maxDisplayPages / 2));
            $endPage = min($startPage + $maxDisplayPages - 1, $totalPages);
            for ($i = $startPage; $i <= $endPage; $i++):
            ?>
                <a href="?page=<?= $i ?><?= $search ? "&search=" . urlencode($search) : "" ?>"
                    class="<?= $i === $page ? "active" : "" ?> pagination-link">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            <a href="<?= $page < $totalPages ? "?page=" . ($page + 1) . ($search ? "&search=" . urlencode($search) : "") : "#" ?>"
                <?= $page === $totalPages ? "disabled" : "" ?>
                class="pagination-link">
                >>
            </a>
        </div>
    </div>

    <!-- 移动端底部导航栏 -->
    <div class="mobile-nav md:hidden">
        <a href="index.php">
            <i class="fa-solid fa-house"></i>
            首页
        </a>
        <a href="#">
            <i class="fa-solid fa-info"></i>
            关于我们
        </a>
        <a href="#">
            <i class="fa-solid fa-envelope"></i>
            联系我们
        </a>
    </div>

    <!-- 页脚 -->
    <footer class="bg-white py-6 mt-8">
        <div class="container text-center text-gray-600">
            &copy; 2025 短剧分享. 保留所有权利.
        </div>
    </footer>
</body>

</html>
    