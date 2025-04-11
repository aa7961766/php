<?php
function uploadImage($file, $targetDir = 'public/images/') {
    $targetFile = $targetDir . basename($file["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // 检查文件是否为真正的图片
    $check = getimagesize($file["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo "文件不是图片。";
        $uploadOk = 0;
    }

    // 检查文件是否已存在
    if (file_exists($targetFile)) {
        echo "文件已存在。";
        $uploadOk = 0;
    }

    // 检查文件大小
    if ($file["size"] > 500000) {
        echo "文件太大。";
        $uploadOk = 0;
    }

    // 允许的文件类型
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif") {
        echo "只允许 JPG, JPEG, PNG & GIF 文件。";
        $uploadOk = 0;
    }

    // 检查是否有错误
    if ($uploadOk == 0) {
        echo "文件未上传。";
        return false;
    } else {
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            return $targetFile;
        } else {
            echo "上传文件时出错。";
            return false;
        }
    }
}
?>    