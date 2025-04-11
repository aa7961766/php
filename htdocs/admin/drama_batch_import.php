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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    // 检查文件上传是否成功
    if ($file['error'] === UPLOAD_ERR_OK) {
        $file_path = $file['tmp_name'];
        $file_content = file_get_contents($file_path);
        $encoding = mb_detect_encoding($file_content, ['UTF-8', 'GBK', 'GB2312']);
        if ($encoding && $encoding!== 'UTF-8') {
            $file_content = mb_convert_encoding($file_content, 'UTF-8', $encoding);
            $temp_file = tmpfile();
            fwrite($temp_file, $file_content);
            fseek($temp_file, 0);
            $handle = $temp_file;
        } else {
            $handle = fopen($file_path, 'r');
        }
        if ($handle) {
            $conn = connectToDatabase();
            // 开启事务以提高性能
            $conn->beginTransaction();

            // 跳过 CSV 文件的标题行
            fgetcsv($handle);

            $inserted_count = 0;
            while (($data = fgetcsv($handle))!== false) {
                $name = preg_replace('/\s+/', '', isset($data[0])? $data[0] : '');
                $link = preg_replace('/\s+/', '', isset($data[1])? $data[1] : '');

                // 检查是否有第三列（发布时间）
                $publish_time = '';
                if (isset($data[2]) &&!empty($data[2])) {
                    $publish_time = preg_replace('/\s+/', '', $data[2]);
                } else {
                    // 没有第三列，使用当前日期（不包含时分秒）
                    $publish_time = date('Y-m-d');
                }

                // 确保数据为 UTF - 8 编码
                $data = array_map(function ($value) {
                    return mb_convert_encoding($value, 'UTF-8', 'GBK,GB2312,UTF-8');
                }, $data);

                // 插入新数据，使用 published_at 字段
                $insert_query = "INSERT INTO dramas (name, link, published_at) VALUES (:name, :link, :publish_time)";
                $insert_stmt = $conn->prepare($insert_query);
                $insert_stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $insert_stmt->bindParam(':link', $link, PDO::PARAM_STR);
                $insert_stmt->bindParam(':publish_time', $publish_time, PDO::PARAM_STR);

                try {
                    if ($insert_stmt->execute()) {
                        $inserted_count++;
                    } else {
                        $errorInfo = $insert_stmt->errorInfo();
                        $message = "导入过程中出现错误，在插入数据 '$name' 时出错: ". $errorInfo[2];
                        $conn->rollBack();
                        break;
                    }
                } catch (PDOException $e) {
                    $message = "导入过程中出现错误，在插入数据 '$name' 时出错: ". $e->getMessage();
                    $conn->rollBack();
                    break;
                }
            }
            fclose($handle);

            if (empty($message)) {
                // 提交事务
                $conn->commit();
                $message = "数据导入成功！插入 $inserted_count 条记录。";
            }
        } else {
            $message = "无法打开上传的文件。";
        }
    } else {
        $message = "文件上传出错，错误代码: ". $file['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>批量导入短剧数据</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 font-sans">
    <?php include 'includes/admin_header.php'; ?>
    <main class="p-8">
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">批量导入短剧数据</h2>
            <?php if ($message): ?>
                <div class="mb-4 <?php echo strpos($message, '成功')!== false? 'text-green-600' : 'text-red-600'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <form action="drama_batch_import.php" method="post" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="csv_file" class="block text-gray-700 font-bold mb-2">选择 CSV 文件:</label>
                    <input type="file" id="csv_file" name="csv_file" class="border border-gray-300 p-2 w-full">
                </div>
                <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    上传文件
                </button>
            </form>
        </div>
    </main>
    <?php include 'includes/admin_footer.php'; ?>
</body>

</html>