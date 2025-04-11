-- 文章分类表
CREATE TABLE IF NOT EXISTS article_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL
);

-- 文章表
CREATE TABLE IF NOT EXISTS articles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    category_id INTEGER,
    published_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    image_path TEXT,
    FOREIGN KEY (category_id) REFERENCES article_categories(id)
);

-- 短剧分类表
CREATE TABLE IF NOT EXISTS drama_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL
);

-- 短剧表
CREATE TABLE IF NOT EXISTS dramas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    link TEXT NOT NULL,
    description TEXT,
    category_id INTEGER,
    image_path TEXT,
    published_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES drama_categories(id)
);

-- 管理员表
CREATE TABLE IF NOT EXISTS admins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    email TEXT UNIQUE
);

-- 网站设置表
CREATE TABLE IF NOT EXISTS settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    site_name TEXT,
    site_url TEXT
);
-- 关键词表

CREATE TABLE search_keywords (
    id INT AUTO_INCREMENT PRIMARY KEY,
    keyword VARCHAR(255) NOT NULL UNIQUE,
    count INT NOT NULL DEFAULT 1
);
-- 插入初始管理员数据，密码为 admin123 的哈希值
INSERT OR IGNORE INTO admins (username, password, email) VALUES ('admin', '$2y$10$78KzXQ9V7f9W1J2u788J3e4K7X6d2H6k8W2N7p8J8m9T7f7g6K7Z2O', 'admin@example.com');

-- 插入初始网站设置数据（示例）
INSERT OR IGNORE INTO settings (site_name, site_url) VALUES ('文章管理系统', 'http://example.com');    