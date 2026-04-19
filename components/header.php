<?php
// Ожидаем, что переменные $pageTitle, $pageDescription, $ogImage, $canonicalUrl уже определены в вызывающем файле
// Если не определены — задаём значения по умолчанию
if (!isset($pageTitle)) {
    $pageTitle = __('site_title');
}
if (!isset($pageDescription)) {
    $pageDescription = $lang == 'ru'
        ? 'Достопримечательности Омска: исторические места, памятники, храмы и улицы. Путеводитель по Омску с фото и описаниями.'
        : 'Omsk landmarks: historical places, monuments, churches and streets. Omsk travel guide with photos and descriptions.';
}
if (!isset($ogImage)) {
    $ogImage = BASE_URL . 'uploads/hero-bg.jpg'; // или default-og.jpg
}
if (!isset($canonicalUrl)) {
    $canonicalUrl = BASE_URL;
}
// slug может быть передан для ссылок смены языка
$slugForLang = $slug ?? '';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> – <?= __('site_title') ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta name="keywords" content="достопримечательности Омска, Омская крепость, Успенский собор, Любинский проспект, памятник Степанычу, туризм в Омске, Omsk landmarks, Omsk fortress, Dormition Cathedral">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta property="og:image" content="<?= $ogImage ?>">
    <meta property="og:url" content="<?= $canonicalUrl ?>">
    <meta property="og:site_name" content="<?= __('site_title') ?>">

    <!-- Canonical -->
    <link rel="canonical" href="<?= $canonicalUrl ?>">

    <!-- hreflang -->
    <link rel="alternate" hreflang="ru" href="<?= BASE_URL ?>?lang=ru<?= $slugForLang ? '&slug='.urlencode($slugForLang) : '' ?>">
    <link rel="alternate" hreflang="en" href="<?= BASE_URL ?>?lang=en<?= $slugForLang ? '&slug='.urlencode($slugForLang) : '' ?>">
    <link rel="alternate" hreflang="x-default" href="<?= BASE_URL ?>">

    <!-- Шрифты -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Стили -->
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= BASE_URL ?>image/favicon/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>image/favicon/favicon-32x32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>image/favicon/apple-touch-icon.png">
    <meta name="theme-color" content="#ffffff">

    <?php include 'includes/metrica.php'; ?>
    <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="<?= BASE_URL ?>" class="site-title">
                <span>Омскъ</span> Исторический
            </a>
            <div class="nav-links">
                <a href="<?= BASE_URL ?>" class="nav-link"><?= __('home') ?></a>
                <a href="<?= BASE_URL ?>about" class="nav-link"><?= $lang == 'ru' ? 'О проекте' : 'About' ?></a>
                <?php if (isset($_SESSION['admin_logged_in'])): ?>
                    <a href="<?= BASE_URL ?>admin/" class="nav-link">Админка</a>
                    <a href="<?= BASE_URL ?>admin/logout.php" class="nav-link">Выход</a>
                <?php endif; ?>
                <div class="lang-switch">
                    <a href="?lang=ru<?= $slugForLang ? '&slug='.urlencode($slugForLang) : '' ?>" class="lang-btn <?= $lang=='ru'?'active':'' ?>">RU</a>
                    <a href="?lang=en<?= $slugForLang ? '&slug='.urlencode($slugForLang) : '' ?>" class="lang-btn <?= $lang=='en'?'active':'' ?>">EN</a>
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
    <main>
