<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
if (!isset($slug)) {
    $slug = $_GET['slug'] ?? ''; // или редирект, если нужно
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($attraction) || !is_array($attraction)) {
    header('Location: ' . BASE_URL);
    exit;
}

// Счётчик просмотров
if (!isset($_SESSION['viewed_' . $attraction['id']])) {
    $pdo->prepare("UPDATE attractions SET views_count = views_count + 1 WHERE id = ?")
        ->execute([$attraction['id']]);
    $_SESSION['viewed_' . $attraction['id']] = true;
    $attraction['views_count'] = ($attraction['views_count'] ?? 0) + 1;
}

$images = getImages($attraction['id']);
$primaryImage = !empty($images) ? UPLOAD_URL . $images[0]['filename'] : BASE_URL . 'img/default-og.jpg';

// Мета-описание
$description = $attraction['short_description'] ?? '';
if (empty($description) && !empty($attraction['full_description'])) {
    $plainText = strip_tags($attraction['full_description']);
    $description = mb_substr($plainText, 0, 157);
    if (mb_strlen($plainText) > 157) {
        $lastSpace = mb_strrpos($description, ' ');
        if ($lastSpace !== false) $description = mb_substr($description, 0, $lastSpace);
        $description .= '…';
    }
    $description = $description ?: 'Достопримечательность Омска: фото, описание, карта и маршрут.';
}

$title = htmlspecialchars($attraction['title'], ENT_QUOTES, 'UTF-8');
$baseTitle = $title . ' — достопримечательность Омска';
$fullTitle = mb_strlen($baseTitle) > 60 ? $title . ' — Омск' : $baseTitle . ' | ' . __('site_title');

// Канонический URL (основная страница)
$canonicalUrl = rtrim(BASE_URL, '/') . '/' . rawurlencode($slug);
// Языковой URL (для указания в разметке)
$langUrl = $canonicalUrl . '?lang=' . ($lang ?? 'ru');

$relatedAttractions = getRelatedAttractions($attraction['id'], 3);
$fullText = strip_tags($attraction['full_description'] ?? $attraction['short_description']);
$readingTime = !empty($fullText) ? ceil(preg_match_all('/\p{L}+/u', $fullText ?? '', $m) / 200) : 1;
$readingTimeText = $lang == 'ru' ? "Время чтения: ~{$readingTime} мин" : "Reading time: ~{$readingTime} min";
$toc = generateTOC($attraction['full_description'] ?? '');
$createdDate = !empty($attraction['created_at']) ? formatDate($attraction['created_at'], $lang) : '';

$shareText = $lang == 'ru' ? 'Поделиться' : 'Share';
$editText = $lang == 'ru' ? 'Редактировать' : 'Edit';
$relatedTitle = $lang == 'ru' ? 'Вам также может быть интересно' : 'You might also like';

// Schema.org
$schemaImages = array_map(function($img) { return UPLOAD_URL . $img['filename']; }, $images);
if (empty($schemaImages)) $schemaImages = [BASE_URL . 'img/default-og.jpg'];

