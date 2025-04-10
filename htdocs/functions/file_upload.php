<?php
function upload_file($file, $removeAdmin = false) {
    if (isset($file) && $file['error'] === 0) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = uniqid() . '_' . basename($file['name']);
        $filePath = $uploadDir . $fileName;
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $baseUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
            if ($removeAdmin) {
                $baseUrl .= str_replace('/admin', '', dirname($_SERVER['SCRIPT_NAME']));
            } else {
                $baseUrl .= str_replace('/admin/add_article.php', '', $_SERVER['REQUEST_URI']);
            }
            return $baseUrl . '/uploads/' . $fileName;
        }
    }
    return '';
}