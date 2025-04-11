<?php

// SQLite 数据库文件路径

$db_file = 'database/articles.db';



// 备份文件保存目录

$backup_dir = __DIR__ . '/backups/';



// 确保备份目录存在

if (!is_dir($backup_dir)) {

    mkdir($backup_dir, 0777, true);

}



// 生成备份文件名，按年月日格式

$date = date('Ymd');

$backup_file = $backup_dir . 'backup_' . $date . '.db';



// 检测当天是否已经备份

if (file_exists($backup_file)) {

    echo "今天已经备份过，无需再次备份。备份文件路径: {$backup_file}";

} else {

    // 复制数据库文件进行备份

    if (copy($db_file, $backup_file)) {

        echo "备份成功，文件路径: {$backup_file}";

    } else {

        echo "备份失败，可能是权限问题或文件不存在。";

    }

}

?>