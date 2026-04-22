<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

$id = $_GET['id'] ?? null;
$route = null;
$translations = ['ru' => [], 'en' => []];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM routes WHERE id = ?");
    $stmt->execute([$id]);
    $route = $stmt->fetch();
    if (!$route) die('Маршрут не найден');

    // Получаем переводы
    $stmt = $pdo->prepare("SELECT * FROM route_translations WHERE route_id = ?");
    $stmt->execute([$id]);
    while ($row = $stmt->fetch()) {
        $translations[$row['language_code']] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slug = trim($_POST['slug'] ?? '');
    $distance = $_POST['distance'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $is_popular = isset($_POST['is_popular']) ? 1 : 0;

    if (empty($slug)) {
        $error = 'Slug обязателен';
    } else {
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE routes SET slug=?, distance=?, duration=?, is_popular=? WHERE id=?");
                $stmt->execute([$slug, $distance, $duration, $is_popular, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO routes (slug, distance, duration, is_popular) VALUES (?, ?, ?, ?)");
                $stmt->execute([$slug, $distance, $duration, $is_popular]);
                $id = $pdo->lastInsertId();
            }

            // Сохраняем переводы
            foreach (['ru', 'en'] as $lang_code) {
                $title = $_POST["title_$lang_code"] ?? '';
                $short = $_POST["short_$lang_code"] ?? '';
                $full = $_POST["full_$lang_code"] ?? '';

                $stmt = $pdo->prepare("INSERT INTO route_translations
                    (route_id, language_code, title, short_description, full_description)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    title = VALUES(title), short_description = VALUES(short_description), full_description = VALUES(full_description)");
                $stmt->execute([$id, $lang_code, $title, $short, $full]);
            }

            // Обновляем количество остановок
            $stmt = $pdo->prepare("UPDATE routes SET stops_count = (SELECT COUNT(*) FROM route_stops WHERE route_id = ?) WHERE id = ?");
            $stmt->execute([$id, $id]);

            header('Location: routes.php?msg=Сохранено');
            exit;
        } catch (PDOException $e) {
            $error = 'Ошибка: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Редактирование' : 'Новый' ?> маршрут</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand"><?= $id ? 'Редактирование' : 'Новый маршрут' ?></span>
            <div>
                <a href="routes.php" class="btn btn-outline-light btn-sm">К списку</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Выход</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="card mb-4">
                <div class="card-header">Основные параметры</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug (URL)</label>
                            <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($route['slug'] ?? '') ?>" required>
                            <small>Например: omsk-1-day</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Расстояние</label>
                            <input type="text" name="distance" class="form-control" value="<?= htmlspecialchars($route['distance'] ?? '') ?>" placeholder="7 км">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Длительность</label>
                            <input type="text" name="duration" class="form-control" value="<?= htmlspecialchars($route['duration'] ?? '') ?>" placeholder="5 часов">
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="is_popular" class="form-check-input" id="popularCheck" <?= isset($route['is_popular']) && $route['is_popular'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="popularCheck">Популярный маршрут ⭐</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="nav nav-tabs">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#ru" type="button">Русский</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#en" type="button">English</button></li>
            </ul>
            <div class="tab-content mt-3">
                <?php foreach (['ru', 'en'] as $lang_code): ?>
                    <div class="tab-pane fade <?= $lang_code=='ru'?'show active':'' ?>" id="<?= $lang_code ?>">
                        <div class="mb-3">
                            <label class="form-label">Название (<?= strtoupper($lang_code) ?>)</label>
                            <input type="text" name="title_<?= $lang_code ?>" class="form-control" value="<?= htmlspecialchars($translations[$lang_code]['title'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Краткое описание</label>
                            <textarea name="short_<?= $lang_code ?>" class="form-control" rows="2"><?= htmlspecialchars($translations[$lang_code]['short_description'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Полное описание</label>
                            <textarea name="full_<?= $lang_code ?>" class="form-control" rows="4"><?= htmlspecialchars($translations[$lang_code]['full_description'] ?? '') ?></textarea>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Сохранить</button>
                <a href="routes.php" class="btn btn-secondary">Отмена</a>
                <?php if ($id): ?>
                    <a href="route_stops.php?id=<?= $id ?>" class="btn btn-info">Управление остановками</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
