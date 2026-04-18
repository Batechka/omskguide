<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$categories = getCategories();
$selected_category = isset($_GET['category']) ? $_GET['category'] : null;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$attractions = getFilteredAttractions($selected_category, $search_query);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="достопримечательности Омска, Омская крепость, Успенский собор, Любинский проспект, памятник Степанычу, туризм в Омске, Omsk landmarks, Omsk fortress, Dormition Cathedral">
    <title><?= __('site_title') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/hlebnikrosh.css">
    <?php include 'includes/metrica.php'; ?>

    <!-- Основная версия страницы -->
     <link rel="canonical" href="<?= BASE_URL ?>">
    <!-- фавикон -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="image/favicon/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="image/favicon-32x32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="image/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="image/favicon/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="image/favicon/android-chrome-512x512.png">
    <meta name="theme-color" content="#ffffff">

    <!-- Чтобы Яндекс и Google понимали, что русская и английская версии – это одна страница на разных языках. -->
     <link rel="alternate" hreflang="ru" href="<?= BASE_URL ?>?lang=ru<?= isset($slug) ? '&slug='.urlencode($slug) : '' ?>">
    <link rel="alternate" hreflang="en" href="<?= BASE_URL ?>?lang=en<?= isset($slug) ? '&slug='.urlencode($slug) : '' ?>">
    <link rel="alternate" hreflang="x-default" href="<?= BASE_URL ?>">
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="<?= BASE_URL ?>" class="site-title">
                <span>Омскъ</span> Исторический
            </a>
            <div class="nav-links">
                <a href="<?= BASE_URL ?>" class="nav-link"><?= __('home') ?></a>
                <a href="about.php" class="nav-link"><?= $lang == 'ru' ? 'О проекте' : 'About' ?></a>
                <?php if (isset($_SESSION['admin_logged_in'])): ?>
                    <a href="admin/" class="nav-link">Админка</a>
                    <a href="admin/logout.php" class="nav-link">Выход</a>
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

    <!-- HERO БАННЕР (знакомство с сайтом) -->
    <section class="hero">
        <div class="hero-background">
            <!-- Замените hero-bg.jpg на своё изображение или оставьте как есть -->
            <img src="<?= UPLOAD_URL ?>e83ce0b649358602599a9b4f6c72a4e5.jpg" alt="Панорама Омска" loading="eager">
            <div class="hero-overlay"></div>
        </div>
        <div class="hero-content container">
            <h1 class="hero-title"><?= __('hero_title') ?></h1>
            <p class="hero-subtitle"><?= __('hero_subtitle') ?></p>
            <a href="#explore" class="hero-btn"><?= __('hero_button') ?></a>
        </div>
    </section>

    <main class="container" id="explore">
        <h1><?= __('attractions') ?> Омска</h1>

        <div class="search-wrapper">
            <input type="text" id="searchInput" class="search-input" placeholder="<?= $lang=='ru'?'Поиск...':'Search...' ?>" value="<?= htmlspecialchars($search_query) ?>">
            <div id="searchSuggestions" class="search-suggestions"></div>
        </div>

        <div class="category-filter">
            <a href="index.php" class="category-link <?= !$selected_category ? 'active' : '' ?>"><?= $lang=='ru'?'Все':'All' ?></a>
            <?php foreach($categories as $cat): ?>
                <a href="?category=<?= $cat['id'] ?>" class="category-link <?= $selected_category==$cat['id'] ? 'active' : '' ?>">
                    <?= htmlspecialchars($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="attractions-grid">
            <?php foreach ($attractions as $item): ?>
                <article class="attraction-card animate-on-scroll">
                    <?php if (!empty($item['primary_image'])): ?>
                        <img src="<?= UPLOAD_URL . htmlspecialchars($item['primary_image']) ?>" class="card-img" alt="<?= htmlspecialchars($item['title']) ?>" loading="lazy">
                    <?php else: ?>
                        <div class="card-img placeholder-img"></div>
                    <?php endif; ?>
                    <div class="card-content">
                        <h2 class="card-title"><?= htmlspecialchars($item['title']) ?></h2>
                        <p class="card-description"><?= htmlspecialchars($item['short_description']) ?></p>
                        <a href="attraction.php?slug=<?= urlencode($item['slug']) ?>" class="btn"><?= __('read_more') ?></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>© <?= date('Y') ?> Омск. Историческое наследие.</p>
        </div>
    </footer>

    <script>
        const searchInput = document.getElementById('searchInput');
        const suggestionsBox = document.getElementById('searchSuggestions');
        let debounceTimer;

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();
            if (query.length < 2) {
                suggestionsBox.innerHTML = '';
                suggestionsBox.style.display = 'none';
                return;
            }
            debounceTimer = setTimeout(() => {
                fetch('<?= BASE_URL ?>ajax_fuzzy_search.php?q=' + encodeURIComponent(query) + '&lang=<?= $lang ?>')
                    .then(r => r.json())
                    .then(data => {
                        if (data.length) {
                            suggestionsBox.innerHTML = data.map(item =>
                                `<a href="attraction.php?slug=${item.slug}" class="suggestion-item">${item.title}</a>`
                            ).join('');
                        } else {
                            suggestionsBox.innerHTML = '<div class="no-suggestions">Ничего не найдено</div>';
                        }
                        suggestionsBox.style.display = 'block';
                    })
                    .catch(err => {
                        console.error('Ошибка поиска:', err);
                        suggestionsBox.innerHTML = '<div class="no-suggestions">Ошибка</div>';
                        suggestionsBox.style.display = 'block';
                    });
            }, 300);
        });

        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target)) suggestionsBox.style.display = 'none';
        });

        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                window.location.href = `index.php?search=${encodeURIComponent(searchInput.value)}`;
            }
        });

        (function() {
            const body = document.body;
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') body.classList.add('dark-theme');

            document.querySelectorAll('.theme-toggle').forEach(btn => {
                btn.addEventListener('click', () => {
                    const theme = btn.dataset.theme;
                    body.classList.toggle('dark-theme', theme === 'dark');
                    localStorage.setItem('theme', theme === 'dark' ? 'dark' : 'light');
                });
            });

            // Управление размером шрифта
            (function() {
                const html = document.documentElement;
                let fontSizeLevel = parseInt(localStorage.getItem('fontSizeLevel')) || 0;

                function applyFontSize() {
                    html.classList.remove('font-size-large', 'font-size-extra-large');
                    if (fontSizeLevel === 1) html.classList.add('font-size-large');
                    if (fontSizeLevel === 2) html.classList.add('font-size-extra-large');
                }
                applyFontSize();

                document.querySelectorAll('.font-size-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        if (btn.dataset.size === 'increase') {
                            fontSizeLevel = Math.min(fontSizeLevel + 1, 2);
                        } else if (btn.dataset.size === 'reset') {
                            fontSizeLevel = 0;
                        }
                        applyFontSize();
                        localStorage.setItem('fontSizeLevel', fontSizeLevel);
                    });
                });
            })();

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
            }, { threshold: 0.1 });
            document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));
        })();
    </script>
</body>
</html>
