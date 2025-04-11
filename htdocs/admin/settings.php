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
    $siteName = $_POST['site_name'];
    $siteUrl = $_POST['site_url'];

    $stmt = $conn->prepare("UPDATE settings SET site_name = :site_name, site_url = :site_url WHERE id = 1");
    $stmt->bindParam(':site_name', $siteName, PDO::PARAM_STR);
    $stmt->bindParam(':site_url', $siteUrl, PDO::PARAM_STR);
    $stmt->execute();

    $message = "设置已更新。";
}

$stmt = $conn->prepare("SELECT site_name, site_url FROM settings WHERE id = 1");
$stmt->execute();
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

$conn = null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>网站基础信息设置</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 font-sans">
    <?php include 'includes/admin_header.php'; ?>
    <main class="p-8">
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">网站基础信息设置</h2>
            <?php if (isset($message)) { ?>
                <p class="text-green-600 text-sm mb-4"><?php echo $message; ?></p>
            <?php } ?>
        </div>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="bg-white shadow-md rounded-lg p-6 space-y-6">
            <div>
                <label for="site_name" class="block text-sm font-medium text-gray-700">网站名称:</label>
                <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_name']); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="site_url" class="block text-sm font-medium text-gray-700">网站网址:</label>
                <input type="text" id="site_url" name="site_url" value="<?php echo htmlspecialchars($settings['site_url']); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    保存设置
                </button>
            </div>
        </form>
    </main>
    <?php include 'includes/admin_footer.php'; ?>
</body>

</html>    