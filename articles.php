<?php
if (!isset($articles)) {
    header('Location: ' . BASE_URL);
    exit;
}
if (!isset($lang)) {
    $lang = $_SESSION['lang'] ?? 'ru';
}
if (!isset($page)) {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
}
if (!isset($hasMore)) {
    $hasMore = false;
}

// Получаем категории для навигации
$articleCategories = $pdo->query("
    SELECT c.id, c.slug, ct.name
    FROM article_categories c
    JOIN article_category_translations ct ON c.id = ct.category_id AND ct.language_code = '{$lang}'
    ORDER BY ct.name
")->fetchAll();

// Популярные статьи (Featured)
$featuredArticles = $pdo->query("
    SELECT a.id, a.slug, a.image, t.title, t.short_description
    FROM articles a
    JOIN article_translations t ON a.id = t.article_id AND t.language_code = '{$lang}'
    WHERE a.is_published = 1
    ORDER BY a.views_count DESC
    LIMIT 3
")->fetchAll();

// SEO-заголовки
$pageTitle = $lang == 'ru'
    ? 'Статьи о достопримечательностях и интересных местах Омска — Омскъ Исторический'
    : 'Articles about Omsk landmarks and interesting places — Historical Omsk';
$pageDescription = $lang == 'ru'
    ? 'Читайте статьи о достопримечательностях Омска: куда сходить, что посмотреть, гиды по городу, новости и советы для туристов.'
    : 'Read articles about Omsk landmarks: where to go, what to see, city guides, news and tips for tourists.';
$ogImage = BASE_URL . 'uploads/hero-bg.jpg';
$canonicalUrl = BASE_URL . 'articles';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">

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
    <link rel="stylesheet" href="<?= BASE_URL ?>css/hlebnikrosh.css">
    <?php include 'includes/metrica.php'; ?>

    <!-- Структурированные данные -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "ItemList",
            "name": "Статьи о достопримечательностях Омска",
            "description": "<?= htmlspecialchars($pageDescription) ?>",
            "numberOfItems": <?= count($articles) ?>,
            "itemListElement": [
                <?php $i = 0;
                foreach ($articles as $article): ?>
                    <?= $i > 0 ? ',' : '' ?> {
                        "@type": "ListItem",
                        "position": <?= $i + 1 ?>,
                        "item": {
                            "@type": "Article",
                            "name": "<?= htmlspecialchars($article['title']) ?>",
                            "url": "<?= BASE_URL ?>article/<?= urlencode($article['slug']) ?>"
                        }
                    }
                <?php $i++;
                endforeach; ?>
            ]
        }
    </script>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "Омскъ Исторический",
            "url": "<?= BASE_URL ?>",
            "logo": "<?= BASE_URL ?>img/logo.png"
        }
    </script>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebSite",
            "name": "Омскъ Исторический",
            "url": "<?= BASE_URL ?>",
            "potentialAction": {
                "@type": "SearchAction",
                "target": "<?= BASE_URL ?>search?q={search_term_string}",
                "query-input": "required name=search_term_string"
            }
        }
    </script>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "mainEntity": [
                <?php if ($lang == 'ru'): ?> {
                        "@type": "Question",
                        "name": "Куда сходить в Омске зимой?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "Зимой в Омске можно посетить ледовые городки, катки в парках, Омский цирк, музеи и театры. Также популярны экскурсии по историческому центру с гидом."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "Что посмотреть в Омске за 1 день?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "За один день можно успеть посетить Омскую крепость, прогуляться по Любинскому проспекту, увидеть Успенский собор и сделать фото с памятником Степанычу. Начните с набережной Иртыша, а завершите день в Омском театре драмы."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "Какие бесплатные места в Омске стоит посетить?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "Бесплатно можно посетить Омскую крепость (территория открыта), Иртышскую набережную, Любинский проспект, памятник Степанычу, а также многие храмы, включая Успенский собор."
                        }
                    }
                <?php else: ?> {
                        "@type": "Question",
                        "name": "Where to go in Omsk in winter?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "In winter you can visit ice towns, skating rinks in parks, Omsk Circus, museums and theaters. Guided tours of the historic center are also popular."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "What to see in Omsk in 1 day?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "In one day you can visit Omsk Fortress, walk along Lyubinsky Prospect, see the Dormition Cathedral, and take a photo with the Styopanych monument. Start at the Irtysh Embankment and end at the Omsk Drama Theater."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "What free places in Omsk are worth visiting?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "You can visit Omsk Fortress (territory is open), Irtysh Embankment, Lyubinsky Prospect, the Styopanych monument, and many churches including the Dormition Cathedral for free."
                        }
                    }
                <?php endif; ?>
            ]
        }
    </script>

    <style>
        /* Hero-секция */
        .articles-hero {
            background: linear-gradient(135deg, rgba(179, 78, 58, 0.08) 0%, rgba(143, 59, 42, 0.02) 100%);
            border-radius: var(--radius);
            padding: 3rem 2rem;
            margin: 2rem 0;
            text-align: center;
            animation: subtleFade 0.8s ease-out;
        }

        .articles-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 5vw, 3.5rem);
            margin-bottom: 1.5rem;
            color: var(--text);
        }

        .articles-hero p {
            font-size: 1.2rem;
            color: var(--muted);
            max-width: 700px;
            margin: 0 auto 2rem;
        }

        .hero-cta {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .hero-cta .btn {
            padding: 0.75rem 2rem;
            background: var(--primary);
            color: #fff;
            border-radius: 2rem;
            text-decoration: none;
            font-weight: 500;
            transition: transform 0.3s;
        }

        .hero-cta .btn:hover {
            transform: scale(1.05);
        }

        @keyframes subtleFade {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Навигация по категориям */
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

        /* Карточки статей */
        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 2.5rem;
            margin: 3rem 0;
        }

        .article-card {
            background: var(--surface);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all var(--transition);
            opacity: 0;
            transform: translateY(30px);
        }

        .article-card.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .article-card:hover {
            transform: translateY(-6px) scale(1.01);
            box-shadow: var(--shadow-hover);
        }

        .article-card-img {
            width: 100%;
            height: 210px;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .article-card:hover .article-card-img {
            transform: scale(1.05);
        }

        .article-card-content {
            padding: 1.8rem 1.8rem 2rem;
        }

        .article-card-content h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .article-card-content p {
            color: var(--muted);
            margin-bottom: 1.5rem;
        }

        /* SEO-текст */
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

        .seo-block h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin: 2rem 0 1rem;
            color: var(--primary-dark);
        }

        /* Popular Searches */
        .popular-searches {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            margin: 2rem 0;
        }

        .popular-searches a {
            padding: 0.4rem 1.2rem;
            background: var(--primary-soft);
            color: var(--primary-dark);
            border-radius: 2rem;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
        }

        .popular-searches a:hover {
            background: var(--primary);
            color: #fff;
        }

        /* FAQ */
        .faq-list dt {
            font-weight: 600;
            cursor: pointer;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--border);
        }

        .faq-list dd {
            margin: 0 0 1rem 0;
            display: none;
        }

        .faq-list dt.active+dd {
            display: block;
        }

        /* Прогресс-бар */
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

        /* Featured Articles */
        .featured-articles {
            margin: 2rem 0;
            padding: 2rem;
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .featured-articles h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }

        .featured-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
        }

        @media (max-width: 768px) {
            .articles-hero {
                padding: 2rem 1rem;
            }

            .articles-grid {
                grid-template-columns: 1fr;
            }

            .hero-cta {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>

<body>
    <div class="reading-progress" id="progressBar"></div>
    <?php $slugForLang = '';
    include 'components/header.php'; ?>

    <main class="container">
        <div class="breadcrumbs">
            <a href="<?= BASE_URL ?>"><?= __('home') ?></a> /
            <span><?= $lang == 'ru' ? 'Статьи' : 'Articles' ?></span>
        </div>

        <!-- HERO -->
        <section class="articles-hero">
            <h1><?= $lang == 'ru' ? 'Статьи о достопримечательностях и интересных местах Омска' : 'Articles about Omsk Landmarks & Interesting Places' ?></h1>
            <p><?= $lang == 'ru' ? 'Гид по Омску для тех, кто ищет, куда сходить и что посмотреть. Выбирайте категорию, изучайте статьи и планируйте идеальную прогулку.' : 'A guide to Omsk for those looking for places to go and things to see. Choose a category, explore articles and plan your perfect walk.' ?></p>
            <div class="hero-cta">
                <a href="<?= BASE_URL ?>" class="btn"><?= $lang == 'ru' ? 'Достопримечательности' : 'Landmarks' ?></a>
                <a href="<?= BASE_URL ?>kuda-shodit-v-omske" class="btn"><?= $lang == 'ru' ? 'Маршруты' : 'Routes' ?></a>
                <a href="<?= BASE_URL ?>articles" class="btn"><?= $lang == 'ru' ? 'Все статьи' : 'All articles' ?></a>
            </div>
        </section>

        <!-- НАВИГАЦИЯ ПО КАТЕГОРИЯМ -->
        <?php if (!empty($articleCategories)): ?>
            <nav class="category-nav">
                <a href="<?= BASE_URL ?>articles" class="<?= !isset($_GET['category']) ? 'active' : '' ?>"><?= $lang == 'ru' ? 'Все' : 'All' ?></a>
                <?php foreach ($articleCategories as $cat): ?>
                    <a href="?category=<?= $cat['id'] ?>" class="<?= ($_GET['category'] ?? '') == $cat['id'] ? 'active' : '' ?>"><?= htmlspecialchars($cat['name']) ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <!-- FEATURED ARTICLES -->
        <?php if (!empty($featuredArticles)): ?>
            <section class="featured-articles">
                <h2><?= $lang == 'ru' ? 'Популярные статьи' : 'Popular Articles' ?></h2>
                <div class="featured-grid">
                    <?php foreach ($featuredArticles as $fa): ?>
                        <article class="article-card">
                            <?php if ($fa['image']): ?>
                                <img src="<?= BASE_URL ?>uploads/articles/<?= htmlspecialchars($fa['image']) ?>" class="article-card-img" alt="<?= htmlspecialchars($fa['title']) ?>" loading="lazy">
                            <?php endif; ?>
                            <div class="article-card-content">
                                <h3><?= htmlspecialchars($fa['title']) ?></h3>
                                <p><?= htmlspecialchars($fa['short_description']) ?></p>
                                <a href="<?= BASE_URL ?>article/<?= urlencode($fa['slug']) ?>" class="btn"><?= $lang == 'ru' ? 'Читать' : 'Read' ?></a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- ОСНОВНОЙ СПИСОК СТАТЕЙ -->
        <h2><?= $lang == 'ru' ? 'Новые статьи об Омске' : 'New Articles about Omsk' ?></h2>
        <div class="articles-grid">
            <?php foreach ($articles as $article): ?>
                <article class="article-card">
                    <?php if ($article['image']): ?>
                        <img src="<?= BASE_URL ?>uploads/articles/<?= htmlspecialchars($article['image']) ?>"
                            class="article-card-img"
                            alt="<?= htmlspecialchars($article['title']) ?>"
                            loading="lazy"
                            width="400" height="210">
                    <?php endif; ?>
                    <div class="article-card-content">
                        <h2><?= htmlspecialchars($article['title']) ?></h2>
                        <p><?= htmlspecialchars($article['short_description']) ?></p>
                        <a href="<?= BASE_URL ?>article/<?= urlencode($article['slug']) ?>" class="btn">
                            <?= $lang == 'ru' ? 'Читать' : 'Read' ?>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($hasMore): ?>
            <div class="load-more-container">
                <a href="?page=<?= $page + 1 ?>" class="btn btn-outline">
                    <?= $lang == 'ru' ? 'Больше статей' : 'More articles' ?>
                </a>
            </div>
        <?php endif; ?>

        <!-- SEO ТЕКСТ -->
        <section class="seo-block">
            <h2><?= $lang == 'ru' ? 'Гид по Омску — куда сходить и что посмотреть' : 'Omsk Guide — Where to Go & What to See' ?></h2>
            <?php if ($lang == 'ru'): ?>
                <p>Омск — город с богатой историей и удивительной архитектурой. Здесь каждый найдёт что-то для себя: от прогулок по историческому центру до знакомства с современными арт-пространствами. В нашем блоге мы собираем лучшие статьи о достопримечательностях, событиях и скрытых уголках города.</p>
                <p>Планируете выходные? Ищете, куда сходить бесплатно? Хотите узнать о необычных местах Омска? Наши авторы регулярно публикуют новые материалы, которые помогут вам составить идеальный маршрут.</p>
            <?php else: ?>
                <p>Omsk is a city with a rich history and amazing architecture. Everyone will find something for themselves: from walks through the historic center to exploring modern art spaces. In our blog, we collect the best articles about landmarks, events and hidden corners of the city.</p>
                <p>Planning a weekend? Looking for free things to do? Want to learn about unusual places in Omsk? Our authors regularly publish new materials that will help you create the perfect itinerary.</p>
            <?php endif; ?>
        </section>

        <!-- FAQ -->
        <section class="seo-block">
            <h2><?= $lang == 'ru' ? 'Часто задаваемые вопросы об Омске' : 'Frequently Asked Questions about Omsk' ?></h2>
            <dl class="faq-list">
                <?php if ($lang == 'ru'): ?>
                    <dt>Куда сходить в Омске зимой?</dt>
                    <dd>Зимой в Омске можно посетить ледовые городки, катки в парках, Омский цирк, музеи и театры. Также популярны экскурсии по историческому центру с гидом.</dd>
                    <dt>Что посмотреть в Омске за 1 день?</dt>
                    <dd>За один день можно успеть посетить Омскую крепость, прогуляться по Любинскому проспекту, увидеть Успенский собор и сделать фото с памятником Степанычу. Начните с набережной Иртыша, а завершите день в Омском театре драмы.</dd>
                    <dt>Какие бесплатные места в Омске стоит посетить?</dt>
                    <dd>Бесплатно можно посетить Омскую крепость (территория открыта), Иртышскую набережную, Любинский проспект, памятник Степанычу, а также многие храмы, включая Успенский собор.</dd>
                <?php else: ?>
                    <dt>Where to go in Omsk in winter?</dt>
                    <dd>In winter you can visit ice towns, skating rinks in parks, Omsk Circus, museums and theaters. Guided tours of the historic center are also popular.</dd>
                    <dt>What to see in Omsk in 1 day?</dt>
                    <dd>In one day you can visit Omsk Fortress, walk along Lyubinsky Prospect, see the Dormition Cathedral, and take a photo with the Styopanych monument. Start at the Irtysh Embankment and end at the Omsk Drama Theater.</dd>
                    <dt>What free places in Omsk are worth visiting?</dt>
                    <dd>You can visit Omsk Fortress (territory is open), Irtysh Embankment, Lyubinsky Prospect, the Styopanych monument, and many churches including the Dormition Cathedral for free.</dd>
                <?php endif; ?>
            </dl>
        </section>

        <!-- POPULAR SEARCHES -->
        <section class="popular-searches">
            <strong><?= $lang == 'ru' ? 'Популярные запросы:' : 'Popular searches:' ?></strong>
            <a href="<?= BASE_URL ?>kuda-shodit-v-omske"><?= $lang == 'ru' ? 'куда сходить в Омске сегодня' : 'where to go in Omsk today' ?></a>
            <a href="<?= BASE_URL ?>"><?= $lang == 'ru' ? 'куда сходить вечером' : 'where to go in the evening' ?></a>
            <a href="<?= BASE_URL ?>"><?= $lang == 'ru' ? 'куда сходить бесплатно' : 'free things to do' ?></a>
        </section>

        <!-- ВНУТРЕННЯЯ ПЕРЕЛИНКОВКА -->
        <section class="internal-links">
            <strong><?= $lang == 'ru' ? 'Популярные разделы:' : 'Popular sections:' ?></strong>
            <a href="<?= BASE_URL ?>kuda-shodit-v-omske"><?= $lang == 'ru' ? 'Маршруты по Омску' : 'Routes in Omsk' ?></a>
            <a href="<?= BASE_URL ?>"><?= $lang == 'ru' ? 'Достопримечательности Омска' : 'Omsk Landmarks' ?></a>
            <a href="<?= BASE_URL ?>"><?= $lang == 'ru' ? 'Интересные места Омска' : 'Interesting Places' ?></a>
            <a href="<?= BASE_URL ?>"><?= $lang == 'ru' ? 'Прогулки по Омску' : 'Walks in Omsk' ?></a>
            <a href="<?= BASE_URL ?>"><?= $lang == 'ru' ? 'Советы туристам' : 'Tourist Tips' ?></a>
        </section>

        <p class="last-updated"><?= $lang == 'ru' ? 'Последнее обновление:' : 'Last updated:' ?> <?= date('d.m.Y') ?></p>
    </main>

    <?php include 'components/footer.php'; ?>
    <script src="<?= BASE_URL ?>js/theme.js"></script>
    <script>
        // Прогресс-бар чтения
        window.addEventListener('scroll', function() {
            const winHeight = window.innerHeight;
            const docHeight = document.documentElement.scrollHeight - winHeight;
            const scrolled = (window.scrollY / docHeight) * 100;
            document.getElementById('progressBar').style.width = scrolled + '%';
        });

        // Анимация появления карточек
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -30px 0px'
        });
        document.querySelectorAll('.article-card').forEach(card => observer.observe(card));

        // FAQ аккордеон
        document.querySelectorAll('.faq-list dt').forEach(dt => {
            dt.addEventListener('click', function() {
                this.classList.toggle('active');
                const dd = this.nextElementSibling;
                dd.style.display = dd.style.display === 'block' ? 'none' : 'block';
            });
        });
    </script>
</body>

</html>
