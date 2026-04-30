<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$category_id = $_GET['category'] ?? null;
$lang = $_SESSION['lang'] ?? 'ru';
$limit = 6;
$offset = 0;

$attractions = getFilteredAttractionsPaginated($category_id, '', $limit, $offset, $lang);

ob_start();
foreach ($attractions as $item):
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
echo json_encode(['html' => $html]);
