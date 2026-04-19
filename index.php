<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
$request = trim($_SERVER['REQUEST_URI'], '/');
$request = strtok($request, '?');

if ($request === '' || $request === 'index.php') {
    echo "Главная страница (ЧПУ работает)";
    exit;
} else {
    $attraction = getAttractionBySlug($request);
    if ($attraction) {
        $slug = $request;
        require_once 'attraction.php';
        exit;
    } else {
        http_response_code(404);
        echo "Страница не найдена";
        exit;
    }
}

ini_set('display_errors', 1);
error_reporting(E_ALL);
// настройка header
// Определяем переменные для header
$pageTitle = __('site_title');
$pageDescription = $lang == 'ru'
    ? 'Достопримечательности Омска: исторические места, памятники, храмы и улицы. Путеводитель по Омску с фото и описаниями.'
    : 'Omsk landmarks: historical places, monuments, churches and streets. Omsk travel guide with photos and descriptions.';
$ogImage = BASE_URL . 'uploads/hero-bg.jpg';
$canonicalUrl = BASE_URL;

// -----------------------

$categories = getCategories();
$selected_category = isset($_GET['category']) ? $_GET['category'] : null;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$attractions = getFilteredAttractions($selected_category, $search_query);


require_once 'components/header.php';
?>



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

    <?php $extraScripts = '<script src="' . BASE_URL . 'js/search.js"></script>'; ?>
    <?php require_once 'components/footer.php'; ?>

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
