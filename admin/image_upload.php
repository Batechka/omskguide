<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $attraction_id = (int)$_POST['attraction_id'];
    $file = $_FILES['image'];
    $alt = trim($_POST['alt'] ?? '');
    if (empty($alt)) {
        $stmt = $pdo->prepare("SELECT title FROM attraction_translations WHERE attraction_id = ? AND language_code = 'ru'");
        $stmt->execute([$attraction_id]);
        $title = $stmt->fetchColumn();
        $alt = $title ? 'Фото: ' . $title : 'Достопримечательность Омска';
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;
    $target = UPLOAD_DIR . $filename;

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $target)) {
        $stmt = $pdo->prepare("INSERT INTO images (attraction_id, filename, alt_text) VALUES (?, ?, ?)");
        $stmt->execute([$attraction_id, $filename, $alt]);
        echo json_encode(['success' => true]);
        exit;
    }
}
echo json_encode(['success' => false]);
