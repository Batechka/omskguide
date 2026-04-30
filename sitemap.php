<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Устанавливаем заголовок ответа
header('Content-Type: application/xml; charset=utf-8');

// Получаем все достопримечательности (только slug)
$attractions = $pdo->query("SELECT slug FROM attractions ORDER BY id")->fetchAll();

// Получаем все маршруты
$routes = $pdo->query("SELECT slug FROM routes ORDER BY id")->fetchAll();

// Формируем XML
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

    <!-- Маршруты (каталог) -->
    <url>
        <loc><?= BASE_URL ?>routes</loc>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>

    <!-- О проекте -->
    <url>
        <loc><?= BASE_URL ?>about</loc>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Достопримечательности -->
    <?php foreach ($attractions as $item): ?>
    <url>
        <loc><?= BASE_URL . urlencode($item['slug']) ?></loc>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>

    <!-- Маршруты (детальные страницы) -->
    <?php foreach ($routes as $route): ?>
    <url>
        <loc><?= BASE_URL ?>routes/<?= urlencode($route['slug']) ?></loc>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>

</urlset>
