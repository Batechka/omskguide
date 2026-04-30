<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

$route_id = $_GET['route_id'] ?? 0;
if (!$route_id) {
    header('Location: routes.php');
    exit;
}

$stmt = $pdo->prepare("SELECT r.*, rt.title FROM routes r
                       JOIN route_translations rt ON r.id = rt.route_id AND rt.language_code = 'ru'
                       WHERE r.id = ?");
$stmt->execute([$route_id]);
$route = $stmt->fetch();
if (!$route) {
    die('Маршрут не найден');
}

// Удаление остановки
if (isset($_GET['delete'])) {
    $stop_id = (int)$_GET['delete'];
    // Удаляем файл изображения, если он есть
    $stmt = $pdo->prepare("SELECT image FROM route_stops WHERE id = ?");
    $stmt->execute([$stop_id]);
    $img = $stmt->fetchColumn();
    if ($img && file_exists(UPLOAD_DIR . '../routes/' . $img)) {
        unlink(UPLOAD_DIR . '../routes/' . $img);
    }
    $pdo->prepare("DELETE FROM route_stops WHERE id = ? AND route_id = ?")->execute([$stop_id, $route_id]);
    header("Location: route_stops.php?route_id=$route_id");
    exit;
}

// Перемещение вверх/вниз (без изменений)
if (isset($_GET['up'])) {
    $stop_id = (int)$_GET['up'];
    $stmt = $pdo->prepare("SELECT stop_order FROM route_stops WHERE id = ?");
    $stmt->execute([$stop_id]);
    $current = $stmt->fetchColumn();
    if ($current > 1) {
        $pdo->prepare("UPDATE route_stops SET stop_order = stop_order + 1 WHERE route_id = ? AND stop_order = ? - 1")->execute([$route_id, $current]);
        $pdo->prepare("UPDATE route_stops SET stop_order = stop_order - 1 WHERE id = ?")->execute([$stop_id]);
    }
    header("Location: route_stops.php?route_id=$route_id");
    exit;
}
if (isset($_GET['down'])) {
    $stop_id = (int)$_GET['down'];
    $stmt = $pdo->prepare("SELECT stop_order FROM route_stops WHERE id = ?");
    $stmt->execute([$stop_id]);
    $current = $stmt->fetchColumn();
    $max = $pdo->prepare("SELECT MAX(stop_order) FROM route_stops WHERE route_id = ?");
    $max->execute([$route_id]);
    $maxOrder = $max->fetchColumn();
    if ($current < $maxOrder) {
        $pdo->prepare("UPDATE route_stops SET stop_order = stop_order - 1 WHERE route_id = ? AND stop_order = ? + 1")->execute([$route_id, $current]);
        $pdo->prepare("UPDATE route_stops SET stop_order = stop_order + 1 WHERE id = ?")->execute([$stop_id]);
    }
    header("Location: route_stops.php?route_id=$route_id");
    exit;
}

// Добавление остановки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_stop'])) {
    $stop_type = $_POST['stop_type'] ?? 'existing';
    $attraction_id = $_POST['attraction_id'] ?? null;
    $custom_title = trim($_POST['custom_title'] ?? '');
    $custom_description = trim($_POST['custom_description'] ?? '');
    $walk_time = trim($_POST['walk_time'] ?? '');
    $uploaded_image = null;

    // Обработка загрузки изображения
    if (isset($_FILES['stop_image']) && $_FILES['stop_image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['stop_image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $target_dir = UPLOAD_DIR . '../routes/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $target = $target_dir . $filename;
        if (move_uploaded_file($_FILES['stop_image']['tmp_name'], $target)) {
            $uploaded_image = $filename;
        }
    }

    if ($stop_type === 'existing' && empty($attraction_id)) {
        $error = 'Выберите достопримечательность из списка';
    } elseif ($stop_type === 'custom' && empty($custom_title)) {
        $error = 'Укажите название своей точки';
    } else {
        $save_attraction_id = ($stop_type === 'existing') ? $attraction_id : null;
        $save_custom_title = ($stop_type === 'custom') ? $custom_title : null;
        $save_custom_description = ($stop_type === 'custom') ? $custom_description : null;

        $stmt = $pdo->prepare("SELECT MAX(stop_order) FROM route_stops WHERE route_id = ?");
        $stmt->execute([$route_id]);
        $nextOrder = $stmt->fetchColumn() + 1;

        $stmt = $pdo->prepare("INSERT INTO route_stops
            (route_id, stop_order, attraction_id, custom_title, custom_description, walk_time_to_next, image)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$route_id, $nextOrder, $save_attraction_id, $save_custom_title, $save_custom_description, $walk_time, $uploaded_image]);
        header("Location: route_stops.php?route_id=$route_id");
        exit;
    }
}

