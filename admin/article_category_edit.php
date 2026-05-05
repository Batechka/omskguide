<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

$id = $_GET['id'] ?? null;
$category = null;
$translations = ['ru' => [], 'en' => []];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM article_categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch();
    if (!$category) die('Категория не найдена');
    $stmt = $pdo->prepare("SELECT * FROM article_category_translations WHERE category_id = ?");
    $stmt->execute([$id]);
    while ($row = $stmt->fetch()) $translations[$row['language_code']] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slug = trim($_POST['slug'] ?? '');
    if (empty($slug)) { $error = 'Slug обязателен'; }
    else {
        try {
            if ($id) {
                $pdo->prepare("UPDATE article_categories SET slug = ? WHERE id = ?")->execute([$slug, $id]);
            } else {
                $pdo->prepare("INSERT INTO article_categories (slug) VALUES (?)")->execute([$slug]);
                $id = $pdo->lastInsertId();
            }
            foreach (['ru','en'] as $lang_code) {
                $name = $_POST["name_$lang_code"] ?? '';
                $pdo->prepare("INSERT INTO article_category_translations (category_id, language_code, name)
                    VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name)")->execute([$id, $lang_code, $name]);
            }
            header('Location: article_categories.php');
            exit;
        } catch (PDOException $e) { $error = $e->getMessage(); }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Редактировать' : 'Новая' ?> категория</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand">Категория</span>
            <div>
                <a href="article_categories.php" class="btn btn-outline-light btn-sm">Назад</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Выход</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <?php if (isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label>Slug</label>
                <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($category['slug'] ?? '') ?>" required>
            </div>
            <ul class="nav nav-tabs">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#ru" type="button">RU</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#en" type="button">EN</button></li>
            </ul>
            <div class="tab-content mt-3">
                <?php foreach(['ru','en'] as $lang_code): ?>
                <div class="tab-pane fade <?= $lang_code=='ru'?'show active':'' ?>" id="<?= $lang_code ?>">
                    <div class="mb-3">
                        <label>Название (<?= strtoupper($lang_code) ?>)</label>
                        <input type="text" name="name_<?= $lang_code ?>" class="form-control" value="<?= htmlspecialchars($translations[$lang_code]['name'] ?? '') ?>">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
