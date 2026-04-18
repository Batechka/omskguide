<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT filename FROM images WHERE id = ?");
$stmt->execute([$id]);
$img = $stmt->fetch();
if ($img) {
    $filepath = UPLOAD_DIR . $img['filename'];
    if (file_exists($filepath)) unlink($filepath);
    $pdo->prepare("DELETE FROM images WHERE id = ?")->execute([$id]);
}
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
exit;
