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

// 获取要修改的文章信息
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $articleId = $_GET['id'];
    $selectQuery = "SELECT * FROM articles WHERE id = :id";
    $selectStmt = $conn->prepare($selectQuery);
    $selectStmt->bindParam(':id', $articleId, PDO::PARAM_INT);
    $selectStmt->execute();
    $article = $selectStmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        $message = "未找到该文章信息。";
    }
} else {
    $message = "无效的文章 ID。";
}

// 处理表单提交
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($article)) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $publish_time = $_POST['publish_time'];

    $updateQuery = "UPDATE articles SET title = :title, content = :content, published_at = :publish_time WHERE id = :id";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bindParam(':title', $title, PDO::PARAM_STR);
    $updateStmt->bindParam(':content', $content, PDO::PARAM_STR);
    $updateStmt->bindParam(':publish_time', $publish_time, PDO::PARAM_STR);
    $updateStmt->bindParam(':id', $articleId, PDO::PARAM_INT);

    if ($updateStmt->execute()) {
        $message = "文章信息修改成功！";
    } else {
        $errorInfo = $updateStmt->errorInfo();
        $message = "修改文章信息时出错: ". $errorInfo[2];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修改文章信息</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 font-sans">
    <?php include 'includes/admin_header.php'; ?>
    <main class="p-8">
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">修改文章信息</h2>
            <?php if ($message): ?>
                <div class="mb-4 <?php echo strpos($message, '成功')!== false? 'text-green-600' : 'text-red-600'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($article)): ?>
                <form action="article_edit.php?id=<?php echo $articleId; ?>" method="post">
                    <div class="mb-4">
                        <label for="title" class="block text-gray-700 font-bold mb-2">标题:</label>
                        <input type="text" id="title" name="title" value="<?php echo $article['title']; ?>" class="border border-gray-300 p-2 w-full">
                    </div>
                    <div class="mb-4">
                        <label for="content" class="block text-gray-700 font-bold mb-2">内容:</label>
                        <textarea id="content" name="content" class="border border-gray-300 p-2 w-full h-32"><?php echo $article['content']; ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="publish_time" class="block text-gray-700 font-bold mb-2">发布时间:</label>
                        <input type="datetime-local" id="publish_time" name="publish_time" value="<?php echo str_replace(' ', 'T', $article['published_at']); ?>" class="border border-gray-300 p-2 w-full">
                    </div>
                    <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        保存修改
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </main>
    <?php include 'includes/admin_footer.php'; ?>
</body>

</html>