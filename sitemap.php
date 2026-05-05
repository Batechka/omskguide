<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/xml; charset=utf-8');

// Получаем все достопримечательности (только slug)
$attractions = $pdo->query("SELECT slug FROM attractions ORDER BY id")->fetchAll();
// Все маршруты
$routes = $pdo->query("SELECT slug FROM routes ORDER BY id")->fetchAll();
// Все статьи
$articles = $pdo->query("SELECT slug FROM articles WHERE is_published = 1 ORDER BY id")->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    <!-- Главная -->
    <url>
        <loc><?= BASE_URL ?>ru/</loc>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?= BASE_URL ?>en/</loc>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Каталог маршрутов -->
    <url>
        <loc><?= BASE_URL ?>ru/kuda-shodit-v-omske</loc>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc><?= BASE_URL ?>en/kuda-shodit-v-omske</loc>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>

    <!-- Каталог статей -->
    <url>
        <loc><?= BASE_URL ?>ru/articles</loc>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc><?= BASE_URL ?>en/articles</loc>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>

    <!-- О проекте -->
    <url>
        <loc><?= BASE_URL ?>ru/about</loc>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    <url>
        <loc><?= BASE_URL ?>en/about</loc>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

    <!-- Достопримечательности -->
    <?php foreach ($attractions as $item): ?>
        <url>
            <loc><?= BASE_URL ?>ru/<?= urlencode($item['slug']) ?></loc>
            <changefreq>monthly</changefreq>
            <priority>0.8</priority>
        </url>
        <url>
            <loc><?= BASE_URL ?>en/<?= urlencode($item['slug']) ?></loc>
            <changefreq>monthly</changefreq>
            <priority>0.8</priority>
        </url>
    <?php endforeach; ?>

    <!-- Маршруты (детальные) -->
    <?php foreach ($routes as $route): ?>
        <url>
            <loc><?= BASE_URL ?>ru/kuda-shodit-v-omske/<?= urlencode($route['slug']) ?></loc>
            <changefreq>monthly</changefreq>
            <priority>0.8</priority>
        </url>
        <url>
            <loc><?= BASE_URL ?>en/kuda-shodit-v-omske/<?= urlencode($route['slug']) ?></loc>
            <changefreq>monthly</changefreq>
            <priority>0.8</priority>
        </url>
    <?php endforeach; ?>

    <!-- Статьи -->
    <?php foreach ($articles as $article): ?>
        <url>
            <loc><?= BASE_URL ?>ru/article/<?= urlencode($article['slug']) ?></loc>
            <changefreq>monthly</changefreq>
            <priority>0.8</priority>
        </url>
        <url>
            <loc><?= BASE_URL ?>en/article/<?= urlencode($article['slug']) ?></loc>
            <changefreq>monthly</changefreq>
            <priority>0.8</priority>
        </url>
    <?php endforeach; ?>

</urlset>
