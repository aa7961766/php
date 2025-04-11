<?php
include 'public/includes/header.php';
require_once 'includes/db_connection.php';

if (isset($_GET['id'])) {
    $articleId = $_GET['id'];
    $conn = connectToDatabase();
    $stmt = $conn->prepare("SELECT articles.id, articles.title, articles.content, articles.image_path, article_categories.name as category_name 
                            FROM articles 
                            JOIN article_categories ON articles.category_id = article_categories.id 
                            WHERE articles.id = :id");
    $stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
    $stmt->execute();
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
    $conn = null;

    if ($article) {
        echo "<h2>{$article['title']}</h2>";
        if ($article['image_path']) {
            echo "<img src='{$article['image_path']}' alt='文章图片'>";
        }
        echo "<p>{$article['content']}</p>";
        echo "<p>分类: {$article['category_name']}</p>";
    } else {
        echo "文章未找到。";
    }
} else {
    echo "无效的文章ID。";
}

include 'public/includes/footer.php';