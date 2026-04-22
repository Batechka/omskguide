<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$category = $_GET['category'] ?? null;
$search = trim($_GET['search'] ?? '');
$lang = $_SESSION['lang'] ?? 'ru';
$limit = 6;
$offset = ($page - 1) * $limit;

// Функция должна быть определена в functions.php
$attractions = getFilteredAttractionsPaginated($category, $search, $limit, $offset, $lang);

// Общее количество (можно взять из той же функции или отдельно)
$total = $pdo->query("SELECT COUNT(*) FROM attractions")->fetchColumn();
$hasMore = ($offset + $limit) < $total;

ob_start();
foreach ($attractions as $item):
    // Здесь HTML-шаблон карточки точно такой же, как в index.php
?>
<article class="attraction-card animate-on-scroll">
    <?php if (!empty($item['primary_image'])): ?>
        <img src="<?= UPLOAD_URL . htmlspecialchars($item['primary_image']) ?>" class="card-img" alt="<?= htmlspecialchars($item['title']) ?>" loading="lazy">
    <?php else: ?>
        <div class="card-img placeholder-img"></div>
    <?php endif; ?>
    <div class="card-content">
        <h2 class="card-title"><?= htmlspecialchars($item['title']) ?></h2>
        <p class="card-description"><?= htmlspecialchars($item['short_description']) ?></p>
        <a href="<?= BASE_URL . urlencode($item['slug']) ?>" class="btn"><?= __('read_more') ?></a>
    </div>
</article>
<?php
endforeach;
$html = ob_get_clean();

header('Content-Type: application/json');
echo json_encode([
    'html' => $html,
    'hasMore' => $hasMore
]);
