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