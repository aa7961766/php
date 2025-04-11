<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理 - 文章管理系统</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <script>
        // 用于切换导航菜单显示状态的函数
        function toggleNav() {
            const nav = document.querySelector('nav ul');
            nav.classList.toggle('hidden');
        }
    </script>
</head>
<body>
    <header class="bg-gray-800 text-white p-4">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">后台管理</h1>
            <!-- 手机端导航切换按钮 -->
            <button class="md:hidden" onclick="toggleNav()">
                <i class="fa-solid fa-bars text-white"></i>
            </button>
        </div>
        <nav class="mt-2">
            <ul class="md:flex md:flex-row md:space-x-8 md:space-y-0 flex-col space-y-2 hidden md:block">
                <li><a href="../index.php" class="flex items-center hover:underline">
                    <span class="mr-1"><i class="fa-solid fa-house"></i></span>网站首页
                </a></li>
                <li><a href="dashboard.php" class="flex items-center hover:underline">
                    <span class="mr-1"><i class="fa-solid fa-chart-pie"></i></span>后台首页
                </a></li>
                <li><a href="article_manage.php" class="flex items-center hover:underline">
                    <span class="mr-1"><i class="fa-solid fa-file-alt"></i></span>文章管理
                </a></li>
                <li><a href="category_manage.php" class="flex items-center hover:underline">
                    <span class="mr-1"><i class="fa-solid fa-folder"></i></span>文章分类管理
                </a></li>
                <li><a href="article_list.php" class="flex items-center hover:underline">
                    <span class="mr-1"><i class="fa-solid fa-folder"></i></span>文章列表
                </a></li>
                <li><a href="drama_manage.php" class="flex items-center hover:underline">
                    <span class="mr-1"><i class="fa-solid fa-video"></i></span>短剧管理
                </a></li>
                <li><a href="drama_category_manage.php" class="flex items-center hover:underline">
                    <span class="mr-1"><i class="fa-solid fa-theater-masks"></i></span>短剧分类管理
                </a></li>
                <li><a href="drama_batch_import.php" class="flex items-center hover:underline">
                    <span class="mr-1"><i class="fa-solid fa-file-import"></i></span>批量导入短剧
                </a></li>
                <li><a href="drama_list.php" class="flex items-center hover:underline">
                    <span class="mr-1"><i class="fa-solid fa-file-import"></i></span>短剧列表
                </a></li>
                <li><a href="search_keyword_cloud.php" class="flex items-center hover:underline">
                    <span class="mr-1"><i class="fa-solid fa-file-import"></i></span>关键词云图
                </a></li>
                <li><a href="settings.php" class="flex items-center hover:underline">
                    <span class="mr-1"><i class="fa-solid fa-gear"></i></span>网站设置
                </a></li>
            </ul>
        </nav>
    </header>    