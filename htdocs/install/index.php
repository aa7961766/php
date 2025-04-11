<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文章管理系统安装</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex flex-col items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">文章管理系统安装</h1>
        <?php
        require_once '../includes/db_connection.php';

        $conn = connectToDatabase();
        $dbName = 'articles.db';
        $installFlagFile = __DIR__ . '/installed.flag';

        $reinstall = isset($_GET['reinstall']) && $_GET['reinstall'] === 'true';

        try {
            // 检查是否已经安装（通过标记文件）
            if (file_exists($installFlagFile)) {
                if ($reinstall) {
                    $result = "请手动删除 installed.flag 文件后再尝试重新安装。";
                } else {
                    $result = "系统已经安装，若要重新安装，请先手动删除 installed.flag 文件。";
                }
            } else {
                // 定义要检查的表名数组
                $tablesToCheck = [
                    'article_categories',
                    'articles',
                    'drama_categories',
                    'dramas',
                    'admins',
                    'settings',
		    'search_keywords'
                ];

                $allTablesExist = true;
                foreach ($tablesToCheck as $tableName) {
                    $checkTableQuery = "SELECT name FROM sqlite_master WHERE type='table' AND name='$tableName'";
                    $stmt = $conn->query($checkTableQuery);
                    if (!$stmt->fetchColumn()) {
                        $allTablesExist = false;
                        break;
                    }
                }

                if ($allTablesExist) {
                    // 若表存在但标记文件不存在，可能是标记文件被手动删除，可重新安装
                    if ($reinstall) {
                        // 对于SQLite，删除数据库文件来模拟删除数据库
                        if (file_exists($dbName)) {
                            unlink($dbName);
                            // 重新连接数据库
                            $conn = connectToDatabase();
                        }
                    } else {
                        // 生成标记文件
                        file_put_contents($installFlagFile, 'Installed');
                        $result = "系统已经安装，若要重新安装，请先手动删除 installed.flag 文件。";
                    }
                }

                if (!isset($result)) {
                    // 读取 SQL 文件内容
                    $sql = file_get_contents(__DIR__ . '/install.sql');

                    // 生成 admin123 的哈希值
                    $password = 'admin123';
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    // 替换 SQL 语句中的密码哈希值
                    $sql = str_replace("'$2y$10$78KzXQ9V7f9W1J2u788J3e4K7X6d2H6k8W2N7p8J8m9T7f7g6K7Z2O'", "'$hashedPassword'", $sql);

                    // 执行 SQL 语句
                    $conn->exec($sql);
                    $result = "安装成功！";

                    // 安装成功后创建标记文件
                    file_put_contents($installFlagFile, 'Installed');
                }
            }
        } catch (PDOException $e) {
            $result = "安装失败: " . $e->getMessage();
        } catch (Exception $e) {
            $result = "操作出错: " . $e->getMessage();
        }

        $conn = null;
        ?>
        <div class="result text-center mb-6">
            <?php if (strpos($result, '成功')!== false): ?>
                <p class="text-green-500"><?php echo $result; ?></p>
            <?php else: ?>
                <p class="text-red-500"><?php echo $result; ?></p>
            <?php endif; ?>
        </div>
        <?php if (strpos($result, '已经安装')!== false): ?>
            <div class="text-center mb-6">
                <a href="#" onclick="if(confirm('确认要重新安装吗？此操作将删除现有数据！')) { window.location.href='?reinstall=true'; } return false;" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 focus:outline-none focus:ring-red-500">重新安装</a>
            </div>
        <?php endif; ?>
        <div class="links text-center">
            <a href="../index.php" class="text-blue-500 hover:underline">网站首页</a>
            <a href="../admin/login.php" class="text-blue-500 hover:underline ml-4">后台管理登录</a>
        </div>
    </div>
</body>

</html>    