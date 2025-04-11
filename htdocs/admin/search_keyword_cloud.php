<?php
session_start();
require_once '../includes/auth_functions.php';
require_once '../includes/db_connection.php';

if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit;
}

$conn = connectToDatabase();

// 查询搜索关键词及其搜索次数
$query = "SELECT keyword, count FROM search_keywords";
$stmt = $conn->query($query);
$keywords = $stmt->fetchAll(PDO::FETCH_ASSOC);

$conn = null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>搜索关键词云图</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        #cloud {
            width: 100%;
            min-height: 600px;
            margin: 20px auto;
            position: relative;
            border: 1px solid #ccc;
        }

       .keyword {
            position: absolute;
            cursor: pointer;
            transition: transform 0.2s ease-in-out;
        }

       .keyword:hover {
            transform: scale(1.1);
        }
    </style>
</head>

<body class="bg-gray-100 font-sans">
    <?php include 'includes/admin_header.php'; ?>
    <main class="p-8">
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">搜索关键词云图</h2>
        </div>
        <div id="cloud"></div>
    </main>
    <?php include 'includes/admin_footer.php'; ?>

    <script>
        const keywords = <?php echo json_encode($keywords); ?>;

        // 获取最大搜索次数，用于计算字体大小
        const maxCount = Math.max(...keywords.map(keyword => keyword.count));

        const cloud = document.getElementById('cloud');
        const cloudWidth = cloud.offsetWidth;
        const cloudHeight = cloud.offsetHeight;

        keywords.forEach(keyword => {
            const span = document.createElement('span');
            span.textContent = keyword.keyword;
            span.classList.add('keyword');

            // 根据搜索次数计算字体大小，范围在 16px 到 48px 之间
            const fontSize = Math.min(48, Math.max(16, (keyword.count / maxCount) * 48));
            span.style.fontSize = `${fontSize}px`;

            // 随机生成颜色
            const randomColor = `rgb(${Math.floor(Math.random() * 256)}, ${Math.floor(Math.random() * 256)}, ${Math.floor(Math.random() * 256)})`;
            span.style.color = randomColor;

            // 创建显示搜索次数的文本节点
            const countSpan = document.createElement('span');
            countSpan.textContent = ` (${keyword.count}次)`;
            countSpan.style.fontSize = '12px'; // 设置搜索次数的字体大小
            countSpan.style.color = '#666'; // 设置搜索次数的颜色

            span.appendChild(countSpan);

            // 随机生成 x 和 y 坐标
            const x = Math.random() * (cloudWidth - span.offsetWidth);
            const y = Math.random() * (cloudHeight - span.offsetHeight);

            span.style.left = `${x}px`;
            span.style.top = `${y}px`;

            cloud.appendChild(span);
        });
    </script>
</body>

</html>    