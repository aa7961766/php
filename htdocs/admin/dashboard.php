<?php
session_start();
require_once '../includes/auth_functions.php';
require_once '../includes/db_connection.php';

if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit;
}

// 处理注销请求
if (isset($_GET['logout'])) {
    logoutAdmin();
}

$conn = connectToDatabase();

// 查询文章总数量
$articleCountStmt = $conn->query("SELECT COUNT(*) as count FROM articles");
$articleCount = $articleCountStmt->fetch(PDO::FETCH_ASSOC)['count'];

// 查询短剧总数量
$dramaCountStmt = $conn->query("SELECT COUNT(*) as count FROM dramas");
$dramaCount = $dramaCountStmt->fetch(PDO::FETCH_ASSOC)['count'];

// 查询文章分类数量
$articleCategoryCountStmt = $conn->query("SELECT COUNT(*) as count FROM article_categories");
$articleCategoryCount = $articleCategoryCountStmt->fetch(PDO::FETCH_ASSOC)['count'];

// 查询短剧分类数量
$dramaCategoryCountStmt = $conn->query("SELECT COUNT(*) as count FROM drama_categories");
$dramaCategoryCount = $dramaCategoryCountStmt->fetch(PDO::FETCH_ASSOC)['count'];

// 查询搜索关键词总数量
$searchKeywordCountStmt = $conn->query("SELECT COUNT(*) as count FROM search_keywords");
$searchKeywordCount = $searchKeywordCountStmt->fetch(PDO::FETCH_ASSOC)['count'];

$conn = null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台仪表盘</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 font-sans">
    <?php include 'includes/admin_header.php'; ?>
    <main class="p-8">
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">后台仪表盘</h2>
            <p class="text-gray-600">欢迎，<?php echo $_SESSION['admin_username']; ?>！</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-2">文章总数量</h3>
                <p class="text-3xl font-bold text-blue-600"><?php echo $articleCount; ?></p>
            </div>
            <div class="bg-white shadow-md rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-2">短剧总数量</h3>
                <p class="text-3xl font-bold text-green-600"><?php echo $dramaCount; ?></p>
            </div>
            <div class="bg-white shadow-md rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-2">文章分类数量</h3>
                <p class="text-3xl font-bold text-yellow-600"><?php echo $articleCategoryCount; ?></p>
            </div>
            <div class="bg-white shadow-md rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-2">短剧分类数量</h3>
                <p class="text-3xl font-bold text-purple-600"><?php echo $dramaCategoryCount; ?></p>
            </div>
            <div class="bg-white shadow-md rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-2">搜索关键词总数量</h3>
                <p class="text-3xl font-bold text-orange-600"><?php echo $searchKeywordCount; ?></p>
            </div>
        </div>
        <div class="mt-8">
            <a href="?logout=1" class="inline-block bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                注销
            </a>
        </div>
    </main>
    <?php include 'includes/admin_footer.php'; ?>
</body>

</html>    