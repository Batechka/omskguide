<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

// Удаление маршрута
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM routes WHERE id = ?")->execute([$id]);
    header('Location: routes.php?msg=Удалено');
    exit;
}

$routes = $pdo->query("
    SELECT r.id, r.slug, r.distance, r.duration, r.stops_count, r.is_popular,
           MAX(CASE WHEN rt.language_code = 'ru' THEN rt.title END) as title_ru,
           MAX(CASE WHEN rt.language_code = 'en' THEN rt.title END) as title_en
    FROM routes r
    LEFT JOIN route_translations rt ON r.id = rt.route_id
    GROUP BY r.id
    ORDER BY r.id DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title>Управление маршрутами</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand">Маршруты</span>
            <div>
                <a href="index.php" class="btn btn-outline-light btn-sm">Достопримечательности</a>
                <a href="route_edit.php" class="btn btn-success btn-sm">+ Новый маршрут</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Выход</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
        <?php endif; ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Slug</th>
                    <th>RU Название</th>
                    <th>EN Название</th>
                    <th>Расстояние</th>
                    <th>Время</th>
                    <th>Популярный</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($routes as $route): ?>
                <tr>
                    <td><?= $route['id'] ?></td>
                    <td><?= htmlspecialchars($route['slug']) ?></td>
                    <td><?= htmlspecialchars($route['title_ru'] ?? '') ?></td>
                    <td><?= htmlspecialchars($route['title_en'] ?? '') ?></td>
                    <td><?= htmlspecialchars($route['distance'] ?? '') ?></td>
                    <td><?= htmlspecialchars($route['duration'] ?? '') ?></td>
                    <td><?= $route['is_popular'] ? '⭐' : '' ?></td>
                    <td>
                        <a href="route_edit.php?id=<?= $route['id'] ?>" class="btn btn-sm btn-primary">Ред.</a>
                        <a href="route_stops.php?route_id=<?= $route['id'] ?>" class="btn btn-sm btn-warning">Остановки</a>
                        <a href="?delete=<?= $route['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')">Уд.</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
