<?php
session_start();
require_once '../includes/auth_functions.php';
require_once '../includes/db_connection.php';
require_once '../includes/image_upload.php';

if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit;
}

$conn = connectToDatabase();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $categoryId = $_POST['category_id'];

    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $imagePath = uploadImage($_FILES['image']);
    }

    $stmt = $conn->prepare("INSERT INTO articles (title, content, category_id, image_path) VALUES (:title, :content, :category_id, :image_path)");
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->bindParam(':content', $content, PDO::PARAM_STR);
    $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
    $stmt->bindParam(':image_path', $imagePath, PDO::PARAM_STR);
    $stmt->execute();

    $message = "文章已添加。";
}

$stmt = $conn->prepare("SELECT id, name FROM article_categories");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$conn = null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>添加文章</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 font-sans">
    <?php include 'includes/admin_header.php'; ?>
    <main class="p-8">
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">添加文章</h2>
            <?php if (isset($message)) { ?>
                <p class="text-green-600 text-sm mb-4"><?php echo $message; ?></p>
            <?php } ?>
        </div>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data" class="bg-white shadow-md rounded-lg p-6 space-y-6">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">文章标题:</label>
                <input type="text" id="title" name="title" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="content" class="block text-sm font-medium text-gray-700">文章内容:</label>
                <textarea id="content" name="content" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm h-32"></textarea>
            </div>
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700">文章分类:</label>
                <select id="category_id" name="category_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="image" class="block text-sm font-medium text-gray-700">文章图片:</label>
                <input type="file" id="image" name="image" class="mt-1 block w-full">
            </div>
            <div>
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    添加文章
                </button>
            </div>
        </form>
    </main>
    <?php include 'includes/admin_footer.php'; ?>
</body>

</html>    