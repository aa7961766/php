<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../includes/auth_functions.php';
require_once '../includes/db_connection.php';

if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit;
}

$message = '';
$conn = connectToDatabase();

// 处理批量删除
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['selected_articles'])) {
    $selectedArticles = $_POST['selected_articles'];
    $conn->beginTransaction();
    foreach ($selectedArticles as $articleId) {
        $deleteQuery = "DELETE FROM articles WHERE id = :id";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bindParam(':id', $articleId, PDO::PARAM_INT);
        if (!$deleteStmt->execute()) {
            $errorInfo = $deleteStmt->errorInfo();
            $message = "删除文章时出错: ". $errorInfo[2];
            $conn->rollBack();
            break;
        }
    }
    if (empty($message)) {
        $conn->commit();
        $message = "文章批量删除成功！";
    }
}

// 分页处理
$limit = 50; // 每页显示的记录数
$page = isset($_GET['page']) && is_numeric($_GET['page'])? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// 获取总记录数
$totalQuery = "SELECT COUNT(*) as total FROM articles";
$totalStmt = $conn->query($totalQuery);
$totalResult = $totalStmt->fetch(PDO::FETCH_ASSOC);
$totalRecords = $totalResult['total'];
$totalPages = ceil($totalRecords / $limit);

// 获取当前页的文章数据
$selectQuery = "SELECT * FROM articles LIMIT :offset, :limit";
$selectStmt = $conn->prepare($selectQuery);
$selectStmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$selectStmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$selectStmt->execute();
$articles = $selectStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文章列表</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <script>
        // 全选功能
        function selectAll() {
            var checkboxes = document.querySelectorAll('input[type="checkbox"]');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = document.getElementById('select-all').checked;
            }
        }
    </script>
    <style>
        /* 小屏幕下表格样式优化 */
        @media (max-width: 640px) {
            table.min-w-full {
                min-width: auto;
            }
            table th,
            table td {
                padding: 0.5rem;
                font-size: 0.875rem;
            }
        }

        /* 小屏幕下分页导航样式优化 */
        @media (max-width: 640px) {
            nav.flex {
                justify-content: center;
            }
            nav.flex a {
                padding: 0.25rem 0.5rem;
                font-size: 0.875rem;
            }
        }

        /* 小屏幕下页脚样式优化 */
        @media (max-width: 640px) {
            footer {
                padding: 0.5rem;
                font-size: 0.875rem;
            }
        }
    </style>
</head>

<body class="bg-gray-100 font-sans">
    <?php include 'includes/admin_header.php'; ?>
    <main class="p-8">
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">文章列表</h2>
            <?php if ($message): ?>
                <div class="mb-4 <?php echo strpos($message, '成功')!== false? 'text-green-600' : 'text-red-600'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" id="select-all" onclick="selectAll()">
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">标题</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">发布时间</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">操作</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($articles as $article): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-no-wrap">
                                        <input type="checkbox" name="selected_articles[]" value="<?php echo $article['id']; ?>">
                                    </td>
                                    <td class="px-6 py-4 whitespace-no-wrap"><?php echo $article['id']; ?></td>
                                    <td class="px-6 py-4 whitespace-no-wrap"><?php echo $article['title']; ?></td>
                                    <td class="px-6 py-4 whitespace-no-wrap"><?php echo $article['published_at']; ?></td>
                                    <td class="px-6 py-4 whitespace-no-wrap">
                                        <a href="article_edit.php?id=<?php echo $article['id']; ?>" class="text-blue-600 hover:underline">修改</a>
                                        <a href="article_list.php?delete=<?php echo $article['id']; ?>" class="text-red-600 hover:underline ml-4" onclick="return confirm('确定要删除该文章吗？')">删除</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        批量删除
                    </button>
                </div>
            </form>
            <!-- 分页导航 -->
            <div class="mt-4">
                <nav class="flex items-center justify-between flex-wrap">
                    <div class="flex -space-x-px flex-wrap">
                        <?php if ($page > 1): ?>
                            <a href="article_list.php?page=<?php echo $page - 1; ?>" class="px-3 py-2 ml-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700">上一页</a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="article_list.php?page=<?php echo $i; ?>" class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 <?php echo $i == $page? 'bg-blue-500 text-white' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="article_list.php?page=<?php echo $page + 1; ?>" class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700">下一页</a>
                        <?php endif; ?>
                    </div>
                </nav>
            </div>
        </div>
    </main>
    <?php include 'includes/admin_footer.php'; ?>
</body>

</html>    