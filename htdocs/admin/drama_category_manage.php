<?php
session_start();
require_once '../includes/auth_functions.php';
require_once '../includes/db_connection.php';

if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit;
}

$conn = connectToDatabase();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $stmt = $conn->prepare("INSERT INTO drama_categories (name) VALUES (:name)");
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->execute();
    $message = "短剧分类已添加。";
}

$stmt = $conn->prepare("SELECT id, name FROM drama_categories");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$conn = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>短剧分类管理</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <?php include 'includes/admin_header.php';?>
    <main class="p-8">
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">短剧分类管理</h2>
            <?php if (isset($message)) {?>
                <p class="text-green-600 text-sm mb-4"><?php echo $message;?></p>
            <?php }?>
        </div>
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">分类名称:</label>
                    <input type="text" id="name" name="name" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        添加分类
                    </button>
                </div>
            </form>
        </div>
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">现有分类</h3>
            <ul class="space-y-2">
                <?php foreach ($categories as $category):?>
                    <li class="flex justify-between items-center border-b border-gray-200 pb-2">
                        <span class="text-gray-700"><?php echo $category['name'];?></span>
                        <!-- 这里可以添加编辑和删除按钮等功能，后续可完善 -->
                        <div class="flex space-x-2">
                            <button type="button" class="text-blue-500 hover:text-blue-700 focus:outline-none">
                                <i class="fa-solid fa-pen-to-square"></i> 编辑
                            </button>
                            <button type="button" class="text-red-500 hover:text-red-700 focus:outline-none">
                                <i class="fa-solid fa-trash"></i> 删除
                            </button>
                        </div>
                    </li>
                <?php endforeach;?>
            </ul>
        </div>
    </main>
    <?php include 'includes/admin_footer.php';?>
</body>
</html>