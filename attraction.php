<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Гарантируем запуск сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Переменная $attraction должна быть передана из index.php
if (!isset($attraction) || !is_array($attraction)) {
    header('Location: ' . BASE_URL);
    exit;
}

// Счётчик просмотров (один раз за сессию) – на SEO не влияет, оставлен для интерфейса
if (!isset($_SESSION['viewed_' . $attraction['id']])) {
    $pdo->prepare("UPDATE attractions SET views_count = views_count + 1 WHERE id = ?")
        ->execute([$attraction['id']]);
    $_SESSION['viewed_' . $attraction['id']] = true;
    $attraction['views_count'] = ($attraction['views_count'] ?? 0) + 1;
}

$images = getImages($attraction['id']);
$primaryImage = !empty($images)
    ? UPLOAD_URL . $images[0]['filename']
    : BASE_URL . 'img/default-og.jpg';

// Улучшенное мета-описание
$description = $attraction['short_description'] ?? '';
if (empty($description) && !empty($attraction['full_description'])) {
    $plainText = strip_tags($attraction['full_description']);
    $description = mb_substr($plainText, 0, 157); // берём чуть меньше 160 для запаса
    if (mb_strlen($plainText) > 157) {
        $lastSpace = mb_strrpos($description, ' ');
        if ($lastSpace !== false) {
            $description = mb_substr($description, 0, $lastSpace);
        }
        $description .= '…';
    }
    $description = $description ?: 'Достопримечательность Омска: фото, описание, карта и маршрут.';
}

$title = htmlspecialchars($attraction['title'], ENT_QUOTES, 'UTF-8');
// Улучшенный Title с ключевыми словами
$baseTitle = $title . ' — достопримечательность Омска';
if (mb_strlen($baseTitle) > 60) {
    $fullTitle = $title . ' — Омск'; // краткий вариант для очень длинных названий
} else {
    $fullTitle = $baseTitle . ' | ' . __('site_title');
}

// Канонический URL – чистый ЧПУ без параметров
$canonicalSlug = rawurlencode($slug);
$pageUrl = rtrim(BASE_URL, '/') . '/' . rawurlencode($slug);

// Похожие достопримечательности
$relatedAttractions = getRelatedAttractions($attraction['id'], 3);

// Время чтения (корректный подсчёт слов для кириллицы, защита от пустоты)
$fullText = strip_tags($attraction['full_description'] ?? $attraction['short_description']);
if (!empty($fullText)) {
    $wordCount = preg_match_all('/\p{L}+/u', $fullText ?? '', $m);
    $readingTime = ceil($wordCount / 200);
} else {
    $readingTime = 1;
}
$readingTimeText = $lang == 'ru'
    ? "Время чтения: ~{$readingTime} мин"
    : "Reading time: ~{$readingTime} min";

// Оглавление (все заголовки h1-h6)
$toc = generateTOC($attraction['full_description'] ?? '');

// Дата с учётом языка
$createdDate = !empty($attraction['created_at'])
    ? formatDate($attraction['created_at'], $lang)
    : '';

// Тексты интерфейса
$shareText = $lang == 'ru' ? 'Поделиться' : 'Share';
$editText = $lang == 'ru' ? 'Редактировать' : 'Edit';
$relatedTitle = $lang == 'ru' ? 'Вам также может быть интересно' : 'You might also like';

// Подготовка данных для Schema.org (надёжный массив → json_encode)
$schemaImages = array_map(function($img) {
    return UPLOAD_URL . $img['filename'];
}, $images);
if (empty($schemaImages)) {
    $schemaImages = [BASE_URL . 'img/default-og.jpg'];
}

