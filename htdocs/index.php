<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/db_connection.php';

$conn = connectToDatabase();
// 每页显示的记录数
$recordsPerPage = 10;

// 获取当前页码
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;

// 获取搜索关键词
$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';

// 若搜索关键词不为空，则记录搜索关键词
if (!empty($searchKeyword)) {
    // 记录搜索关键词
    $recordQuery = "INSERT OR REPLACE INTO search_keywords (id, keyword, count) 
                    VALUES ((SELECT id FROM search_keywords WHERE keyword = :keyword), :keyword, 
                            COALESCE((SELECT count FROM search_keywords WHERE keyword = :keyword), 0) + 1)";
    $recordStmt = $conn->prepare($recordQuery);
    $recordStmt->bindParam(':keyword', $searchKeyword, PDO::PARAM_STR);
    try {
        $recordStmt->execute();
    } catch (PDOException $e) {
        echo "记录搜索关键词时出错: ". $e->getMessage();
    }
}

// 计算偏移量
$offset = ($currentPage - 1) * $recordsPerPage;

try {
    // 构建查询条件
    $whereClause = '';
    $params = [];
    if (!empty($searchKeyword)) {
        $whereClause = "WHERE name LIKE :search";
        $params[':search'] = "%$searchKeyword%";
    }

    // 计算总记录数
    $countQuery = "SELECT COUNT(*) as total FROM dramas $whereClause ORDER BY published_at DESC";
    $countStmt = $conn->prepare($countQuery);
    if (!empty($params)) {
        $countStmt->execute($params);
    } else {
        $countStmt->execute();
    }
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // 计算总页数
    $totalPages = ceil($totalRecords / $recordsPerPage);

    // 获取当前页的数据，添加 published_at 字段到查询中，并按发布时间降序排序
    $query = "SELECT name, link, published_at FROM dramas $whereClause ORDER BY published_at DESC LIMIT :offset, :limit";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $recordsPerPage, PDO::PARAM_INT);
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
    }
    $stmt->execute();
    $dramas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "数据库查询出错: ". $e->getMessage();
    $dramas = [];
    $totalPages = 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>短剧数据主页</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <style>
        /* 为分页容器添加边框用于调试 */
       .pagination-container {
            border: 1px solid red;
            padding: 10px;
        }
        /* 添加红色字体样式 */
       .total-count {
            color: red;
        }
    </style>
</head>

<body class="bg-gray-100 font-sans">
    <?php include 'public/includes/header.php'; ?>
    <main class="p-4 md:p-8 mb-16">
        <!-- 搜索框 -->
        <form action="" method="get" class="mb-4">
            <div class="flex items-center">
                <input type="text" name="search" placeholder="搜索短剧名称" value="<?php echo htmlspecialchars($searchKeyword); ?>" class="border border-gray-300 rounded-l-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 w-full">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r-md hover:bg-blue-600 focus:outline-none focus:ring-blue-500">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>
        </form>

        <h2 class="text-2xl font-bold text-gray-800 mb-4">短剧数据列表</h2>
        <?php if (count($dramas) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                名称
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider md:block hidden">
                                链接
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                发布时间
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($dramas as $drama): ?>
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="<?php echo htmlspecialchars($drama['link']); ?>" class="text-blue-600 hover:underline">
                                        <?php echo htmlspecialchars($drama['name']); ?>
                                    </a>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap md:block hidden">
                                    <a href="<?php echo htmlspecialchars($drama['link']); ?>" class="text-blue-600 hover:underline">
                                        <?php echo htmlspecialchars($drama['link']); ?>
                                    </a>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <?php
                                    if ($drama['published_at']) {
                                        echo htmlspecialchars(date('Y-m-d', strtotime($drama['published_at'])));
                                    } else {
                                        echo '无';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- 使用 <h3> 标签显示短剧总数量 -->
            <h3 class="total-count">短剧总数量: <?php echo $totalRecords; ?></h3>
            <!-- 分页链接 -->
            <div class="mt-4 flex justify-center flex-wrap pagination-container">
                <?php if ($totalPages > 1): ?>
                    <ul class="flex space-x-2 flex-wrap justify-center">
                        <?php if ($currentPage > 1): ?>
                            <li><a href="?page=<?php echo $currentPage - 1;?>&search=<?php echo htmlspecialchars($searchKeyword); ?>" class="text-blue-600 hover:underline"><i class="fa-solid fa-chevron-left"></i></a></li>
                        <?php endif; ?>
                        <?php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);
                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                            <li>
                                <a href="?page=<?php echo $i;?>&search=<?php echo htmlspecialchars($searchKeyword); ?>" class="<?php echo ($i === $currentPage) ? 'text-white bg-blue-600 px-2 py-1 rounded' : 'text-blue-600 hover:underline'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <?php if ($currentPage < $totalPages): ?>
                            <li><a href="?page=<?php echo $currentPage + 1;?>&search=<?php echo htmlspecialchars($searchKeyword); ?>" class="text-blue-600 hover:underline"><i class="fa-solid fa-chevron-right"></i></a></li>
                        <?php endif; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-600">只有一页数据，无需分页。</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-600">暂无符合条件的短剧数据。</p>
        <?php endif; ?>
    </main>
    <?php include 'public/includes/footer.php'; ?>
</body>

</html>    