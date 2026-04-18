<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

$attractions = getAllAttractionsAdmin();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= __('admin_panel') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand"><?= __('admin_panel') ?></span>
            <div>
                <a href="../index.php" class="btn btn-outline-light btn-sm"><?= __('home') ?></a>
                <a href="logout.php" class="btn btn-outline-light btn-sm"><?= __('logout') ?></a>
                <a href="categories.php" class="btn btn-outline-light btn-sm"><?=__('categories') ?></a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <div class="d-flex justify-content-between mb-3">
            <h2><?= __('attractions') ?></h2>
            <a href="attraction_edit.php" class="btn btn-success">+ <?= __('add_attraction') ?></a>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Slug</th>
                    <th>RU Title</th>
                    <th>EN Title</th>
                    <th><?= __('actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attractions as $a): ?>
                <tr>
                    <td><?= $a['id'] ?></td>
                    <td><?= htmlspecialchars($a['slug']) ?></td>
                    <td><?= htmlspecialchars($a['title_ru'] ?? '') ?></td>
                    <td><?= htmlspecialchars($a['title_en'] ?? '') ?></td>
                    <td>
                        <a href="attraction_edit.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-primary"><?= __('edit') ?></a>
                        <a href="?delete=<?= $a['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('<?= __('confirm_delete') ?>')"><?= __('delete') ?></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php
// Обработка удаления
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Получить все изображения для удаления файлов
    $stmt = $pdo->prepare("SELECT filename FROM images WHERE attraction_id = ?");
    $stmt->execute([$id]);
    $images = $stmt->fetchAll();
    foreach ($images as $img) {
        $filepath = UPLOAD_DIR . $img['filename'];
        if (file_exists($filepath)) unlink($filepath);
    }
    $pdo->prepare("DELETE FROM attractions WHERE id = ?")->execute([$id]);
    header('Location: index.php');
    exit;
}
?>
