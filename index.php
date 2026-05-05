<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';

// =====================================================
// КРИТИЧЕСКАЯ ОЧИСТКА ГРЯЗНЫХ URL (ПЕРВЫЙ ПРИОРИТЕТ)
// =====================================================

// Если есть route=index.php в любом виде — РЕДИРЕКТ на чистую главную
// if (isset($_GET['route']) && strpos($_GET['route'], 'index.php') !== false) {
//     $lang = $_GET['lang'] ?? 'ru';
//     header("Location: /{$lang}/", true, 301);
//     exit;
// }




// =====================================================
// ЧИСТЫЙ ROUTE ИЗ PATH (игнорируем GET параметры)
// =====================================================
$request = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Извлекаем язык из пути /ru/ или /en/
if (preg_match('#^([a-z]{2})/#', $request, $matches)) {
    $lang = $matches[1];
    $request = substr($request, strlen($matches[0])); // убираем /ru/
} else {
    $lang = $_GET['lang'] ?? 'ru';
}

$request = trim($request, '/');

// Полностью удаляем index.php из маршрута
$request = preg_replace('#index\.php#i', '', $request);
$request = trim($request, '/');

// Если пустой — главная
if (empty($request)) {
    $request = '';
}

if (!in_array($lang, ['ru', 'en'])) {
    $lang = 'ru';
}

$_SESSION['lang'] = $lang;

// Дополнительная защита (на всякий случай)
if ($request === 'index.php') {
    header("Location: /{$lang}/", true, 301);
    exit;
}
/*
|--------------------------------------------------------------------------
| Определяем текущий запрос
|--------------------------------------------------------------------------
*/



/*
|--------------------------------------------------------------------------
| ROUTE (ПРАВИЛЬНО)
|--------------------------------------------------------------------------
*/

$request = trim($_GET['route'] ?? '', '/');

// защита от index.php
if ($request === 'index.php') {
    $request = '';
}

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


