<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowed)) {
        http_response_code(400);
        echo 'Недопустимый формат';
        exit;
    }

    $filename = 'article_' . uniqid() . '.' . $ext;
    $target_dir = __DIR__ . '/../uploads/articles/content/';

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $target = $target_dir . $filename;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        echo BASE_URL . 'uploads/articles/content/' . $filename;
        exit;
    }
}

http_response_code(400);
echo 'Ошибка загрузки';
