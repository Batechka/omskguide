<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM article_categories WHERE id = ?")->execute([$id]);
    header('Location: article_categories.php');
    exit;
}

$categories = $pdo->query("
    SELECT c.id, c.slug,
           MAX(CASE WHEN ct.language_code = 'ru' THEN ct.name END) as name_ru,
           MAX(CASE WHEN ct.language_code = 'en' THEN ct.name END) as name_en
    FROM article_categories c
    LEFT JOIN article_category_translations ct ON c.id = ct.category_id
    GROUP BY c.id
    ORDER BY c.id
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Категории статей</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand">Категории статей</span>
            <div>
                <a href="index.php" class="btn btn-outline-light btn-sm">Главная админки</a>
                <a href="article_category_edit.php" class="btn btn-success btn-sm">+ Добавить</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Выход</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Slug</th>
                    <th>RU</th>
                    <th>EN</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?= $cat['id'] ?></td>
                        <td><?= htmlspecialchars($cat['slug']) ?></td>
                        <td><?= htmlspecialchars($cat['name_ru'] ?? '') ?></td>
                        <td><?= htmlspecialchars($cat['name_en'] ?? '') ?></td>
                        <td>
                            <a href="article_category_edit.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-primary">Ред</a>
                            <a href="?delete=<?= $cat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')">Уд</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