// Получение остановок
$stops = $pdo->prepare("
    SELECT rs.*,
           COALESCE(t.title, rs.custom_title) AS display_title,
           a.slug AS attraction_slug
    FROM route_stops rs
    LEFT JOIN attractions a ON rs.attraction_id = a.id
    LEFT JOIN attraction_translations t ON a.id = t.attraction_id AND t.language_code = 'ru'
    WHERE rs.route_id = ?
    ORDER BY rs.stop_order
");
$stops->execute([$route_id]);
$stops = $stops->fetchAll();

$attractions = $pdo->query("
    SELECT a.id, t.title
    FROM attractions a
    JOIN attraction_translations t ON a.id = t.attraction_id AND t.language_code = 'ru'
    ORDER BY t.title
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Остановки маршрута: <?= htmlspecialchars($route['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #custom-fields { display: none; }
        #existing-fields { display: block; }
        .image-thumb { max-width: 80px; max-height: 60px; border-radius: 6px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand">Остановки: <?= htmlspecialchars($route['title']) ?></span>
            <div>
                <a href="routes.php" class="btn btn-outline-light btn-sm">К списку маршрутов</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Выход</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">Добавить остановку</div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Тип точки</label>
                        <select name="stop_type" id="stopType" class="form-select">
                            <option value="existing" selected>Существующая достопримечательность</option>
                            <option value="custom">Своя точка</option>
                        </select>
                    </div>
                    <div id="existing-fields">
                        <div class="mb-3">
                            <label class="form-label">Выберите достопримечательность</label>
                            <select name="attraction_id" class="form-select">
                                <option value="">-- Выберите --</option>
                                <?php foreach ($attractions as $attr): ?>
                                    <option value="<?= $attr['id'] ?>"><?= htmlspecialchars($attr['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div id="custom-fields">
                        <div class="mb-3">
                            <label class="form-label">Название точки</label>
                            <input type="text" name="custom_title" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Описание</label>
                            <textarea name="custom_description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Изображение (для своей точки)</label>
                        <input type="file" name="stop_image" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Время до следующей точки</label>
                        <input type="text" name="walk_time" class="form-control" placeholder="например: 10 мин">
                    </div>
                    <button type="submit" name="add_stop" class="btn btn-primary">Добавить</button>
                </form>
            </div>
        </div>

        <h3>Список остановок</h3>
        <?php if (empty($stops)): ?>
            <p>Нет остановок. Добавьте первую.</p>
        <?php else: ?>
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>№</th>
                        <th>Изображение</th>
                        <th>Название</th>
                        <th>Время до след.</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stops as $stop): ?>
                        <tr>
                            <td><?= $stop['stop_order'] ?></td>
                            <td>
                                <?php if ($stop['image']): ?>
                                    <img src="<?= BASE_URL ?>uploads/routes/<?= htmlspecialchars($stop['image']) ?>" class="image-thumb" alt="">
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($stop['display_title']) ?>
                                <?php if ($stop['attraction_id']): ?>
                                    <span class="badge bg-secondary">из базы</span>
                                <?php else: ?>
                                    <span class="badge bg-info">своё</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($stop['walk_time_to_next'] ?? '—') ?></td>
                            <td>
                                <a href="?route_id=<?= $route_id ?>&up=<?= $stop['id'] ?>" class="btn btn-sm btn-outline-secondary">↑</a>
                                <a href="?route_id=<?= $route_id ?>&down=<?= $stop['id'] ?>" class="btn btn-sm btn-outline-secondary">↓</a>
                                <a href="?route_id=<?= $route_id ?>&delete=<?= $stop['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить остановку?')">×</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        const stopType = document.getElementById('stopType');
        const existingFields = document.getElementById('existing-fields');
        const customFields = document.getElementById('custom-fields');
        function toggleFields() {
            if (stopType.value === 'existing') {
                existingFields.style.display = 'block';
                customFields.style.display = 'none';
            } else {
                existingFields.style.display = 'none';
                customFields.style.display = 'block';
            }
        }
        stopType.addEventListener('change', toggleFields);
        toggleFields();
    </script>
</body>
</html>
