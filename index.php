<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';

/*
|--------------------------------------------------------------------------
| Язык сайта
|--------------------------------------------------------------------------
*/
if (isset($_GET['lang']) && in_array($_GET['lang'], ['ru', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
    $params = $_GET;
    unset($params['lang']);
    $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?');
    if (!empty($params)) {
        $redirectUrl .= '?' . http_build_query($params);
    }
    header("Location: $redirectUrl");
    exit;
}
$lang = $_SESSION['lang'] ?? 'ru';

/*
|--------------------------------------------------------------------------
| Определяем текущий запрос
|--------------------------------------------------------------------------
*/
$request = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Убираем подпапку, если сайт в ней (например, /omsk/)
$basePath = trim(parse_url(BASE_URL, PHP_URL_PATH), '/');
if ($basePath && strpos($request, $basePath) === 0) {
    $request = substr($request, strlen($basePath));
    $request = trim($request, '/');
}

// Если запрос пустой или index.php — считаем главной
if ($request === 'index.php') {
    $request = '';
}

/*
|--------------------------------------------------------------------------
| Проверка маршрутов
|--------------------------------------------------------------------------
// Проверка маршрутов: /routes и /routes/любой-slug
*/
// Проверка маршрутов: /routes и /routes/слаг
if ($request === 'routes' || strpos($request, 'routes/') === 0) {
    if ($request === 'routes') {
        // Каталог маршрутов
        $routes = getAllRoutes();
        $pageTitle = $lang == 'ru' ? 'Маршруты по Омску' : 'Omsk Routes';
        $pageDescription = $lang == 'ru'
            ? 'Готовые пешие маршруты по Омску: от исторического центра до кофейных прогулок.'
            : 'Ready-made walking routes around Omsk: from historic center to coffee walks.';
        $ogImage = BASE_URL . 'uploads/hero-bg.jpg';
        $canonicalUrl = BASE_URL . 'routes';
        require_once 'routes.php';
        exit;
    } else {
        // Детальная страница маршрута
        $routeSlug = substr($request, 7); // убираем 'routes/'
        $route = getRouteBySlug($routeSlug);
        if ($route) {
            $slug = $routeSlug;
            require_once 'route-detail.php';
            exit;
        }
    }
}

/*
|--------------------------------------------------------------------------
| Главная страница
|--------------------------------------------------------------------------
*/
if ($request === '' || $request === 'index.php') {
    $categories = getCategories();
    $selected_category = $_GET['category'] ?? null;
    $search_query = trim($_GET['search'] ?? '');

    // --- ПАГИНАЦИЯ ---
    $limit = 6;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    $totalAttractions = $pdo->query("SELECT COUNT(*) FROM attractions")->fetchColumn();
    $attractions = getFilteredAttractionsPaginated($selected_category, $search_query, $limit, $offset);
    // --- КОНЕЦ ПАГИНАЦИИ ---

    $pageTitle = __('site_title');
    $pageDescription = $lang === 'ru'
        ? 'Достопримечательности Омска: исторические места, памятники, храмы и улицы.'
        : 'Omsk landmarks: historical places, monuments, churches and streets.';
    $ogImage = BASE_URL . 'uploads/hero-bg.jpg';
    $canonicalUrl = strtok(BASE_URL . $_SERVER['REQUEST_URI'], '?');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title><?= htmlspecialchars($pageTitle) ?></title>

        <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">

        <meta property="og:type" content="website">
        <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
        <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
        <meta property="og:image" content="<?= $ogImage ?>">
        <meta property="og:url" content="<?= $canonicalUrl ?>">

        <link rel="canonical" href="<?= $canonicalUrl ?>">

        <link rel="alternate" hreflang="ru" href="<?= BASE_URL ?>?lang=ru">
        <link rel="alternate" hreflang="en" href="<?= BASE_URL ?>?lang=en">
        <link rel="alternate" hreflang="x-default" href="<?= BASE_URL ?>">

        <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
        <link rel="stylesheet" href="<?= BASE_URL ?>css/faq.css">
        <link rel="stylesheet" href="css/card.css">

        <?php include 'includes/metrica.php'; ?>

    </head>


    <body>
        <header class="site-header">
            <div class="container header-inner">
                <a href="<?= BASE_URL ?>" class="site-title">
                    <span>Омскъ</span> Исторический
                </a>
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

        <!-- HERO БАННЕР -->
        <section class="hero">
            <div class="hero-background">
                <img src="<?= UPLOAD_URL ?>e83ce0b649358602599a9b4f6c72a4e5.jpg" alt="Панорама Омска" loading="eager">
                <div class="hero-overlay"></div>
            </div>
            <div class="hero-content container">
                <h1 class="hero-title"><?= __('hero_title') ?></h1>
                <p class="hero-subtitle"><?= __('hero_subtitle') ?></p>
                <a href="#explore" class="hero-btn"><?= __('hero_button') ?></a>
            </div>
        </section>


        <?php
            $popularRoutes = $pdo->query("
                SELECT r.slug, rt.title, r.distance, r.duration, r.stops_count
                FROM routes r
                JOIN route_translations rt ON r.id = rt.route_id AND rt.language_code = '{$lang}'
                WHERE r.is_popular = 1
                LIMIT 3
            ")->fetchAll();
            if (!empty($popularRoutes)):
            ?>
            <section class="popular-routes container">
                <h2><?= $lang == 'ru' ? 'Популярные маршруты' : 'Popular Routes' ?></h2>
                <div class="routes-grid">
                    <?php foreach ($popularRoutes as $pr): ?>
                        <div class="route-card">
                            <h3>📍 <?= htmlspecialchars($pr['title']) ?></h3>
                            <div class="route-meta">
                                <?php if ($pr['distance']): ?><span>🚶 <?= $pr['distance'] ?></span><?php endif; ?>
                                <?php if ($pr['duration']): ?><span>⏱ <?= $pr['duration'] ?></span><?php endif; ?>
                                <?php if ($pr['stops_count']): ?><span>📍 <?= $pr['stops_count'] ?> остановок</span><?php endif; ?>
                            </div>
                            <a href="<?= BASE_URL ?>routes/<?= urlencode($pr['slug']) ?>" class="btn">
                                <?= $lang == 'ru' ? 'Смотреть маршрут' : 'View route' ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a href="<?= BASE_URL ?>routes" class="btn btn-outline"><?= $lang == 'ru' ? 'Все маршруты' : 'All routes' ?></a>
            </section>
            <?php endif; ?>

        <main class="container" id="explore">
            <h1><?= __('attractions') ?> Омска</h1>

            <!-- Поиск -->
            <div class="search-wrapper">
                <input type="text" id="searchInput" class="search-input"
                       placeholder="<?= $lang=='ru' ? 'Поиск...' : 'Search...' ?>"
                       value="<?= htmlspecialchars($search_query) ?>">
                <div id="searchSuggestions" class="search-suggestions"></div>
            </div>

            <!-- Категории -->
            <div class="category-filter">
                <a href="<?= BASE_URL ?>" class="category-link <?= !$selected_category ? 'active' : '' ?>">
                    <?= $lang=='ru' ? 'Все' : 'All' ?>
                </a>
                <?php foreach($categories as $cat): ?>
                    <a href="?category=<?= $cat['id'] ?>" class="category-link <?= $selected_category==$cat['id'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Карточки -->
            <div class="attractions-grid">
                <?php foreach ($attractions as $item): ?>
                    <article class="attraction-card animate-on-scroll">
                        <?php if (!empty($item['primary_image'])): ?>
                            <img src="<?= UPLOAD_URL . htmlspecialchars($item['primary_image']) ?>"
                                 class="card-img" alt="<?= htmlspecialchars($item['title']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="card-img placeholder-img"></div>
                        <?php endif; ?>
                        <div class="card-content">
                            <h2 class="card-title"><?= htmlspecialchars($item['title']) ?></h2>
                            <p class="card-description"><?= htmlspecialchars($item['short_description']) ?></p>
                            <a href="<?= BASE_URL . urlencode($item['slug']) ?>" class="btn">
                                <?= __('read_more') ?>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>

            </div>
                <?php if (count($attractions) == $limit && ($offset + $limit) < $totalAttractions): ?>
                    <div class="load-more-container">
                        <button id="loadMoreBtn" class="btn btn-outline load-more-btn" data-page="<?= $page + 1 ?>">
                            <?= $lang == 'ru' ? 'Показать ещё' : 'Load more' ?>
                        </button>
                    </div>
                <?php endif; ?>
        </main>

        <!-- faq -->
        <?php include 'components/faq.php'; ?>

        <footer class="site-footer">
            <div class="container">
                <p>© <?= date('Y') ?> Омск. Историческое наследие.</p>
                <p>
                    <a href="<?= BASE_URL ?>privacy">Политика конфиденциальности</a> |
                    <a href="<?= BASE_URL ?>terms">Пользовательское соглашение</a>
                </p>
            </div>
        </footer>

        <script>
            // Поиск
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
                                    `<a href="<?= BASE_URL ?>${item.slug}" class="suggestion-item">${item.title}</a>`
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
                    window.location.href = `<?= BASE_URL ?>?search=${encodeURIComponent(searchInput.value)}`;
                }
            });

            // Тема и шрифт
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

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('visible');
                        }
                    });
                }, { threshold: 0.1 });
                document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));
            })();
        </script>
        <script>
            let currentPage = 2;
            const loadMoreBtn = document.getElementById('loadMoreBtn');
            const container = document.querySelector('.attractions-grid');

            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', function() {
                    const btn = this;
                    btn.disabled = true;
                    btn.textContent = '<?= $lang == 'ru' ? 'Загрузка...' : 'Loading...' ?>';

                    const url = new URL('<?= BASE_URL ?>ajax_load_more.php', window.location.origin);
                    url.searchParams.set('page', currentPage);
                    url.searchParams.set('ajax', '1');
                    // Передаём текущие фильтры
                    const params = new URLSearchParams(window.location.search);
                    if (params.has('category')) url.searchParams.set('category', params.get('category'));
                    if (params.has('search')) url.searchParams.set('search', params.get('search'));

                    fetch(url)
                        .then(r => r.json())
                        .then(data => {
                            if (data.html) {
                                container.insertAdjacentHTML('beforeend', data.html);
                                currentPage++;
                                btn.dataset.page = currentPage;
                                btn.textContent = '<?= $lang == 'ru' ? 'Показать ещё' : 'Load more' ?>';
                                btn.disabled = false;

                                // Обновляем анимации для новых карточек
                                const observer = new IntersectionObserver((entries) => {
                                    entries.forEach(entry => {
                                        if (entry.isIntersecting) {
                                            entry.target.classList.add('visible');
                                        }
                                    });
                                }, { threshold: 0.1 });

                                document.querySelectorAll('.attraction-card:not(.visible)').forEach(card => {
                                    observer.observe(card);
                                });

                                if (!data.hasMore) btn.remove();
                            } else {
                                btn.remove();
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            btn.textContent = '<?= $lang == 'ru' ? 'Ошибка' : 'Error' ?>';
                            btn.disabled = false;
                        });
                });
            }
            </script>
    </body>
    </html>
<?php
exit;
}

/*
|--------------------------------------------------------------------------
| Страница достопримечательности
|--------------------------------------------------------------------------
*/

$attraction = getAttractionBySlug($request);

if ($attraction) {
    $slug = $request;
    require 'attraction.php';
    exit;
}


/*
|--------------------------------------------------------------------------
| Статические страницы
|--------------------------------------------------------------------------
*/

if (in_array($request, ['about', 'privacy', 'terms', 'omsk-1-day', 'omsk-with-kids', 'omsk-photo-spots'])) {
    require_once $request . '.php';
    exit;
}


/*
|--------------------------------------------------------------------------
| 404
|--------------------------------------------------------------------------
*/

http_response_code(404);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
<meta charset="UTF-8">
<title>404 — Страница не найдена</title>
<link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
</head>
<body>

<div class="container" style="padding:4rem 0;text-align:center;">
<h1>Страница не найдена</h1>
<a href="<?= BASE_URL ?>" class="btn">На главную</a>
</div>

</body>
</html>
