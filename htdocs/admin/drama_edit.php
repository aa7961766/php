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

// 获取要修改的短剧信息
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $dramaId = $_GET['id'];
    $selectQuery = "SELECT * FROM dramas WHERE id = :id";
    $selectStmt = $conn->prepare($selectQuery);
    $selectStmt->bindParam(':id', $dramaId, PDO::PARAM_INT);
    $selectStmt->execute();
    $drama = $selectStmt->fetch(PDO::FETCH_ASSOC);

    if (!$drama) {
        $message = "未找到该短剧信息。";
    }
} else {
    $message = "无效的短剧 ID。";
}

// 处理表单提交
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($drama)) {
    $name = $_POST['name'];
    $link = $_POST['link'];
    $publish_time = $_POST['publish_time'];

    $updateQuery = "UPDATE dramas SET name = :name, link = :link, published_at = :publish_time WHERE id = :id";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bindParam(':name', $name, PDO::PARAM_STR);
    $updateStmt->bindParam(':link', $link, PDO::PARAM_STR);
    $updateStmt->bindParam(':publish_time', $publish_time, PDO::PARAM_STR);
    $updateStmt->bindParam(':id', $dramaId, PDO::PARAM_INT);

    if ($updateStmt->execute()) {
        $message = "短剧信息修改成功！";
    } else {
        $errorInfo = $updateStmt->errorInfo();
        $message = "修改短剧信息时出错: ". $errorInfo[2];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修改短剧信息</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 font-sans">
    <?php include 'includes/admin_header.php'; ?>
    <main class="p-8">
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">修改短剧信息</h2>
            <?php if ($message): ?>
                <div class="mb-4 <?php echo strpos($message, '成功')!== false? 'text-green-600' : 'text-red-600'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($drama)): ?>
                <form action="drama_edit.php?id=<?php echo $dramaId; ?>" method="post">
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 font-bold mb-2">名称:</label>
                        <input type="text" id="name" name="name" value="<?php echo $drama['name']; ?>" class="border border-gray-300 p-2 w-full">
                    </div>
                    <div class="mb-4">
                        <label for="link" class="block text-gray-700 font-bold mb-2">链接:</label>
                        <input type="text" id="link" name="link" value="<?php echo $drama['link']; ?>" class="border border-gray-300 p-2 w-full">
                    </div>
                    <div class="mb-4">
                        <label for="publish_time" class="block text-gray-700 font-bold mb-2">发布时间:</label>
                        <input type="datetime-local" id="publish_time" name="publish_time" value="<?php echo str_replace(' ', 'T', $drama['published_at']); ?>" class="border border-gray-300 p-2 w-full">
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