if ($request === 'articles' || strpos($request, 'article/') === 0) {
    if ($request === 'articles') {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 6;
        $offset = ($page - 1) * $limit;
        $articles = getArticles($limit, $offset);
        $totalArticles = getTotalArticles();
        $hasMore = ($offset + $limit) < $totalArticles;

        $pageTitle = $lang == 'ru' ? 'Статьи — Омскъ Исторический' : 'Articles — Historical Omsk';
        $pageDescription = $lang == 'ru' ? 'Читайте наши статьи о достопримечательностях Омска' : 'Read our articles about Omsk landmarks';
        require_once 'articles.php';
        exit;
    } elseif (strpos($request, 'article/') === 0) {
        $articleSlug = substr($request, 8);
        $article = getArticleBySlug($articleSlug);
        if ($article) {
            require_once 'article-detail.php';
            exit;
        }
    }
}
/*
|--------------------------------------------------------------------------
| Проверка маршрутов
|--------------------------------------------------------------------------
// Проверка маршрутов: /routes и /routes/любой-slug
*/
// Проверка маршрутов: /routes и /routes/слаг
// Проверка маршрутов: /kuda-shodit-v-omske и /kuda-shodit-v-omske/слаг
if ($request === 'kuda-shodit-v-omske' || strpos($request, 'kuda-shodit-v-omske/') === 0) {
    if ($request === 'kuda-shodit-v-omske') {
        $routes = getAllRoutes();
        $pageTitle = $lang == 'ru' ? 'Маршруты по Омску' : 'Omsk Routes';
        $pageDescription = $lang == 'ru'
            ? 'Готовые пешие маршруты по Омску: от исторического центра до кофейных прогулок.'
            : 'Ready-made walking routes around Omsk: from historic center to coffee walks.';
        $ogImage = BASE_URL . 'uploads/hero-bg.jpg';
        $canonicalUrl = BASE_URL . 'kuda-shodit-v-omske';
        require_once 'kuda-shodit-v-omske.php';
        exit;
    } else {
        $routeSlug = substr($request, strlen('kuda-shodit-v-omske/'));
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

    $pageTitle = $lang == 'ru'
        ? 'Достопримечательности Омска: куда сходить, что посмотреть, фото и описания — Омскъ Исторический'
        : 'Omsk Landmarks: What to See, Photos, Descriptions, Map — Historical Omsk';
    $pageDescription = $lang === 'ru'
        ? 'Достопримечательности Омска: куда сходить, что посмотреть, где погулять. Полный путеводитель по Омску с фото, описаниями, маршрутами и советами.'
        : 'Omsk landmarks: what to see, where to walk, photos, descriptions, routes and tips.';
    $ogImage = BASE_URL . 'uploads/hero-bg.jpg';
    $canonicalUrl = BASE_URL . trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');



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

        <link rel="alternate" hreflang="ru" href="<?= BASE_URL ?>ru/">
        <link rel="alternate" hreflang="en" href="<?= BASE_URL ?>en/">
        <link rel="alternate" hreflang="x-default" href="<?= BASE_URL ?>ru/">

        <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
        <link rel="stylesheet" href="<?= BASE_URL ?>css/faq.css">
        <link rel="stylesheet" href="<?= BASE_URL ?>css/card.css">

        <?php include 'includes/metrica.php'; ?>

    </head>


    <body>
        <?php
        $slugForLang = '';
        include 'components/header.php';
        ?>

        <!-- HERO БАННЕР -->
        <section class="hero">
            <div class="hero-background">
                <img src="<?= UPLOAD_URL ?>e83ce0b649358602599a9b4f6c72a4e5.jpg" alt="Панорама Омска — достопримечательности исторического центра" loading="eager">
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
                SELECT r.slug, rt.title, rt.short_description, r.distance, r.duration, r.stops_count
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
                            <?php if (!empty($pr['short_description'])): ?>
                                <p class="route-description"><?= htmlspecialchars($pr['short_description']) ?></p>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>kuda-shodit-v-omske/<?= urlencode($pr['slug']) ?>" class="btn">
                                <?= $lang == 'ru' ? 'Смотреть маршрут' : 'View route' ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a href="<?= BASE_URL ?>kuda-shodit-v-omske" class="btn btn-outline"><?= $lang == 'ru' ? 'Все маршруты' : 'All routes' ?></a>
            </section>
        <?php endif; ?>

        <main class="container" id="explore">
            <h2 class="main-title"><?= __('attractions') ?> Омска</h2>

            <!-- Поиск -->
            <div class="search-wrapper">
                <input type="text" id="searchInput" class="search-input"
                    placeholder="<?= $lang == 'ru' ? 'Поиск...' : 'Search...' ?>"
                    value="<?= htmlspecialchars($search_query) ?>">
                <div id="searchSuggestions" class="search-suggestions"></div>
            </div>

            <!-- Категории -->
            <div class="category-filter" id="categoryFilter">
                <a href="#" class="category-link <?= !$selected_category ? 'active' : '' ?>" data-category=""><?= $lang == 'ru' ? 'Все' : 'All' ?></a>
                <?php foreach ($categories as $cat): ?>
                    <a href="#" class="category-link <?= $selected_category == $cat['id'] ? 'active' : '' ?>"
                        data-category="<?= $cat['id'] ?>" style="border-left: 6px solid <?= htmlspecialchars($cat['color']) ?>;">
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
                                class="card-img" alt="<?= htmlspecialchars($item['title']) ?> — достопримечательность Омска" loading="lazy">
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
        <?php
        $homeArticles = getArticles(3, 0);
        if (!empty($homeArticles)):
        ?>
            <section class="home-articles container">
                <h2><?= $lang == 'ru' ? 'Статьи и новости' : 'Articles & News' ?></h2>
                <div class="articles-grid">
                    <?php foreach ($homeArticles as $article): ?>
                        <article class="article-card">
                            <?php if ($article['image']): ?>
                                <img src="<?= BASE_URL ?>uploads/articles/<?= htmlspecialchars($article['image']) ?>"
                                    alt="<?= htmlspecialchars($article['title']) ?>"
                                    class="article-card-img" loading="lazy">
                            <?php endif; ?>
                            <div class="article-card-content">
                                <h3><?= htmlspecialchars($article['title']) ?></h3>
                                <p><?= htmlspecialchars($article['short_description']) ?></p>
                                <a href="<?= BASE_URL ?>article/<?= urlencode($article['slug']) ?>" class="btn">
                                    <?= $lang == 'ru' ? 'Читать' : 'Read' ?>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="load-more-container">
                    <a href="<?= BASE_URL ?>articles" class="btn btn-outline">
                        <?= $lang == 'ru' ? 'Больше статей' : 'More articles' ?>
                    </a>
                </div>
            </section>
        <?php endif; ?>
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
                }, {
                    threshold: 0.1
                });
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
                                }, {
                                    threshold: 0.1
                                });

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
    <script>
        // AJAX-фильтрация по категориям
        document.querySelectorAll('.category-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const categoryId = this.dataset.category;

                // Обновляем активный класс
                document.querySelectorAll('.category-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');

                // Обновляем URL без перезагрузки
                const url = new URL(window.location);
                if (categoryId) {
                    url.searchParams.set('category', categoryId);
                } else {
                    url.searchParams.delete('category');
                }
                history.pushState({}, '', url);

                // Сбрасываем пагинацию (скрываем кнопку "Показать ещё", обнуляем текущую страницу)
                const loadMoreBtn = document.getElementById('loadMoreBtn');
                if (loadMoreBtn) loadMoreBtn.style.display = 'none';
                currentPage = 2;

                // AJAX-запрос
                fetch(`ajax_filter_attractions.php?category=${categoryId}&lang=<?= $lang ?>`)
                    .then(r => r.json())
                    .then(data => {
                        document.querySelector('.attractions-grid').innerHTML = data.html;
                        // Обновляем анимации
                        const observer = new IntersectionObserver((entries) => {
                            entries.forEach(entry => {
                                if (entry.isIntersecting) entry.target.classList.add('visible');
                            });
                        }, {
                            threshold: 0.1
                        });
                        document.querySelectorAll('.attraction-card').forEach(card => observer.observe(card));
                    });
            });
        });
    </script>

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
