<?php
include 'public/includes/header.php';
require_once 'includes/db_connection.php';

$conn = connectToDatabase();
$stmt = $conn->query("SELECT articles.id, articles.title, substr(articles.content, 1, 100) as summary, articles.published_at, article_categories.name as category_name 
                      FROM articles 
                      JOIN article_categories ON articles.category_id = article_categories.id");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
$conn = null;
?>

<h2>文章列表</h2>
<table class="table-auto">
    <thead>
        <tr>
            <th class="px-4 py-2">标题</th>
            <th class="px-4 py-2">摘要</th>
            <th class="px-4 py-2">分类</th>
            <th class="px-4 py-2">发布时间</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($articles as $article): ?>
            <tr>
                <td class="border px-4 py-2"><a href="article_detail.php?id=<?php echo $article['id']; ?>"><?php echo $article['title']; ?></a></td>
                <td class="border px-4 py-2"><?php echo $article['summary']; ?></td>
                <td class="border px-4 py-2"><?php echo $article['category_name']; ?></td>
                <td class="border px-4 py-2"><?php echo $article['published_at']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'public/includes/footer.php'; ?>