<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$categories = getCategories();
$selected_category = $_GET['category'] ?? null;
$search_query = trim($_GET['search'] ?? '');

// Пагинация
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$totalAttractions = $pdo->query("SELECT COUNT(*) FROM attractions")->fetchColumn();
$attractions = getFilteredAttractionsPaginated($selected_category, $search_query, $limit, $offset);

// SEO-заголовки
$pageTitle = $lang == 'ru'
    ? 'Достопримечательности Омска — куда сходить, что посмотреть, фото и описания'
    : 'Omsk Landmarks — what to see, where to go, photos and descriptions';
$pageDescription = $lang == 'ru'
    ? 'Полный каталог достопримечательностей Омска: фото, описания, карты. Выбирайте категории и планируйте маршрут.'
    : 'Complete catalog of Omsk landmarks: photos, descriptions, maps. Choose categories and plan your route.';
$ogImage = BASE_URL . 'uploads/hero-bg.jpg';
$canonicalUrl = BASE_URL . 'attractions';
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
    <?php include 'includes/metrica.php'; ?>

    <!-- Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "ItemList",
      "name": "Достопримечательности Омска",
      "description": "<?= htmlspecialchars($pageDescription) ?>",
      "numberOfItems": <?= count($attractions) ?>,
      "itemListElement": [
        <?php $i = 0; foreach ($attractions as $item): ?>
          <?= $i > 0 ? ',' : '' ?>
          {
            "@type": "ListItem",
            "position": <?= $i + 1 ?>,
            "item": {
              "@type": "TouristAttraction",
              "name": "<?= htmlspecialchars($item['title']) ?>",
              "url": "<?= BASE_URL . urlencode($item['slug']) ?>"
            }
          }
        <?php $i++; endforeach; ?>
      ]
    }
    </script>

    <style>
        .attractions-hero {
            background: linear-gradient(135deg, rgba(179,78,58,0.08) 0%, rgba(143,59,42,0.02) 100%);
            border-radius: var(--radius);
            padding: 2.5rem 2rem;
            margin: 2rem 0;
            text-align: center;
        }
        .attractions-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2rem, 5vw, 3rem);
            margin-bottom: 1rem;
            color: var(--text);
        }
        .attractions-hero p {
            font-size: 1.1rem;
            color: var(--muted);
            max-width: 600px;
            margin: 0 auto 1.5rem;
        }
        .category-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            margin: 2rem 0;
        }
        .category-nav a {
            padding: 0.5rem 1.5rem;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 2rem;
            text-decoration: none;
            color: var(--text);
            font-weight: 500;
            transition: all 0.2s;
        }
        .category-nav a:hover,
        .category-nav a.active {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }
        .seo-block {
            max-width: 800px;
            margin: 3rem auto;
            line-height: 1.8;
        }
        .seo-block h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }
        @media (max-width: 768px) {
            .attractions-hero { padding: 2rem 1rem; }
        }
    </style>
</head>
<body>
    <?php $slugForLang = ''; include 'components/header.php'; ?>

    <main class="container">
        <div class="breadcrumbs">
            <a href="<?= BASE_URL ?>"><?= __('home') ?></a> /
            <span><?= $lang == 'ru' ? 'Достопримечательности' : 'Landmarks' ?></span>
        </div>

        <!-- Hero -->
        <section class="attractions-hero">
            <h1><?= $lang == 'ru' ? 'Достопримечательности Омска — куда сходить и что посмотреть' : 'Omsk Landmarks — What to See & Where to Go' ?></h1>
            <p><?= $lang == 'ru' ? 'Полный каталог достопримечательностей Омска с фото, описаниями и картами. Выбирайте категорию и планируйте идеальный маршрут.' : 'Complete catalog of Omsk landmarks with photos, descriptions and maps. Choose a category and plan your perfect route.' ?></p>
        </section>

        <!-- Поиск -->
        <div class="search-wrapper">
            <input type="text" id="searchInput" class="search-input"
                   placeholder="<?= $lang=='ru' ? 'Поиск...' : 'Search...' ?>"
                   value="<?= htmlspecialchars($search_query) ?>">
            <div id="searchSuggestions" class="search-suggestions"></div>
        </div>

        <!-- Категории -->
        <nav class="category-nav" id="categoryFilter">
            <a href="<?= BASE_URL ?>attractions" class="<?= !$selected_category ? 'active' : '' ?>"><?= $lang=='ru' ? 'Все' : 'All' ?></a>
            <?php foreach($categories as $cat): ?>
                <a href="?category=<?= $cat['id'] ?>" class="<?= $selected_category==$cat['id'] ? 'active' : '' ?>"
                   style="border-left: 6px solid <?= htmlspecialchars($cat['color']) ?>;">
                    <?= htmlspecialchars($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- Карточки -->
        <div class="attractions-grid" id="attractionsGrid">
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

        <!-- SEO-текст -->
        <section class="seo-block">
            <h2><?= $lang == 'ru' ? 'Достопримечательности Омска — полный гид' : 'Omsk Landmarks — Complete Guide' ?></h2>
            <?php if ($lang == 'ru'): ?>
                <p>Омск — город с богатой историей и уникальной архитектурой. Здесь каждый найдёт что-то для себя: от прогулок по историческому центру до знакомства с современными арт-пространствами. Наш каталог содержит десятки достопримечательностей с подробными описаниями, фотографиями и картами.</p>
                <p>Выбирайте категорию — храмы, памятники, музеи, парки — и составляйте свой идеальный маршрут. Все объекты снабжены координатами, временем работы и советами по посещению.</p>
            <?php else: ?>
                <p>Omsk is a city with a rich history and unique architecture. Everyone will find something for themselves: from walks through the historic center to exploring modern art spaces. Our catalog contains dozens of landmarks with detailed descriptions, photos and maps.</p>
                <p>Choose a category — churches, monuments, museums, parks — and plan your perfect itinerary. All objects are supplied with coordinates, opening hours and visiting tips.</p>
            <?php endif; ?>
        </section>
    </main>

    <?php include 'components/footer.php'; ?>
    <script src="<?= BASE_URL ?>js/theme.js"></script>
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
                    });
            }, 300);
        });
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target)) suggestionsBox.style.display = 'none';
        });

        // Пагинация
        let currentPage = 2;
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function() {
                const btn = this;
                btn.disabled = true;
                btn.textContent = '<?= $lang == 'ru' ? 'Загрузка...' : 'Loading...' ?>';
                const url = new URL('<?= BASE_URL ?>ajax_load_more.php', window.location.origin);
                url.searchParams.set('page', currentPage);
                url.searchParams.set('ajax', '1');
                const params = new URLSearchParams(window.location.search);
                if (params.has('category')) url.searchParams.set('category', params.get('category'));
                if (params.has('search')) url.searchParams.set('search', params.get('search'));
                fetch(url)
                    .then(r => r.json())
                    .then(data => {
                        if (data.html) {
                            document.getElementById('attractionsGrid').insertAdjacentHTML('beforeend', data.html);
                            currentPage++;
                            btn.dataset.page = currentPage;
                            btn.textContent = '<?= $lang == 'ru' ? 'Показать ещё' : 'Load more' ?>';
                            btn.disabled = false;
                            if (!data.hasMore) btn.remove();
                        } else {
                            btn.remove();
                        }
                    })
                    .catch(err => {
                        btn.textContent = '<?= $lang == 'ru' ? 'Ошибка' : 'Error' ?>';
                        btn.disabled = false;
                    });
            });
        }

        // Анимация появления
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));
    </script>
</body>
</html>