$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'TouristAttraction',
    'name' => $attraction['title'],
    'description' => $description,
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonicalUrl],
    'publisher' => [
        '@type' => 'Organization',
        'name' => __('site_title'),
        'logo' => file_exists($_SERVER['DOCUMENT_ROOT'] . '/img/logo.png') ? ['@type' => 'ImageObject', 'url' => BASE_URL . 'img/logo.png'] : null
    ],
    'image' => $schemaImages,
    'url' => $canonicalUrl,
    'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Омск', 'addressCountry' => 'RU'],
    'datePublished' => date('c', strtotime($attraction['created_at'] ?? 'now'))
];
if (!empty($attraction['latitude']) && !empty($attraction['longitude'])) {
    $schema['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => $attraction['latitude'], 'longitude' => $attraction['longitude']];
}
if (empty($schema['publisher']['logo'])) unset($schema['publisher']['logo']);

// BreadcrumbList
$breadcrumbItems = [['@type' => 'ListItem', 'position' => 1, 'name' => __('home'), 'item' => BASE_URL]];
$position = 2;
if (!empty($attraction['category_name']) && !empty($attraction['category_id'])) {
    $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => $position++, 'name' => $attraction['category_name'], 'item' => BASE_URL . '?category=' . $attraction['category_id']];
}
$breadcrumbItems[] = ['@type' => 'ListItem', 'position' => $position, 'name' => $attraction['title'], 'item' => $canonicalUrl];
$breadcrumbSchema = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $breadcrumbItems];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang ?? 'ru', ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($fullTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($description) ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($fullTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($description) ?>">
    <meta property="og:image" content="<?= $primaryImage ?>">
    <meta property="og:url" content="<?= $canonicalUrl ?>">
    <meta property="og:site_name" content="<?= __('site_title') ?>">
    <?php if (!empty($attraction['latitude']) && !empty($attraction['longitude'])): ?>
    <meta property="place:location:latitude" content="<?= $attraction['latitude'] ?>">
    <meta property="place:location:longitude" content="<?= $attraction['longitude'] ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($fullTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($description) ?>">
    <meta name="twitter:image" content="<?= $primaryImage ?>">
    <meta name="robots" content="index,follow">
    <script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?></script>
    <script type="application/ld+json"><?= json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?></script>

    <!-- Каноническая ссылка (основная страница без параметров) -->
    <link rel="canonical" href="<?= $canonicalUrl ?>">
    <!-- Языковые версии -->
    <link rel="alternate" hreflang="ru" href="<?= $canonicalUrl ?>?lang=ru">
    <link rel="alternate" hreflang="en" href="<?= $canonicalUrl ?>?lang=en">
    <link rel="alternate" hreflang="x-default" href="<?= $canonicalUrl ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/hlebnikrosh.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/audio.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <?php include 'includes/metrica.php'; ?>
</head>
<body>
    <?php
    $slugForLang = $slug;
    include 'components/header.php';
    ?>
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
            <div class="breadcrumbs">
                <a href="<?= BASE_URL ?>"><?= __('home') ?></a> /
                <?php if (!empty($attraction['category_name'])): ?>
                    <a href="<?= BASE_URL ?>?category=<?= $attraction['category_id'] ?>"><?= htmlspecialchars($attraction['category_name']) ?></a> /
                <?php endif; ?>
                <span><?= htmlspecialchars($attraction['title']) ?></span>
            </div>
            <div class="attraction-meta">
                <span>📍 Омск, Россия</span>
                <?php if ($createdDate): ?><span>🕒 <?= $createdDate ?></span><?php endif; ?>
                <span class="reading-time">⏱️ <?= $readingTimeText ?></span>
                <span>👁️ <?= $attraction['views_count'] ?? 0 ?></span>
            </div>
            <div class="share-buttons">
                <span><?= $shareText ?>:</span>
                <a href="https://vk.com/share.php?url=<?= urlencode($canonicalUrl) ?>&title=<?= urlencode($fullTitle) ?>" target="_blank" class="share-btn vk" rel="noopener">VK</a>
                <a href="https://t.me/share/url?url=<?= urlencode($canonicalUrl) ?>&text=<?= urlencode($fullTitle) ?>" target="_blank" class="share-btn telegram" rel="noopener">Telegram</a>
                <a href="https://api.whatsapp.com/send?text=<?= urlencode($fullTitle . ' ' . $canonicalUrl) ?>" target="_blank" class="share-btn whatsapp" rel="noopener">WhatsApp</a>
            </div>
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
                echo strip_tags($desc) === $desc ? nl2br(htmlspecialchars($desc)) : addAnchorsToHeadings($desc);
                ?>
            </div>

            <!-- 🎧 Аудиогид -->
            <?php if (!empty($attraction['audio_file'])): ?>
                <div class="audio-guide">
                    <h3><?= $lang == 'ru' ? 'Аудиогид' : 'Audio guide' ?></h3>
                    <div class="audio-player">
                        <button class="audio-play-btn" id="playBtn">
                            <span class="play-icon">▶</span>
                        </button>
                        <div class="audio-progress-container" id="progressContainer">
                            <div class="audio-progress-bar" id="progressBar" style="width: 0%"></div>
                        </div>
                        <span class="audio-time" id="timeDisplay">00:00 / 00:00</span>
                    </div>
                    <audio id="audioElement" preload="none">
                        <source src="<?= BASE_URL ?>uploads/audio/<?= htmlspecialchars($attraction['audio_file']) ?>" type="audio/mpeg">
                        Ваш браузер не поддерживает аудио.
                    </audio>
                </div>
            <?php endif; ?>

            <!-- Галерея -->
            <?php if (!empty($images)): ?>
                <h3 style="margin-top: 3rem; margin-bottom: 1.8rem; font-family: 'Cormorant Garamond', serif;"><?= __('gallery') ?></h3>
                <div class="gallery-grid" id="gallery">
                    <?php $isFirst = true; foreach ($images as $img): ?>
                        <a href="<?= UPLOAD_URL . htmlspecialchars($img['filename']) ?>" class="gallery-item glightbox" data-gallery="gallery" data-title="<?= htmlspecialchars($img['alt_text'] ?? $attraction['title']) ?>">
                            <img src="<?= UPLOAD_URL . htmlspecialchars($img['filename']) ?>" alt="<?= htmlspecialchars($img['alt_text'] ?? $attraction['title']) ?>" loading="<?= $isFirst ? 'eager' : 'lazy' ?>" <?= $isFirst ? 'fetchpriority="high"' : '' ?>>
                        </a>
                    <?php $isFirst = false; endforeach; ?>
                </div>
            <?php elseif (isset($_SESSION['admin_logged_in'])): ?>
                <div class="no-images-message"><p>📷 <?= $lang == 'ru' ? 'Нет изображений.' : 'No images.' ?> <a href="<?= BASE_URL ?>admin/attraction_edit.php?id=<?= $attraction['id'] ?>"><?= $lang == 'ru' ? 'Добавить в админке' : 'Add in admin' ?></a></p></div>
            <?php endif; ?>

            <!-- Карта -->
            <?php if (!empty($attraction['latitude']) && !empty($attraction['longitude'])):
                $lat = (float)$attraction['latitude']; $lng = (float)$attraction['longitude'];
                if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180): ?>
                <div class="attraction-map">
                    <h3><?= $lang == 'ru' ? 'Местоположение' : 'Location' ?></h3>
                    <div id="attractionMap" style="height: 400px; border-radius: 20px; box-shadow: var(--shadow);"></div>
                    <a href="https://www.openstreetmap.org/directions?from=&to=<?= $lat ?>%2C<?= $lng ?>" target="_blank" class="btn map-directions-btn"><?= $lang == 'ru' ? 'Построить маршрут' : 'Get directions' ?></a>
                </div>
                <?php else: if (isset($_SESSION['admin_logged_in'])) echo '<p style="color:red;">Ошибка координат</p>'; endif; ?>
            <?php endif; ?>
        </article>

        <!-- Похожие -->
        <?php if (!empty($relatedAttractions)): ?>
            <section class="related-attractions"><h2 class="related-title"><?= $relatedTitle ?></h2><div class="related-grid"><?php foreach ($relatedAttractions as $related): ?>
                <article class="attraction-card animate-on-scroll">
                    <?php if (!empty($related['primary_image'])): ?><img src="<?= UPLOAD_URL . htmlspecialchars($related['primary_image']) ?>" class="card-img" alt="<?= htmlspecialchars($related['title']) ?>" loading="lazy"><?php else: ?><div class="card-img placeholder-img"></div><?php endif; ?>
                    <div class="card-content"><h3 class="card-title"><?= htmlspecialchars($related['title']) ?></h3><p class="card-description"><?= htmlspecialchars($related['short_description']) ?></p><a href="<?= BASE_URL . rawurlencode($related['slug']) ?>" class="btn"><?= __('read_more') ?></a></div>
                </article>
            <?php endforeach; ?></div></section>
        <?php endif; ?>
    </main>
    <footer class="site-footer">
        <div class="container"><p>© <?= date('Y') ?> Омск. Историческое наследие.</p><p><a href="<?= BASE_URL ?>privacy">Политика конфиденциальности</a> | <a href="<?= BASE_URL ?>terms">Пользовательское соглашение</a></p></div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
    <script>
        const lightbox = GLightbox({ selector: '.glightbox', touchNavigation: true, loop: true, autoplayVideos: false });
        const animatedEls = document.querySelectorAll('.animate-on-scroll');
        const observer = new IntersectionObserver((entries) => { entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); observer.unobserve(e.target); } }); }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
        animatedEls.forEach(el => observer.observe(el));
        document.querySelectorAll('.table-of-contents a').forEach(a => a.addEventListener('click', function(e) { e.preventDefault(); const id = this.getAttribute('href').substring(1); const el = document.getElementById(id); if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'start' }); history.pushState(null, null, `#${id}`); } }));
        (function() {
            const body = document.body;
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') body.classList.add('dark-theme');
            document.querySelectorAll('.theme-toggle').forEach(btn => btn.addEventListener('click', () => { const theme = btn.dataset.theme; body.classList.toggle('dark-theme', theme === 'dark'); localStorage.setItem('theme', theme === 'dark' ? 'dark' : 'light'); }));
            const html = document.documentElement;
            let fontSizeLevel = parseInt(localStorage.getItem('fontSizeLevel')) || 0;
            function applyFontSize() { html.classList.remove('font-size-large', 'font-size-extra-large'); if (fontSizeLevel === 1) html.classList.add('font-size-large'); if (fontSizeLevel === 2) html.classList.add('font-size-extra-large'); }
            applyFontSize();
            document.querySelectorAll('.font-size-btn').forEach(btn => btn.addEventListener('click', () => { if (btn.dataset.size === 'increase') fontSizeLevel = Math.min(fontSizeLevel + 1, 2); else if (btn.dataset.size === 'reset') fontSizeLevel = 0; applyFontSize(); localStorage.setItem('fontSizeLevel', fontSizeLevel); }));
        })();
    </script>
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
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
            L.marker([lat, lng]).addTo(map).bindPopup(title).openPopup();
        })();
    </script>
    <?php endif; ?>

    <!-- СКРИПТ ПЛЕЕРА -->
    <script>
        (function() {
            const audio = document.getElementById('audioElement');
            if (!audio) return;
            const playBtn = document.getElementById('playBtn');
            const progressBar = document.getElementById('progressBar');
            const progressContainer = document.getElementById('progressContainer');
            const timeDisplay = document.getElementById('timeDisplay');

            function formatTime(seconds) {
                const mins = Math.floor(seconds / 60);
                const secs = Math.floor(seconds % 60);
                return `${mins.toString().padStart(2,'0')}:${secs.toString().padStart(2,'0')}`;
            }
            audio.addEventListener('loadedmetadata', () => { timeDisplay.textContent = `00:00 / ${formatTime(audio.duration)}`; });
            audio.addEventListener('timeupdate', () => { const pct = (audio.currentTime / audio.duration) * 100; progressBar.style.width = pct + '%'; timeDisplay.textContent = `${formatTime(audio.currentTime)} / ${formatTime(audio.duration)}`; });
            playBtn.addEventListener('click', () => { if (audio.paused) { audio.play(); playBtn.querySelector('.play-icon').textContent = '⏸'; } else { audio.pause(); playBtn.querySelector('.play-icon').textContent = '▶'; } });
            progressContainer.addEventListener('click', (e) => { const rect = progressContainer.getBoundingClientRect(); const clickX = e.clientX - rect.left; const pct = clickX / rect.width; audio.currentTime = pct * audio.duration; });
            audio.addEventListener('ended', () => { playBtn.querySelector('.play-icon').textContent = '▶'; });
        })();
    </script>
    <!-- ЦЕЛЬ МЕТРИКИ: просмотр достопримечательности -->
    <script>
        window.addEventListener('load', function() {
            if (typeof ym !== 'undefined' && typeof ym === 'function') {
                ym(108573048, 'reachGoal', 'attraction_view');
            }
        });
    </script>
</body>
</html>
