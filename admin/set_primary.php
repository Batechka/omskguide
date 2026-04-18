<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT attraction_id FROM images WHERE id = ?");
$stmt->execute([$id]);
$img = $stmt->fetch();

if ($img) {
    $pdo->prepare("UPDATE images SET is_primary = 0 WHERE attraction_id = ?")->execute([$img['attraction_id']]);
    $pdo->prepare("UPDATE images SET is_primary = 1 WHERE id = ?")->execute([$id]);
}
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
exit;
