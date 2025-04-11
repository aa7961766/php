<?php
require_once 'db_connection.php';

// 验证管理员登录
function authenticateAdmin($username, $password) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = :username");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    $conn = null;

    if ($admin) {
        echo "找到用户，用户名: ". $admin['username']. "<br>";
        if (password_verify($password, $admin['password'])) {
            echo "密码验证成功！<br>";
            return $admin;
        } else {
            echo "密码验证失败！<br>";
        }
    } else {
        echo "未找到用户！<br>";
    }
    return false;
}

// 检查管理员是否已登录
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// 注销管理员
function logoutAdmin() {
    session_destroy();
    header("Location:login.php");
    exit;
}
?>