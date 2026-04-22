<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
// route-detail.php
if (!isset($route)) {
    header('Location: ' . BASE_URL . 'routes');
    exit;
}
$stops = getRouteStops($route['id']);
$coffeeSpots = getRouteRecommendations($route['id'], 'coffee');
$photoSpots = getRouteRecommendations($route['id'], 'photo');
$foodSpots = getRouteRecommendations($route['id'], 'food');

$pageTitle = $route['title'];
$pageDescription = $route['short_description'] ?? '';
$ogImage = BASE_URL . 'uploads/hero-bg.jpg';
$canonicalUrl = BASE_URL . 'routes/' . urlencode($route['slug']);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | <?= __('site_title') ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta property="og:image" content="<?= $ogImage ?>">
    <meta property="og:url" content="<?= $canonicalUrl ?>">
    <link rel="canonical" href="<?= $canonicalUrl ?>">
    <link rel="alternate" hreflang="ru" href="<?= $canonicalUrl ?>?lang=ru">
    <link rel="alternate" hreflang="en" href="<?= $canonicalUrl ?>?lang=en">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/routes.css">
    <?php include 'includes/metrica.php'; ?>
    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/hlebnikrosh.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="<?= BASE_URL ?>" class="site-title"><span>Омскъ</span> Исторический</a>
            <div class="nav-links">
                <a href="<?= BASE_URL ?>" class="nav-link"><?= __('home') ?></a>
                <a href="<?= BASE_URL ?>routes" class="nav-link"><?= $lang == 'ru' ? 'Маршруты' : 'Routes' ?></a>
                <a href="<?= BASE_URL ?>about" class="nav-link"><?= $lang == 'ru' ? 'О проекте' : 'About' ?></a>
                <?php if (isset($_SESSION['admin_logged_in'])): ?>
                    <a href="<?= BASE_URL ?>admin/" class="nav-link">Админка</a>
                    <a href="<?= BASE_URL ?>admin/logout.php" class="nav-link">Выход</a>
                <?php endif; ?>
                <div class="lang-switch">
                    <a href="?lang=ru" class="lang-btn <?= $lang=='ru'?'active':'' ?>">RU</a>
                    <a href="?lang=en" class="lang-btn <?= $lang=='en'?'active':'' ?>">EN</a>
                </div>
                <div class="accessibility-controls">
                    <button class="theme-toggle" data-theme="light" title="Светлая тема">☀️</button>
                    <button class="theme-toggle" data-theme="dark" title="Тёмная тема">🌙</button>
                    <button class="font-size-btn" data-size="increase" title="Увеличить шрифт">A+</button>
                    <button class="font-size-btn" data-size="reset" title="Сбросить">A</button>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="breadcrumbs">
            <a href="<?= BASE_URL ?>"><?= __('home') ?></a> /
            <a href="<?= BASE_URL ?>routes"><?= $lang == 'ru' ? 'Маршруты' : 'Routes' ?></a> /
            <span><?= htmlspecialchars($route['title']) ?></span>
        </div>

        <!-- Hero маршрута -->
        <div class="route-hero">
            <h1><?= htmlspecialchars($route['title']) ?></h1>
            <p><?= htmlspecialchars($route['short_description']) ?></p>
            <div class="route-stats">
                <?php if ($route['distance']): ?><span>🚶 <?= $route['distance'] ?></span><?php endif; ?>
                <?php if ($route['duration']): ?><span>⏱ <?= $route['duration'] ?></span><?php endif; ?>
                <?php if ($route['stops_count']): ?><span>📍 <?= $route['stops_count'] ?> остановок</span><?php endif; ?>
            </div>
            <div id="routeMap" style="height: 300px; border-radius: 16px;"></div>
        </div>

        <!-- Пошаговый маршрут -->
        <h2><?= $lang == 'ru' ? 'Пошаговый маршрут' : 'Step-by-step route' ?></h2>
        <div class="stops-list">
            <?php foreach ($stops as $index => $stop): ?>
                <div class="stop-item">
                    <div class="stop-number"><?= $index + 1 ?></div>
                    <div class="stop-content">
                        <h3><?= htmlspecialchars($stop['attraction_title'] ?? $stop['custom_title']) ?></h3>
                        <p><?= htmlspecialchars($stop['custom_description'] ?? '') ?></p>
                        <?php if ($stop['image']): ?>
                            <img src="<?= UPLOAD_URL . $stop['image'] ?>" style="max-width:200px; border-radius:12px;" alt="">
                        <?php endif; ?>
                        <?php if ($stop['walk_time_to_next']): ?>
                            <div class="walk-time">↓ <?= $stop['walk_time_to_next'] ?> до следующей точки</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Рекомендации: кофе -->
        <?php if (!empty($coffeeSpots)): ?>
            <div class="recommendations-block">
                <h3>☕ <?= $lang == 'ru' ? 'Где выпить кофе по пути' : 'Where to have coffee along the way' ?></h3>
                <ul>
                    <?php foreach ($coffeeSpots as $spot): ?>
                        <li><strong><?= htmlspecialchars($spot['title']) ?></strong> – <?= htmlspecialchars($spot['description']) ?> (<?= htmlspecialchars($spot['address']) ?>)</li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Фото и еда аналогично (можно добавить) -->

        <a href="<?= BASE_URL ?>routes" class="btn">← <?= $lang == 'ru' ? 'Все маршруты' : 'All routes' ?></a>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>© <?= date('Y') ?> Омск. Историческое наследие.</p>
            <p>
                <a href="<?= BASE_URL ?>privacy">Политика конфиденциальности</a> |
                <a href="<?= BASE_URL ?>terms">Пользовательское соглашение</a>
            </p>
        </div>
    </footer>

    <script src="<?= BASE_URL ?>js/theme.js"></script>
    <script>
        // Карта с маркерами всех точек маршрута
        <?php
        $markers = [];
        foreach ($stops as $stop) {
            // Здесь нужно получать координаты из attraction или из custom_lat/lng (можно доработать)
            // Пока заглушка
        }
        ?>
        const map = L.map('routeMap').setView([54.9833, 73.3667], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        // Добавление маркеров из $markers (реализуется при наличии координат)
    </script>
    <script src="<?= BASE_URL ?>js/theme.js"></script>
</body>
</html>
