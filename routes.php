<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($routes)) {
    header('Location: ' . BASE_URL);
    exit;
}
// ... остальной код
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | <?= __('site_title') ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= $pageTitle ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta property="og:image" content="<?= $ogImage ?>">
    <meta property="og:url" content="<?= $canonicalUrl ?>">
    <link rel="canonical" href="<?= $canonicalUrl ?>">
    <link rel="alternate" hreflang="ru" href="<?= $canonicalUrl ?>?lang=ru">
    <link rel="alternate" hreflang="en" href="<?= $canonicalUrl ?>?lang=en">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/hlebnikrosh.css">


    <?php include 'includes/metrica.php'; ?>
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
            <span><?= $lang == 'ru' ? 'Маршруты' : 'Routes' ?></span>
        </div>

        <h1><?= $lang == 'ru' ? 'Маршруты по Омску' : 'Omsk Routes' ?></h1>

        <div class="routes-grid">
            <?php foreach ($routes as $route): ?>
                <div class="route-card">
                    <div class="route-card-content">
                        <h3><?= htmlspecialchars($route['title']) ?></h3>
                        <div class="route-meta">
                            <?php if ($route['distance']): ?><span>🚶 <?= $route['distance'] ?></span><?php endif; ?>
                            <?php if ($route['duration']): ?><span>⏱ <?= $route['duration'] ?></span><?php endif; ?>
                            <?php if ($route['stops_count']): ?><span>📍 <?= $route['stops_count'] ?> остановок</span><?php endif; ?>
                        </div>
                        <p><?= htmlspecialchars($route['short_description']) ?></p>
                        <a href="<?= BASE_URL ?>routes/<?= urlencode($route['slug']) ?>" class="btn">
                            <?= $lang == 'ru' ? 'Смотреть маршрут' : 'View route' ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
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

</body>
</html>
