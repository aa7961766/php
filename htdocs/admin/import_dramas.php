<?php
// 开启错误显示，方便调试
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');

// 临时调整内存限制和执行时间
ini_set('memory_limit', '512M');
set_time_limit(300);

// 启动会话
session_start();

// 引入数据库连接文件
require_once '../config/database.php';
// 引入认证函数
require_once '../functions/auth.php';

// 检查用户是否已登录
checkAuth();

$error = '';
$success = '';
$importCount = 0;
$skipCount = 0;
$duplicateMessages = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    if ($file['error'] === 0) {
        $handle = fopen($file['tmp_name'], 'r');
        if ($handle) {
            // 跳过第一行（表头）
            fgetcsv($handle);

            try {
                $pdo->beginTransaction();
                $insertSql = "INSERT INTO dramas (title, link, created_at, tags) VALUES (:title, :link, :created_at, :tags)";
                $insertStmt = $pdo->prepare($insertSql);

                $checkSql = "SELECT id FROM dramas WHERE title = :title AND link = :link";
                $checkStmt = $pdo->prepare($checkSql);

                while (($data = fgetcsv($handle)) !== false) {
                    $title = isset($data[0]) ? trim($data[0]) : '';
                    $link = isset($data[1]) ? trim($data[1]) : '';
                    $created_at = isset($data[2]) ? trim($data[2]) : '';
                    $tags = isset($data[3]) ? trim($data[3]) : '';

                    // 假设 CSV 文件编码为 GBK，转换为 UTF-8
                    $title = mb_convert_encoding($title, 'UTF-8', 'GBK');
                    $link = mb_convert_encoding($link, 'UTF-8', 'GBK');
                    $created_at = mb_convert_encoding($created_at, 'UTF-8', 'GBK');
                    $tags = mb_convert_encoding($tags, 'UTF-8', 'GBK');

                    if (empty($title) || empty($link)) {
                        continue;
                    }

                    if (empty($created_at)) {
                        $created_at = date('Y-m-d H:i:s');
                    }

                    // 检查数据库中是否已存在相同的 title 和 link
                    $checkStmt->execute([
                        ':title' => $title,
                        ':link' => $link
                    ]);
                    if ($checkStmt->fetch()) {
                        $skipCount++;
                        $duplicateMessages[] = "<p class='text-yellow-500'>数据重复，跳过导入：标题 - {$title}，链接 - {$link}</p>";
                        continue;
                    }

                    try {
                        $insertStmt->execute([
                            ':title' => $title,
                            ':link' => $link,
                            ':created_at' => $created_at,
                            ':tags' => $tags
                        ]);
                        $importCount++;
                    } catch (PDOException $e) {
                        if ($e->getCode() === '23000') { // 重复数据错误码
                            $skipCount++;
                            $duplicateMessages[] = "<p class='text-yellow-500'>数据重复，跳过导入：标题 - {$title}，链接 - {$link}</p>";
                        } else {
                            $pdo->rollBack();
                            throw $e;
                        }
                    }
                }

                $pdo->commit();
                if ($importCount > 0) {
                    $success = "<p class='text-green-500 mb-4'>数据导入成功！共导入 {$importCount} 条记录，跳过 {$skipCount} 条重复记录</p>";
                } else {
                    $error = "未成功导入任何有效记录，请检查 CSV 文件内容。";
                }
            } catch (PDOException $e) {
                $error = "导入过程中出现错误: " . $e->getMessage();
            }

            fclose($handle);
        } else {
            $error = "无法打开 CSV 文件。";
        }
    } else {
        $error = "文件上传出错，请检查文件。";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>导入短剧数据</title>
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
                <li class="mb-2">
                    <a href="import_dramas.php"
                        class="flex items-center px-4 py-2 rounded-md text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                        <i class="fa-solid fa-file-import mr-3"></i>
                        导入短剧数据
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
        <h1 class="text-3xl font-bold text-gray-800 mb-6">欢迎，管理员</h1>
        <h2 class="text-2xl font-bold text-gray-800 mb-4">导入短剧数据</h2>
        <?php if ($success): ?>
            <?= $success ?>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="text-red-500 mb-4"><?= $error ?></p>
        <?php endif; ?>
        <?php if (!empty($duplicateMessages)): ?>
            <div class="mb-4">
                <?php foreach ($duplicateMessages as $message): ?>
                    <?= $message ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form action="import_dramas.php" method="post" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="csv_file" class="block text-gray-700 text-sm font-bold mb-2">选择 CSV 文件</label>
                <input type="file" id="csv_file" name="csv_file"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    accept=".csv" required>
            </div>
            <button type="submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                导入数据
            </button>
        </form>
    </div>
</body>

</html>    