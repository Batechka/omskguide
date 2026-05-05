<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($routes)) {
    header('Location: ' . BASE_URL . 'kuda-shodit-v-omske');
    exit;
}

$pageTitle = $lang == 'ru' ? 'Пешие маршруты по Омску — прогулки и экскурсии' : 'Walking Routes in Omsk — Tours & Walks';
$pageDescription = $lang == 'ru'
    ? 'Готовые пешие маршруты по Омску: от исторического центра до кофейных прогулок. Карты, описания, рекомендации.'
    : 'Ready-made walking routes around Omsk: from historic center to coffee walks. Maps, descriptions, tips.';
$ogImage = BASE_URL . 'uploads/hero-bg.jpg';
$canonicalUrl = BASE_URL . 'kuda-shodit-v-omske';

// Популярные маршруты
$popularRoutes = $pdo->query("
    SELECT r.slug, rt.title, r.distance, r.duration, r.stops_count
    FROM routes r
    JOIN route_translations rt ON r.id = rt.route_id AND rt.language_code = '{$lang}'
    WHERE r.is_popular = 1
    LIMIT 3
")->fetchAll();

// Элементы для ItemList schema
$itemListElements = [];
foreach ($routes as $index => $route) {
    $itemListElements[] = [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'item' => [
            '@type' => 'TouristTrip',
            'name' => $route['title'],
            'description' => $route['short_description'],
            'url' => BASE_URL . 'kuda-shodit-v-omske/' . urlencode($route['slug']),
            'touristType' => 'Walking',
            'itinerary' => ['@type' => 'Place', 'name' => $route['title']]
        ]
    ];
}
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

    <link rel="alternate" hreflang="ru" href="<?= BASE_URL ?>ru/">
    <link rel="alternate" hreflang="en" href="<?= BASE_URL ?>en/">
    <link rel="alternate" hreflang="x-default" href="<?= BASE_URL ?>ru/">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/hlebnikrosh.css">

    <!-- Структурированные данные -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "ItemList",
            "name": "Пешие маршруты по Омску",
            "description": "<?= htmlspecialchars($pageDescription) ?>",
            "numberOfItems": <?= count($routes) ?>,
            "itemListElement": <?= json_encode($itemListElements, JSON_UNESCAPED_UNICODE) ?>
        }
    </script>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "TouristDestination",
            "name": "Омск",
            "description": "Исторический центр Омска с пешеходными маршрутами.",
            "touristType": ["Walking", "City"],
            "mainEntityOfPage": "<?= BASE_URL ?>"
        }
    </script>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "Омскъ Исторический",
            "url": "<?= BASE_URL ?>",
            "logo": "<?= BASE_URL ?>img/logo.png",
            "contactPoint": {
                "@type": "ContactPoint",
                "email": "info@omskguide.ru",
                "contactType": "customer service"
            }
        }
    </script>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "BreadcrumbList",
            "itemListElement": [{
                    "@type": "ListItem",
                    "position": 1,
                    "name": "Главная",
                    "item": "<?= BASE_URL ?>"
                },
                {
                    "@type": "ListItem",
                    "position": 2,
                    "name": "Маршруты по Омску",
                    "item": "<?= $canonicalUrl ?>"
                }
            ]
        }
    </script>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "mainEntity": [{
                    "@type": "Question",
                    "name": "Как выбрать пеший маршрут по Омску?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Ориентируйтесь на длительность и расстояние. Для первого знакомства подойдёт маршрут «Омск за 1 день», который охватывает Омскую крепость, Любинский проспект и Успенский собор."
                    }
                },
                {
                    "@type": "Question",
                    "name": "Где погулять в Омске бесплатно?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Бесплатные прогулки доступны по Иртышской набережной, Любинскому проспекту, Омской крепости, скверу им. Дзержинского и парку «Зелёный остров»."
                    }
                }
            ]
        }
    </script>

    <?php include 'includes/metrica.php'; ?>
    <style>
        /* --- Hero-секция --- */
        .routes-hero {
            background: linear-gradient(135deg, rgba(179, 78, 58, 0.08) 0%, rgba(143, 59, 42, 0.02) 100%);
            border-radius: 2rem;
            padding: 2.5rem 2rem;
            margin: 2rem 0;
            text-align: center;
            animation: subtleFade 0.8s ease-out;
        }

        .routes-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2rem, 5vw, 3rem);
            margin-bottom: 1rem;
            color: var(--text);
        }

        .routes-hero p {
            font-size: 1.1rem;
            color: var(--muted);
            max-width: 600px;
            margin: 0 auto 1.5rem;
        }

        .routes-hero .hero-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.7rem 2rem;
            background: var(--primary);
            color: #fff;
            border-radius: 2.5rem;
            text-decoration: none;
            font-weight: 500;
            transition: transform 0.2s;
        }

        .routes-hero .hero-btn:hover {
            transform: scale(1.05);
        }

        @keyframes subtleFade {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* --- SEO-контент --- */
        .seo-content {
            max-width: 800px;
            margin: 2rem auto;
            line-height: 1.7;
            font-size: 1rem;
            color: var(--muted);
        }

        .seo-content h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: var(--text);
        }

        .seo-content p {
            margin-bottom: 1rem;
        }

        /* --- Фильтр --- */
        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 2rem 0 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .filter-bar input {
            padding: 0.6rem 1.5rem;
            border: 1px solid var(--border);
            border-radius: 2rem;
            background: var(--surface);
            flex: 1;
            min-width: 200px;
        }

        .filter-bar select {
            padding: 0.6rem 1.2rem;
            border: 1px solid var(--border);
            border-radius: 2rem;
            background: var(--surface);
        }

        /* --- Карточки маршрутов --- */
        .routes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin: 2rem 0 3rem;
        }

        .route-card {
            background: var(--surface);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all var(--transition);
            position: relative;
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .route-card.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .route-card:hover {
            transform: translateY(-6px) scale(1.01);
            box-shadow: var(--shadow-hover);
        }

        .route-card-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(0deg, rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0) 50%);
            z-index: 1;
            pointer-events: none;
        }

        .route-card-content {
            padding: 1.5rem 1.8rem 2rem;
            position: relative;
            z-index: 2;
        }

        .route-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
            color: var(--muted);
        }

        .route-meta span {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.9rem;
        }

        .badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 1rem;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-weight: 500;
            font-size: 0.8rem;
        }

        .cta-animate {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(179, 78, 58, 0.4);
            }

            70% {
                box-shadow: 0 0 0 8px rgba(179, 78, 58, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(179, 78, 58, 0);
            }
        }

        /* --- Внутренние ссылки --- */
        .related-links {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin: 2rem 0;
        }

        .related-links a {
            display: inline-block;
            padding: 0.5rem 1.2rem;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 2rem;
            text-decoration: none;
            color: var(--text);
            transition: background 0.2s;
        }

        .related-links a:hover {
            background: var(--primary-soft);
        }

        /* --- Прогресс-бар --- */
        .reading-progress {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: var(--primary);
            z-index: 1001;
            width: 0;
            transition: width 0.1s linear;
        }

        /* --- FAQ accordion --- */
        .faq-accordion {
            margin: 2rem 0;
        }

        .faq-accordion dt {
            font-weight: 600;
            cursor: pointer;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border);
            user-select: none;
        }

        .faq-accordion dt::after {
            content: ' ▼';
            font-size: 0.8rem;
            color: var(--muted);
        }

        .faq-accordion dt.active::after {
            content: ' ▲';
        }

        .faq-accordion dd {
            margin: 0;
            padding: 0.75rem 0;
            display: none;
        }

        .faq-accordion dd.active {
            display: block;
        }

        @media (max-width: 768px) {
            .routes-hero {
                padding: 2rem 1rem;
            }

            .filter-bar {
                flex-direction: column;
            }

            .routes-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="reading-progress" id="progressBar"></div>
    <?php
    $slugForLang = '';
    include 'components/header.php';
    ?>

    <main class="container">
        <div class="breadcrumbs">
            <a href="<?= BASE_URL ?>"><?= __('home') ?></a> /
            <span><?= $lang == 'ru' ? 'Маршруты' : 'Routes' ?></span>
        </div>

        <!-- HERO -->
        <section class="routes-hero">
            <h1><?= $pageTitle ?></h1>
            <p><?= $lang == 'ru' ? 'Откройте для себя лучшие пешеходные маршруты по историческому центру, набережным и уютным улочкам. Готовые планы прогулок с картами, описаниями и рекомендациями.' : 'Discover the best walking routes through the historic center, embankments, and cozy streets. Ready-made plans with maps, descriptions, and tips.' ?></p>
            <a href="#routes-list" class="hero-btn"><?= $lang == 'ru' ? 'Выбрать маршрут' : 'Choose a route' ?></a>
        </section>

        <!-- ФИЛЬТР -->
        <div class="filter-bar" id="routes-list">
            <input type="text" id="routeSearch" placeholder="<?= $lang == 'ru' ? 'Поиск маршрутов...' : 'Search routes...' ?>">
            <select id="routeSort">
                <option value="default"><?= $lang == 'ru' ? 'По умолчанию' : 'Default' ?></option>
                <option value="distance"><?= $lang == 'ru' ? 'По расстоянию' : 'By distance' ?></option>
                <option value="duration"><?= $lang == 'ru' ? 'По длительности' : 'By duration' ?></option>
            </select>
        </div>

        <!-- КАРТОЧКИ -->
        <div class="routes-grid" id="routesGrid">
            <?php foreach ($routes as $route): ?>
                <article class="route-card" data-distance="<?= intval($route['distance']) ?>" data-duration="<?= $route['duration'] ?>" data-title="<?= htmlspecialchars($route['title']) ?>">
                    <div class="route-card-overlay"></div>
                    <div class="route-card-content">
                        <h2><?= htmlspecialchars($route['title']) ?></h2>
                        <div class="route-meta">
                            <?php if ($route['distance']): ?><span>🚶 <?= $route['distance'] ?></span><?php endif; ?>
                            <?php if ($route['duration']): ?><span>⏱ <?= $route['duration'] ?></span><?php endif; ?>
                            <?php if ($route['stops_count']): ?><span>📍 <?= $route['stops_count'] ?> <?= $lang == 'ru' ? 'остановок' : 'stops' ?></span><?php endif; ?>
                        </div>
                        <p><?= htmlspecialchars($route['short_description']) ?></p>
                        <a href="<?= BASE_URL ?>kuda-shodit-v-omske/<?= urlencode($route['slug']) ?>" class="btn cta-animate">
                            <?= $lang == 'ru' ? 'Смотреть маршрут' : 'View route' ?>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- SEO ТЕКСТЫ -->
        <section class="seo-content">
            <h2><?= $lang == 'ru' ? 'Как выбрать пеший маршрут по Омску' : 'How to Choose a Walking Route in Omsk' ?></h2>
            <?php if ($lang == 'ru'): ?>
                <p>Выбор идеального маршрута зависит от ваших интересов и времени. Для первого знакомства с городом лучше всего подойдёт маршрут «Омск за 1 день», который охватывает главные достопримечательности: Омскую крепость, Любинский проспект и Успенский собор. Если вы уже знакомы с центром, попробуйте тематические прогулки — кофейные, романтические или фотографические маршруты.</p>
                <p>Для семейных посетителей рекомендуем щадящие по времени и расстоянию маршруты, например «Омск с детьми». Любителям долгих прогулок понравится «Иртышская набережная — от моста до крепости». Каждый маршрут снабжён картой, подробными описаниями остановок и полезными советами.</p>
            <?php else: ?>
                <p>Choosing the ideal route depends on your interests and time. For first-time visitors, the “Omsk in 1 Day” route covering the main landmarks is the best choice. If you already know the center, try themed walks — coffee, romantic or photo routes.</p>
                <p>Families will appreciate shorter, less demanding routes like “Omsk with Kids”. Long‑walk lovers will enjoy the “Irtysh Embankment — from the bridge to the fortress”. Every route includes a map, detailed stop descriptions and helpful tips.</p>
            <?php endif; ?>
            <h2><?= $lang == 'ru' ? 'Часто задаваемые вопросы' : 'Frequently Asked Questions' ?></h2>
            <dl class="faq-accordion">
                <?php if ($lang == 'ru'): ?>
                    <dt>Можно ли пройти маршруты с детской коляской?</dt>
                    <dd>Да, большинство маршрутов проходят по асфальтированным дорожкам и тротуарам, доступным для колясок.</dd>
                    <dt>Есть ли платные участки на маршрутах?</dt>
                    <dd>Все маршруты проходят по общедоступным местам. Вход в музеи и храмы может быть платным по желанию.</dd>
                <?php else: ?>
                    <dt>Can I walk the routes with a baby stroller?</dt>
                    <dd>Yes, most routes follow paved paths and sidewalks, accessible for strollers.</dd>
                    <dt>Are there any paid areas along the routes?</dt>
                    <dd>All routes pass through public areas. Entrance to museums and churches may be paid optionally.</dd>
                <?php endif; ?>
            </dl>
        </section>

        <!-- ВНУТРЕННИЕ ССЫЛКИ -->
        <?php if (!empty($popularRoutes)): ?>
            <section class="related-links">
                <strong><?= $lang == 'ru' ? 'Популярные:' : 'Popular:' ?></strong>
                <?php foreach ($popularRoutes as $pr): ?>
                    <a href="<?= BASE_URL ?>kuda-shodit-v-omske/<?= urlencode($pr['slug']) ?>"><?= htmlspecialchars($pr['title']) ?></a>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>© <?= date('Y') ?> Омск. Историческое наследие.</p>
            <p><a href="<?= BASE_URL ?>privacy">Политика конфиденциальности</a> | <a href="<?= BASE_URL ?>terms">Пользовательское соглашение</a></p>
        </div>
    </footer>

    <script src="<?= BASE_URL ?>js/theme.js"></script>
    <script>
        // Прогресс-бар чтения
        window.addEventListener('scroll', function() {
            var winH = window.innerHeight,
                docH = document.documentElement.scrollHeight - winH,
                scrollTop = window.scrollY || window.pageYOffset,
                progress = (scrollTop / docH) * 100;
            document.getElementById('progressBar').style.width = progress + '%';
        });

        // Анимация появления карточек (без CLS)
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1
        });
        document.querySelectorAll('.route-card').forEach(card => observer.observe(card));

        // Поиск и сортировка
        document.getElementById('routeSearch')?.addEventListener('input', function(e) {
            const q = e.target.value.toLowerCase();
            document.querySelectorAll('.route-card').forEach(card => {
                const title = card.dataset.title?.toLowerCase() || '';
                card.style.display = title.includes(q) ? '' : 'none';
            });
        });

        document.getElementById('routeSort')?.addEventListener('change', function(e) {
            const sortBy = e.target.value;
            const grid = document.getElementById('routesGrid');
            const cards = Array.from(grid.querySelectorAll('.route-card'));
            if (sortBy === 'distance') {
                cards.sort((a, b) => (parseInt(a.dataset.distance) || 0) - (parseInt(b.dataset.distance) || 0));
            } else if (sortBy === 'duration') {
                cards.sort((a, b) => (a.dataset.duration || '').localeCompare(b.dataset.duration || ''));
            } else {
                return; // default order
            }
            cards.forEach(card => grid.appendChild(card));
        });

        // FAQ accordion
        document.querySelectorAll('.faq-accordion dt').forEach(dt => {
            dt.addEventListener('click', function() {
                const dd = this.nextElementSibling;
                this.classList.toggle('active');
                dd.classList.toggle('active');
            });
        });

        // Плавная прокрутка на якоря
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>

</html>
