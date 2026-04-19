<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/xml; charset=utf-8');

// Получаем все достопримечательности (на русском языке, но slug одинаков для всех)
$attractions = $pdo->query("
    SELECT a.slug,
           (SELECT filename FROM images WHERE attraction_id = a.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM attractions a
    ORDER BY a.id
")->fetchAll();

// Статические страницы
$staticPages = ['about', 'privacy', 'terms'];

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">

    <!-- Главная страница -->
    <url>
        <loc><?= BASE_URL ?></loc>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Достопримечательности -->
    <?php foreach ($attractions as $item): ?>
    <url>
        <loc><?= BASE_URL . urlencode($item['slug']) ?></loc>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
        <?php if (!empty($item['primary_image'])): ?>
        <image:image>
            <image:loc><?= UPLOAD_URL . htmlspecialchars($item['primary_image']) ?></image:loc>
        </image:image>
        <?php endif; ?>
    </url>
    <?php endforeach; ?>

    <!-- Статические страницы -->
    <?php foreach ($staticPages as $page): ?>
    <url>
        <loc><?= BASE_URL . $page ?></loc>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    <?php endforeach; ?>

</urlset>
