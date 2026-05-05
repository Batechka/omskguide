<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT image FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    if ($img) { $file = __DIR__ . '/../uploads/articles/' . $img; if (file_exists($file)) unlink($file); }
    $pdo->prepare("DELETE FROM articles WHERE id = ?")->execute([$id]);
    header('Location: articles.php'); exit;
}

$articles = getAllArticlesAdmin();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Статьи</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand">Статьи</span>
            <div>
                <a href="index.php" class="btn btn-outline-light btn-sm">Достопримечательности</a>
                <a href="article_edit.php" class="btn btn-success btn-sm">+ Добавить</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Выход</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <table class="table">
            <thead><tr><th>ID</th><th>Slug</th><th>RU Title</th><th>EN Title</th><th>Опубл.</th><th>Действия</th></tr></thead>
            <tbody>
                <?php foreach ($articles as $a): ?>
                <tr>
                    <td><?= $a['id'] ?></td>
                    <td><?= htmlspecialchars($a['slug']) ?></td>
                    <td><?= htmlspecialchars($a['title_ru'] ?? '') ?></td>
                    <td><?= htmlspecialchars($a['title_en'] ?? '') ?></td>
                    <td><?= $a['is_published'] ? '✅' : '❌' ?></td>
                    <td>
                        <a href="article_edit.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-primary">Ред</a>
                        <a href="?delete=<?= $a['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')">Уд</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
