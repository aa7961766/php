<?php
// 开启错误显示，方便调试
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 数据库文件路径
$dbFile = '../config/database.db';

// 检查数据库文件是否已存在
if (file_exists($dbFile)) {
    if (isset($_POST['override']) && $_POST['override'] === 'yes') {
        // 用户选择覆盖安装，删除旧的数据库文件
        if (!unlink($dbFile)) {
            echo "无法删除旧的数据库文件，请检查文件权限。";
            exit;
        }
    } else {
        // 提示用户是否覆盖安装
        echo '<form method="post">';
        echo '<p>数据库文件已存在，若要重新安装，请选择覆盖安装。</p>';
        echo '<input type="hidden" name="override" value="yes">';
        echo '<button type="submit">覆盖安装</button>';
        echo '</form>';
        exit;
    }
}

try {
    // 引入数据库连接文件
    require_once '../config/database.php';

    // 创建 users 表
    $createUsersTableSql = "
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    ";
    $pdo->exec($createUsersTableSql);

    // 创建 articles 表（直接包含 image 字段）
    $createArticlesTableSql = "
    CREATE TABLE IF NOT EXISTS articles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        content TEXT NOT NULL,
        author_id INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        intro TEXT DEFAULT '',
        image TEXT,  -- 直接添加 image 字段
        category_id INTEGER
    );
    ";
    $pdo->exec($createArticlesTableSql);

    // 创建 categories 表
    $createCategoriesTableSql = "
    CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    ";
    $pdo->exec($createCategoriesTableSql);

    // 创建 dramas 表
    $createDramasTableSql = "
    CREATE TABLE IF NOT EXISTS dramas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        link TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        tags TEXT
    );
    ";
    $pdo->exec($createDramasTableSql);

    // 可以删除原来的 ALTER TABLE 语句，因为 image 字段已在创建表时添加
    // $alterArticlesTableSql = "ALTER TABLE articles ADD COLUMN image TEXT;";
    // $pdo->exec($alterArticlesTableSql);

    // 插入默认管理员账号
    $username = 'admin';
    $password = 'admin123';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $checkSql = "SELECT COUNT(*) as count FROM users WHERE username = :username";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->bindParam(':username', $username, PDO::PARAM_STR);
    $checkStmt->execute();
    $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] == 0) {
        $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->execute();
    }

    echo "安装成功！";
    echo '<p><a href="../index.php">访问主页</a></p>';
    echo '<p><a href="../admin/login.php">访问后台</a></p>';
} catch (PDOException $e) {
    echo "安装过程中出现错误: " . $e->getMessage();
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }
} 
?>