$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'TouristAttraction',
    'name' => $attraction['title'],
    'description' => $description,
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => $pageUrl
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => __('site_title'),
        // Добавляем логотип только если файл существует (проверяем на сервере)
        'logo' => file_exists($_SERVER['DOCUMENT_ROOT'] . '/img/logo.png') ? [
            '@type' => 'ImageObject',
            'url' => BASE_URL . 'img/logo.png'
        ] : null
    ],
    'image' => $schemaImages,
    'url' => $pageUrl,
    'address' => [
        '@type' => 'PostalAddress',
        'addressLocality' => 'Омск',
        'addressCountry' => 'RU'
    ],
    'datePublished' => date('c', strtotime($attraction['created_at'] ?? 'now'))
];

if (!empty($attraction['latitude']) && !empty($attraction['longitude'])) {
    $schema['geo'] = [
        '@type' => 'GeoCoordinates',
        'latitude' => $attraction['latitude'],
        'longitude' => $attraction['longitude']
    ];
}
if (empty($schema['publisher']['logo'])) {
    unset($schema['publisher']['logo']);
}
if (!empty($attraction['category_name']) && !empty($attraction['category_id'])) {
    $breadcrumbItems[] = [
        '@type' => 'ListItem',
        'position' => $position++,
        'name' => $attraction['category_name'],
        'item' => BASE_URL . '?category=' . $attraction['category_id']
    ];
}
// BreadcrumbList Schema.org (совпадает с визуальными крошками)


