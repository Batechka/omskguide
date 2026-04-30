<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

$id = $_GET['id'] ?? null;
$category = null;
$translations = ['ru' => [], 'en' => []];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch();
    if (!$category) die('Категория не найдена');

    $stmt = $pdo->prepare("SELECT language_code, name FROM category_translations WHERE category_id = ?");
    $stmt->execute([$id]);
    while ($row = $stmt->fetch()) {
        $translations[$row['language_code']] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slug = trim($_POST['slug'] ?? '');
    $color = $_POST['color'] ?? '#b34e3a';
    if (empty($slug)) {
        $error = 'Slug обязателен';
    } else {
        try {
            if ($id) {
                $pdo->prepare("UPDATE categories SET slug = ?, color = ? WHERE id = ?")->execute([$slug, $color, $id]);
            } else {
                $pdo->prepare("INSERT INTO categories (slug, color) VALUES (?, ?)")->execute([$slug, $color]);
                $id = $pdo->lastInsertId();
            }
            foreach (['ru','en'] as $lang_code) {
                $name = $_POST["name_$lang_code"] ?? '';
                $pdo->prepare("INSERT INTO category_translations (category_id, language_code, name)
                    VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name)")
                    ->execute([$id, $lang_code, $name]);
            }
            header('Location: categories.php');
            exit;
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
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
                <a href="categories.php" class="btn btn-outline-light btn-sm">Назад</a>
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
                <!-- Цвет категории -->
                <div class="mt-3 mb-3">
                    <label class="form-label">Цвет категории</label>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <?php
                        $colors = ['#b34e3a','#e67e22','#f1c40f','#2ecc71','#3498db','#9b59b6','#1abc9c','#e74c3c'];
                        foreach ($colors as $c) {
                            $checked = (isset($category['color']) && $category['color'] == $c) ? '2px solid black' : 'none';
                            echo "<button type='button' style='width:36px;height:36px;background:$c;border:$checked;border-radius:8px;margin:2px;' onclick=\"document.querySelector('[name=color]').value='$c'\"></button>";
                        }
                        ?>
                    </div>
                    <input type="color" name="color" id="colorPicker" class="form-control form-control-color mt-2"
                        value="<?= htmlspecialchars($category['color'] ?? '#b34e3a') ?>"
                        onchange="this.form.elements['color'].value = this.value">
                    <small class="text-muted">Выберите цвет кнопками или палитрой</small>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
