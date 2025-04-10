<?php
// 开启错误显示，方便调试
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 启动会话
session_start();

// 销毁会话中的所有数据
session_unset();

// 销毁会话
session_destroy();

// 重定向到登录页面
header("Location: login.php");
exit;
?>    