$breadcrumbItems = [
    [
        '@type' => 'ListItem',
        'position' => 1,
        'name' => __('home'),
        'item' => BASE_URL
    ]
];
$position = 2;
if (!empty($attraction['category_name']) && !empty($attraction['category_id'])) {
    $breadcrumbItems[] = [
        '@type' => 'ListItem',
        'position' => $position++,
        'name' => $attraction['category_name'],
        'item' => BASE_URL . '?category=' . $attraction['category_id']
    ];
}
$breadcrumbItems[] = [
    '@type' => 'ListItem',
    'position' => $position,
    'name' => $attraction['title'],
    'item' => $pageUrl
];
$breadcrumbSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $breadcrumbItems
];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang ?? 'ru', ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($fullTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($description) ?>">
    <!-- Open Graph (тип website для карточки места) -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($fullTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($description) ?>">
    <meta property="og:image" content="<?= $primaryImage ?>">
    <meta property="og:url" content="<?= $pageUrl ?>">
    <meta property="og:site_name" content="<?= __('site_title') ?>">
    <?php if (!empty($attraction['latitude']) && !empty($attraction['longitude'])): ?>
    <meta property="place:location:latitude" content="<?= $attraction['latitude'] ?>">
    <meta property="place:location:longitude" content="<?= $attraction['longitude'] ?>">
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($fullTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($description) ?>">
    <meta name="twitter:image" content="<?= $primaryImage ?>">
    <meta name="robots" content="index,follow">

    <!-- Schema.org JSON-LD (валидный) -->
    <script type="application/ld+json">
    <?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
    </script>
    <script type="application/ld+json">
    <?= json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
    </script>

    <!-- Каноническая ссылка -->
    <link rel="canonical" href="<?= $pageUrl ?>">

    <!-- hreflang для языковых версий (полные URL, параметр ?lang=) -->
    <link rel="alternate" hreflang="ru" href="<?= $pageUrl ?>?lang=ru">
    <link rel="alternate" hreflang="en" href="<?= $pageUrl ?>?lang=en">
    <link rel="alternate" hreflang="x-default" href="<?= $pageUrl ?>">

    <!-- Шрифты -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Стили -->
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/hlebnikrosh.css">

    <!-- GLightbox -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
        <a href="<?= BASE_URL ?>" class="back-link"><?= __('back_to_list') ?></a>

        <?php if (isset($_SESSION['admin_logged_in'])): ?>
            <div class="admin-edit-link">
                <a href="<?= BASE_URL ?>admin/attraction_edit.php?id=<?= $attraction['id'] ?>" class="btn-edit">
                    ✎ <?= $editText ?>
                </a>
            </div>
        <?php endif; ?>

        <article class="attraction-detail">
            <h1><?= $title ?></h1>

            <!-- Хлебные крошки (визуальные) -->
            <div class="breadcrumbs">
                <a href="<?= BASE_URL ?>"><?= __('home') ?></a> /
                <?php if (!empty($attraction['category_name'])): ?>
                    <a href="<?= BASE_URL ?>?category=<?= $attraction['category_id'] ?>"><?= htmlspecialchars($attraction['category_name']) ?></a> /
                <?php endif; ?>
                <span><?= htmlspecialchars($attraction['title']) ?></span>
            </div>

            <div class="attraction-meta">
                <span>📍 Омск, Россия</span>
                <?php if ($createdDate): ?>
                    <span>🕒 <?= $createdDate ?></span>
                <?php endif; ?>
                <span class="reading-time">⏱️ <?= $readingTimeText ?></span>
                <span>👁️ <?= $attraction['views_count'] ?? 0 ?></span>
            </div>

            <!-- Кнопки "Поделиться" -->
            <div class="share-buttons">
                <span><?= $shareText ?>:</span>
                <a href="https://vk.com/share.php?url=<?= urlencode($pageUrl) ?>&title=<?= urlencode($fullTitle) ?>" target="_blank" class="share-btn vk" rel="noopener">VK</a>
                <a href="https://t.me/share/url?url=<?= urlencode($pageUrl) ?>&text=<?= urlencode($fullTitle) ?>" target="_blank" class="share-btn telegram" rel="noopener">Telegram</a>
                <a href="https://api.whatsapp.com/send?text=<?= urlencode($fullTitle . ' ' . $pageUrl) ?>" target="_blank" class="share-btn whatsapp" rel="noopener">WhatsApp</a>
            </div>

            <!-- Оглавление -->
            <?php if (!empty($toc)): ?>
                <div class="table-of-contents">
                    <h4><?= $lang == 'ru' ? 'Содержание' : 'Contents' ?></h4>
                    <ul>
                        <?php foreach ($toc as $item): ?>
                            <li><a href="#<?= $item['id'] ?>"><?= htmlspecialchars($item['title']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="description">
                <?php
                $allowed_tags = '<h1><h2><h3><h4><h5><h6><p><br><strong><b><em><i><u><ul><ol><li><blockquote>';
                $desc = $attraction['full_description'] ?? $attraction['short_description'];
                if (strip_tags($desc) === $desc) {
                    echo nl2br(htmlspecialchars($desc));
                } else {
                    $desc = addAnchorsToHeadings($desc);
                    echo strip_tags($desc, $allowed_tags);
                }
                ?>
            </div>

            <!-- Галерея -->
            <?php if (!empty($images)): ?>
                <h3 style="margin-top: 3rem; margin-bottom: 1.8rem; font-family: 'Cormorant Garamond', serif;">
                    <?= __('gallery') ?>
                </h3>
                <div class="gallery-grid" id="gallery">
                    <?php $isFirst = true; foreach ($images as $img): ?>
                        <a href="<?= UPLOAD_URL . htmlspecialchars($img['filename']) ?>"
                            class="gallery-item glightbox"
                            data-gallery="gallery"
                            data-title="<?= htmlspecialchars($img['alt_text'] ?? $attraction['title']) ?>">
                            <img src="<?= UPLOAD_URL . htmlspecialchars($img['filename']) ?>"
                                alt="<?= htmlspecialchars($img['alt_text'] ?? $attraction['title']) ?>"
                                loading="<?= $isFirst ? 'eager' : 'lazy' ?>"
                                <?= $isFirst ? 'fetchpriority="high"' : '' ?>>
                        </a>
                    <?php $isFirst = false; endforeach; ?>
                </div>
            <?php elseif (isset($_SESSION['admin_logged_in'])): ?>
                <div class="no-images-message">
                    <p>📷 <?= $lang == 'ru' ? 'Нет изображений.' : 'No images.' ?>
                       <a href="<?= BASE_URL ?>admin/attraction_edit.php?id=<?= $attraction['id'] ?>">
                           <?= $lang == 'ru' ? 'Добавить в админке' : 'Add in admin' ?>
                       </a>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Карта -->
            <?php if (!empty($attraction['latitude']) && !empty($attraction['longitude'])):
                $lat = (float)$attraction['latitude'];
                $lng = (float)$attraction['longitude'];
                $coord_valid = ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180);
                if ($coord_valid):
            ?>
                <div class="attraction-map">
                    <h3><?= $lang == 'ru' ? 'Местоположение' : 'Location' ?></h3>
                    <div id="attractionMap" style="height: 400px; border-radius: 20px; box-shadow: var(--shadow);"></div>
                    <a href="https://www.openstreetmap.org/directions?from=&to=<?= $lat ?>%2C<?= $lng ?>"
                       target="_blank" class="btn map-directions-btn">
                       <?= $lang == 'ru' ? 'Построить маршрут' : 'Get directions' ?>
                    </a>
                </div>
            <?php
                else:
                    if (isset($_SESSION['admin_logged_in'])) {
                        echo '<p style="color:red;">Ошибка: координаты вне диапазона (широта: '.$lat.', долгота: '.$lng.')</p>';
                    }
                endif;
            endif;
            ?>
        </article>

        <!-- Похожие достопримечательности -->
        <?php if (!empty($relatedAttractions)): ?>
            <section class="related-attractions">
                <h2 class="related-title"><?= $relatedTitle ?></h2>
                <div class="related-grid">
                    <?php foreach ($relatedAttractions as $related): ?>
                        <article class="attraction-card animate-on-scroll">
                            <?php if (!empty($related['primary_image'])): ?>
                                <img src="<?= UPLOAD_URL . htmlspecialchars($related['primary_image']) ?>"
                                     class="card-img" alt="<?= htmlspecialchars($related['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="card-img placeholder-img"></div>
                            <?php endif; ?>
                            <div class="card-content">
                                <h3 class="card-title"><?= htmlspecialchars($related['title']) ?></h3>
                                <p class="card-description"><?= htmlspecialchars($related['short_description']) ?></p>
                                <a href="<?= BASE_URL . rawurlencode($related['slug']) ?>" class="btn">
                                    <?= __('read_more') ?>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
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

    <!-- GLightbox JS -->
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>

    <script>
        // Лайтбокс
        const lightbox = GLightbox({
            selector: '.glightbox',
            touchNavigation: true,
            loop: true,
            autoplayVideos: false
        });

        // Анимации появления
        const animatedElements = document.querySelectorAll('.animate-on-scroll');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
        animatedElements.forEach(el => observer.observe(el));

        // Плавная прокрутка оглавления
        document.querySelectorAll('.table-of-contents a').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    history.pushState(null, null, `#${targetId}`);
                }
            });
        });

        // Тема и размер шрифта
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
        })();
    </script>

    <!-- Карта -->
    <?php if (!empty($attraction['latitude']) && !empty($attraction['longitude'])): ?>
    <script>
    (function() {
        const mapContainer = document.getElementById('attractionMap');
        if (!mapContainer) return;

        const lat = <?= $lat ?? 54.9833 ?>;
        const lng = <?= $lng ?? 73.3667 ?>;
        const title = <?= json_encode($attraction['title'], JSON_UNESCAPED_UNICODE) ?>;

        if (mapContainer._leaflet_id) {
            const parent = mapContainer.parentNode;
            const newContainer = document.createElement('div');
            newContainer.id = 'attractionMap';
            newContainer.style.height = '400px';
            newContainer.style.borderRadius = '20px';
            newContainer.style.boxShadow = 'var(--shadow)';
            parent.replaceChild(newContainer, mapContainer);
            mapContainer = newContainer;
        }

        const map = L.map(mapContainer).setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        L.marker([lat, lng]).addTo(map).bindPopup(title).openPopup();
    })();
    </script>
    <?php endif; ?>
</body>
</html>
