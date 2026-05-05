<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
if (!isset($slug)) {
    $slug = $_GET['slug'] ?? ''; // или редирект, если нужно
}
if (!isset($route)) {
    header('Location: ' . BASE_URL . 'kuda-shodit-v-omske');
    exit;
}

$stops = getRouteStops($route['id']);

$pageTitle = $route['title'];
$pageDescription = $route['short_description'] ?? '';
$ogImage = BASE_URL . 'uploads/hero-bg.jpg';
$canonicalUrl = BASE_URL . 'kuda-shodit-v-omske/' . urlencode($route['slug']);

// Координаты для карты
$routeCoords = [];
$stopCoordinates = []; // для кнопок "Построить маршрут"
foreach ($stops as $index => $stop) {
    if ($stop['attraction_id']) {
        $stmt = $pdo->prepare("SELECT latitude, longitude FROM attractions WHERE id = ?");
        $stmt->execute([$stop['attraction_id']]);
        $coords = $stmt->fetch();
        if ($coords && $coords['latitude'] && $coords['longitude']) {
            $lat = (float)$coords['latitude'];
            $lng = (float)$coords['longitude'];
            if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                $routeCoords[] = [$lat, $lng];
                $stopCoordinates[$index] = ['lat' => $lat, 'lng' => $lng];
            }
        }
    }
}
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
    <link rel="stylesheet" href="<?= BASE_URL ?>css/hlebnikrosh.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/faq.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/audio-routes.css">
    <style>
        .route-hero {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow);
        }

        .route-stats {
            display: flex;
            gap: 1.5rem;
            margin: 1rem 0;
            font-size: 1.1rem;
        }

        .stop-card {
            display: flex;
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
        }

        .stop-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary);
            min-width: 3.5rem;
        }

        .stop-content {
            flex: 1;
        }

        .stop-meta {
            color: var(--muted);
            margin-top: 0.5rem;
            font-style: italic;
        }

        .btn-detail {
            margin-top: 1rem;
            display: inline-block;
        }

        .faq-item {
            margin-bottom: 1.5rem;
        }

        .faq-question {
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 0.3rem;
        }

        .stop-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 1.5rem;
            align-items: start;
            margin-top: 0.5rem;
        }

        .stop-image img {
            width: 100%;
            height: auto;
            border-radius: 12px;
            object-fit: cover;
        }

        .btn-route {

            padding: 0.5rem 0.9rem;
            font-size: 0.8rem;
            border-radius: 20px;
            color: white;
            text-decoration: none;
        }

        .btn-route:hover {
            background: var(--primary-dark);
            color: white;
        }

        @media (max-width: 768px) {
            .stop-layout {
                grid-template-columns: 1fr;
            }

            .stop-card {
                flex-direction: column;
            }

            .route-hero {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <?php
    $slugForLang = $slug;
    include 'components/header.php';
    ?>

    <main class="container">
        <div class="breadcrumbs">
            <a href="<?= BASE_URL ?>"><?= __('home') ?></a> /
            <a href="<?= BASE_URL ?>kuda-shodit-v-omske"><?= $lang == 'ru' ? 'Маршруты' : 'Routes' ?></a> /
            <span><?= htmlspecialchars($route['title']) ?></span>
        </div>

        <div class="route-hero">
            <h1><?= htmlspecialchars($route['title']) ?></h1>
            <p><?= htmlspecialchars($route['short_description']) ?></p>
            <?php if (!empty($route['full_description'])): ?>
                <div class="route-description">
                    <?= nl2br(htmlspecialchars($route['full_description'])) ?>
                </div>
            <?php endif; ?>
            <div class="route-stats">
                <?php if ($route['distance']): ?><span>🚶 <?= $route['distance'] ?></span><?php endif; ?>
                <?php if ($route['duration']): ?><span>⏱ <?= $route['duration'] ?></span><?php endif; ?>
                <?php if ($route['stops_count']): ?><span>📍 <?= $route['stops_count'] ?> остановок</span><?php endif; ?>
            </div>
            <div class="share-route">
                <span class="share-label"><?= $lang == 'ru' ? 'Поделиться:' : 'Share:' ?></span>
                <div class="share-buttons">
                    <a href="https://vk.com/share.php?url=<?= urlencode($canonicalUrl) ?>&title=<?= urlencode($pageTitle) ?>" target="_blank" class="btn btn-sm vk" rel="noopener">VK</a>
                    <a href="https://t.me/share/url?url=<?= urlencode($canonicalUrl) ?>&text=<?= urlencode($pageTitle) ?>" target="_blank" class="btn btn-sm telegram" rel="noopener">Telegram</a>
                    <a href="https://api.whatsapp.com/send?text=<?= urlencode($pageTitle . ' ' . $canonicalUrl) ?>" target="_blank" class="btn btn-sm whatsapp" rel="noopener">WhatsApp</a>
                </div>
            </div>
            <div id="yandexMap" style="height: 400px; border-radius: 16px;"></div>
        </div>

        <?php if (!empty($route['map_image'])): ?>
            <div style="margin: 2rem 0; text-align: center;">
                <img src="<?= BASE_URL ?>uploads/routes/<?= htmlspecialchars($route['map_image']) ?>"
                    alt="Схема маршрута"
                    style="max-width: 100%; border-radius: 16px; box-shadow: var(--shadow);"
                    class="clickable-image">
            </div>
        <?php endif; ?>

        <h2>📍 <?= $lang == 'ru' ? 'Маршрут по шагам' : 'Step-by-step route' ?></h2>
        <div class="stops-list">
            <?php foreach ($stops as $index => $stop): ?>
                <div class="stop-card">
                    <div class="stop-number"><?= $index + 1 ?></div>
                    <div class="stop-content">
                        <h3><?= htmlspecialchars($stop['attraction_title'] ?? $stop['custom_title']) ?></h3>

                        <div class="stop-layout">
                            <?php if ($stop['attraction_image'] || $stop['image']): ?>
                                <div class="stop-image">
                                    <?php if ($stop['attraction_image']): ?>
                                        <img src="<?= UPLOAD_URL . htmlspecialchars($stop['attraction_image']) ?>"
                                            class="clickable-image"
                                            alt="<?= htmlspecialchars($stop['attraction_title']) ?>"
                                            loading="lazy">
                                    <?php elseif ($stop['image']): ?>
                                        <img src="<?= BASE_URL ?>uploads/routes/<?= htmlspecialchars($stop['image']) ?>"
                                            class="clickable-image"
                                            alt="<?= htmlspecialchars($stop['custom_title'] ?? '') ?>"
                                            loading="lazy">
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="stop-description">
                                <?php
                                $description = $stop['custom_description']
                                    ?? $stop['attraction_short_description']
                                    ?? ($lang == 'ru' ? 'Информация появится позже' : 'Information coming soon');
                                ?>
                                <p><?= htmlspecialchars($description) ?></p>
                                <?php if ($stop['walk_time_to_next']): ?>
                                    <div class="stop-meta">↓ <?= $stop['walk_time_to_next'] ?> до следующей точки</div>
                                <?php endif; ?>
                                <?php if ($stop['attraction_slug']): ?>
                                    <div class="btn-detail">
                                        <a href="<?= BASE_URL . urlencode($stop['attraction_slug']) ?>" class="btn btn-sm">
                                            <?= $lang == 'ru' ? 'Подробнее о месте' : 'More details' ?>
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($stopCoordinates[$index])): ?>
                                    <div class="btn-detail">
                                        <a href="#" class="btn btn-route route-btn" data-lat="<?= $stopCoordinates[$index]['lat'] ?>" data-lng="<?= $stopCoordinates[$index]['lng'] ?>">
                                            <?= $lang == 'ru' ? 'Построить маршрут' : 'Build route' ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <?php
                                $stopAudio = null;
                                if ($stop['attraction_id']) {
                                    $stmt = $pdo->prepare("SELECT audio_file FROM attractions WHERE id = ?");
                                    $stmt->execute([$stop['attraction_id']]);
                                    $stopAudio = $stmt->fetchColumn();
                                }
                                ?>

                                <?php if ($stopAudio): ?>
                                    <div class="audio-player">
                                        <div class="audio-controls">
                                            <!-- Rewind -->
                                            <button class="audio-btn rewind-btn" data-action="rewind" aria-label="Назад 10с">
                                                <svg fill="#b34e3a" width="800px" height="800px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg">
                                                    <title>rewind</title>
                                                    <path d="M15.343 16l5.657 5.657-2.828 2.828-8.486-8.485 8.485-8.485 2.829 2.828-5.657 5.657z"></path>
                                                </svg>
                                            </button>

                                            <!-- Play/Pause -->
                                            <button class="audio-btn route-audio-btn" data-action="playpause" data-audio-src="<?= BASE_URL ?>uploads/audio/<?= htmlspecialchars($stopAudio) ?>" data-playing="false">
                                                <svg class="icon icon-play" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path fill="currentColor" d="M8 5v14l11-7z" />
                                                </svg>

                                                <span class="btn-text"><?= $lang == 'ru' ? 'Слушать' : 'Listen' ?></span>
                                            </button>

                                            <!-- Forward -->
                                            <button class="audio-btn forward-btn" data-action="forward" aria-label="Вперёд 10с">
                                                <svg fill="#b34e3a" width="800px" height="800px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg">
                                                    <title>forward</title>
                                                    <path d="M22.314 16l-8.485 8.485-2.829-2.828 5.657-5.657-5.657-5.657 2.828-2.828 8.486 8.485z"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="audio-progress">
                                            <div class="progress-bar">
                                                <div class="progress-fill"></div>
                                                <div class="progress-thumb"></div>
                                            </div>
                                            <div class="time-display">
                                                <span class="current-time">0:00</span>
                                                <span class="duration">0:00</span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <h2>❓ FAQ</h2>
        <div class="faq">
            <div class="faq-item">
                <div class="faq-question"><?= $lang == 'ru' ? 'Сколько реально занимает маршрут?' : 'How long does the route actually take?' ?></div>
                <div class="faq-answer"><?= $lang == 'ru' ? 'Около 5 часов с учётом остановок на фото и кофе.' : 'About 5 hours including stops for photos and coffee.' ?></div>
            </div>
            <div class="faq-item">
                <div class="faq-question"><?= $lang == 'ru' ? 'Можно ли с детьми/колясками?' : 'Is it suitable for children/strollers?' ?></div>
                <div class="faq-answer"><?= $lang == 'ru' ? 'Да, маршрут полностью пешеходный и доступный.' : 'Yes, the route is entirely pedestrian and accessible.' ?></div>
            </div>
            <div class="faq-item">
                <div class="faq-question"><?= $lang == 'ru' ? 'Есть ли платные входы?' : 'Are there any entrance fees?' ?></div>
                <div class="faq-answer"><?= $lang == 'ru' ? 'Вход в храмы и на набережную бесплатный. Музеи — по желанию.' : 'Entry to churches and the embankment is free. Museums are optional.' ?></div>
            </div>
        </div>

        <div style="margin: 3rem 0;">
            <a href="<?= BASE_URL ?>routes" class="btn">← <?= $lang == 'ru' ? 'Все маршруты' : 'All routes' ?></a>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>© <?= date('Y') ?> Омск. Историческое наследие.</p>
            <p><a href="<?= BASE_URL ?>privacy">Политика конфиденциальности</a> | <a href="<?= BASE_URL ?>terms">Пользовательское соглашение</a></p>
        </div>
    </footer>

    <script src="<?= BASE_URL ?>js/theme.js"></script>
    <script src="https://api-maps.yandex.ru/2.1/?apikey=4784952f-46b9-46fd-a855-21c891be3471&lang=ru_RU" type="text/javascript"></script>
    <script>
        // --- Геолокация ---
        let userLat = null,
            userLng = null;
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(pos) {
                userLat = pos.coords.latitude;
                userLng = pos.coords.longitude;
            });
        }

        // --- Кнопки "Построить маршрут" ---
        document.querySelectorAll('.route-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const lat = this.dataset.lat;
                const lng = this.dataset.lng;
                let url;
                if (userLat && userLng) {
                    url = `https://yandex.ru/maps/?rtext=${userLat},${userLng}~${lat},${lng}&rtt=pd`;
                } else {
                    url = `https://yandex.ru/maps/?rtext=~${lat},${lng}&rtt=pd`;
                }
                window.open(url, '_blank');
            });
        });

        // --- Яндекс.Карта ---
        ymaps.ready(function() {
            var routeCoords = <?= json_encode($routeCoords) ?>;

            var map = new ymaps.Map('yandexMap', {
                center: routeCoords.length ? routeCoords[0] : [54.9833, 73.3667],
                zoom: 14,
                controls: ['zoomControl', 'fullscreenControl']
            });

            function createNumberedPlacemark(coords, number) {
                var ContentLayout = ymaps.templateLayoutFactory.createClass(
                    '<div class="route-number">' + number + '</div>'
                );
                return new ymaps.Placemark(coords, {}, {
                    iconLayout: 'default#imageWithContent',
                    iconImageHref: 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="40" height="40"%3E%3C/svg%3E',
                    iconImageSize: [40, 40],
                    iconContentSize: [40, 40],
                    iconImageOffset: [-19, -5],
                    iconContentLayout: ContentLayout,
                    hasBalloon: false,
                    hasHint: false
                });
            }

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var userCoords = [position.coords.latitude, position.coords.longitude];
                    var userPlacemark = new ymaps.Placemark(userCoords, {
                        balloonContent: 'Вы здесь',
                        iconCaption: 'Я'
                    }, {
                        preset: 'islands#blueIcon',
                        iconColor: '#007bff'
                    });
                    map.geoObjects.add(userPlacemark);
                    if (routeCoords.length > 0) {
                        map.setBounds(
                            ymaps.util.bounds.fromPoints([userCoords].concat(routeCoords)), {
                                checkZoomRange: true
                            }
                        );
                    } else {
                        map.setCenter(userCoords, 15);
                    }
                });
            }

            if (routeCoords.length >= 2) {
                var multiRoute = new ymaps.multiRouter.MultiRoute({
                    referencePoints: routeCoords,
                    params: {
                        routingMode: 'pedestrian'
                    }
                }, {
                    boundsAutoApply: true,
                    viaPointInvisible: true
                });
                map.geoObjects.add(multiRoute);

                multiRoute.model.events.add('requestsuccess', function() {
                    multiRoute.getWayPoints().each(function(wayPoint) {
                        wayPoint.options.set('visible', false);
                    });

                    var activeRoute = multiRoute.getActiveRoute();
                    if (activeRoute) {
                        activeRoute.options.set('activeRouteSelectionByClick', false);
                        activeRoute.options.set('hasBalloon', false);
                        activeRoute.getPaths().each(function(path) {
                            path.options.set('hasBalloon', false);
                            path.getSegments().each(function(seg) {
                                seg.options.set('hasBalloon', false);
                            });
                        });
                    }

                    routeCoords.forEach(function(coords, index) {
                        var number = index + 1;
                        var placemark = createNumberedPlacemark(coords, number);
                        map.geoObjects.add(placemark);
                    });
                });
            } else if (routeCoords.length === 1) {
                var placemark = createNumberedPlacemark(routeCoords[0], 1);
                map.geoObjects.add(placemark);
                map.setCenter(routeCoords[0], 16);
            } else {
                console.warn('Нет валидных координат для построения маршрута');
            }
        });
    </script>
    <script>
        class AudioPlayer {
            constructor() {
                this.players = new Map();
                this.init();
            }

            init() {
                document.querySelectorAll('.audio-player').forEach(container => {
                    const playBtn = container.querySelector('.route-audio-btn');
                    if (!playBtn) return;

                    const audioId = playBtn.dataset.audioSrc;
                    const audio = new Audio(audioId);
                    audio.preload = 'metadata';

                    this.players.set(playBtn, {
                        audio,
                        container,
                        playBtn
                    });

                    this.bindEvents(playBtn);
                });
            }

            bindEvents(playBtn) {
                const player = this.players.get(playBtn);

                // Play/Pause
                playBtn.addEventListener('click', () => this.togglePlay(player));

                // Rewind/Forward
                playBtn.closest('.audio-controls').querySelectorAll('.audio-btn[data-action]').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const action = btn.dataset.action;
                        if (action === 'rewind') {
                            player.audio.currentTime = Math.max(0, player.audio.currentTime - 10);
                        } else if (action === 'forward') {
                            player.audio.currentTime = Math.min(player.audio.duration, player.audio.currentTime + 10);
                        }
                    });
                });

                // Progress bar
                const progressBar = player.container.querySelector('.progress-bar');
                progressBar.addEventListener('click', (e) => {
                    const rect = progressBar.getBoundingClientRect();
                    const percent = (e.clientX - rect.left) / rect.width;
                    player.audio.currentTime = percent * player.audio.duration;
                });

                // Audio events
                player.audio.addEventListener('timeupdate', () => this.updateProgress(player));
                player.audio.addEventListener('loadedmetadata', () => this.updateProgress(player));
                player.audio.addEventListener('ended', () => this.resetPlayer(player));
                player.audio.addEventListener('pause', () => this.updatePlayState(player, false));
                player.audio.addEventListener('play', () => this.updatePlayState(player, true));
            }

            togglePlay(player) {
                if (player.audio.paused) {
                    player.audio.play();
                } else {
                    player.audio.pause();
                }
            }

            updatePlayState(player, isPlaying) {
                player.playBtn.dataset.playing = isPlaying;
                player.playBtn.classList.toggle('playing', isPlaying);
            }

            updateProgress(player) {
                const {
                    audio,
                    container
                } = player;
                const progressFill = container.querySelector('.progress-fill');
                const progressThumb = container.querySelector('.progress-thumb');
                const currentTimeEl = container.querySelector('.current-time');
                const durationEl = container.querySelector('.duration');

                const progress = (audio.currentTime / audio.duration) * 100 || 0;
                progressFill.style.width = progress + '%';

                // Фикс thumb: двигается с fill
                const thumbPos = progress;
                progressThumb.style.left = thumbPos + '%';

                currentTimeEl.textContent = this.formatTime(audio.currentTime);
                durationEl.textContent = this.formatTime(audio.duration);
            }

            resetPlayer(player) {
                this.updatePlayState(player, false);
                player.audio.currentTime = 0;
                this.updateProgress(player);
            }

            formatTime(seconds) {
                const mins = Math.floor(seconds / 60);
                const secs = Math.floor(seconds % 60);
                return `${mins}:${secs.toString().padStart(2, '0')}`;
            }
        }

        // Инициализация
        document.addEventListener('DOMContentLoaded', () => new AudioPlayer());
    </script>

    <!-- Модальное окно для изображений -->
    <div class="modal-overlay" id="imageModal">
        <div class="modal-content">
            <button class="modal-close" id="modalClose">&times;</button>
            <img id="modalImage" src="" alt="">
        </div>
    </div>

    <script>
        (function() {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            const closeBtn = document.getElementById('modalClose');
            document.querySelectorAll('.clickable-image').forEach(img => {
                img.addEventListener('click', function() {
                    modalImg.src = this.src;
                    modalImg.alt = this.alt;
                    modal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                });
            });
            closeBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeModal();
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('active')) closeModal();
            });

            function closeModal() {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        })();
        document.querySelectorAll('.faq-item').forEach(item => {
            item.onclick = () => item.classList.toggle('active');
        });
    </script>
</body>

</html>
