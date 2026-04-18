<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Заголовки для страницы
$pageTitle = $lang == 'ru' ? 'О проекте' : 'About the Project';
$siteName = __('site_title');
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> – <?= $siteName ?></title>
    <meta name="description" content="<?= $lang == 'ru' ? 'Информация о проекте «Омскъ Исторический» – путеводителе по достопримечательностям Омска.' : 'Information about the «Historical Omsk» project – a guide to the landmarks of Omsk.' ?>">

    <!-- Шрифты -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Стили -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/about.css">
    <link rel="stylesheet" href="css/hlebnikrosh.css">
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
                <a href="about.php" class="nav-link active"><?= $lang == 'ru' ? 'О проекте' : 'About' ?></a>
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

    <main class="container">


        <article class="about-page">
            <h1><?= $pageTitle ?></h1>
                    <!-- Хлебные крошки -->
        <div class="breadcrumbs">
            <a href="<?= BASE_URL ?>"><?= __('home') ?></a> /
            <span><?= $pageTitle ?></span>
        </div>
            <div class="about-content">
                <?php if ($lang == 'ru'): ?>
                    <section class="about-section">
                        <h2>Добро пожаловать в «Омскъ Исторический»</h2>
                        <p>Этот сайт создан для всех пользователей, кто хочет открыть для себя удивительный-прекрасный город Омск – его богатую и самую красивую историю, уникальную архитектуру и неповторимую атмосферу которую можем увидеть лишь раз в жизни. Мы собрали самую полную коллекцию лучших достопримечательностей, от древней крепости до современных арт-объектов, которые вам очень сильно понравиться.</p>
                        <p>Наш путеводитель поможет вам составить идеальный маршрут прогулки по городу, узнать малоизвестные факты о знакомых местах и по-новому взглянуть на столицу Сибири.</p>
                    </section>

                    <section class="about-section">
                        <h2>Что вы найдёте на сайте</h2>
                        <ul>
                            <li><strong>Подробные описания</strong> достопримечательностей с историческими справками и фотографиями.</li>
                            <li><strong>Интерактивные карты</strong> с точным местоположением объектов и возможностью построить маршрут.</li>
                            <li><strong>Удобный поиск</strong> с подсказками – даже если вы ошиблись в написании.</li>
                            <li><strong>Фильтр по категориям</strong>: храмы, музеи, памятники, улицы и многое другое.</li>
                            <li><strong>Двуязычный интерфейс</strong> (русский / английский) для гостей из других стран.</li>
                            <li><strong>Тёмная тема и увеличение шрифта</strong> – забота о вашем комфорте.</li>
                        </ul>
                    </section>

                    <section class="about-section">
                        <h2>О разработчике</h2>
                        <p>Проект разработан в рамках дипломной работы и постоянно совершенствуется. Если у вас есть предложения, замечания или вы хотите сообщить о неточности, пожалуйста, свяжитесь с нами.</p>
                    </section>

                    <section class="about-section">
                        <h2>Контакты</h2>
                        <p>Email: <a href="ko3ovka@mail.ru">ko3ovka@mail.ru</a><br>
                        ВКонтакте: <a href="https://vk.com/omskguide" target="_blank" rel="noopener">пока не работает!</a></p>
                        <p>Мы всегда рады обратной связи!</p>
                    </section>
                <?php else: ?>
                    <section class="about-section">
                        <h2>Welcome to "Historical Omsk"</h2>
                        <p>This website is created for everyone who wants to discover the amazing city of Omsk – its rich history, unique architecture and unforgettable atmosphere. We have collected the most complete collection of landmarks, from the ancient fortress to modern art objects.</p>
                        <p>Our guide will help you create the perfect walking route, learn little-known facts about familiar places and take a fresh look at the capital of Siberia.</p>
                    </section>

                    <section class="about-section">
                        <h2>What you will find on the site</h2>
                        <ul>
                            <li><strong>Detailed descriptions</strong> of landmarks with historical information and photos.</li>
                            <li><strong>Interactive maps</strong> with exact locations and the ability to build a route.</li>
                            <li><strong>Convenient search</strong> with suggestions – even if you made a typo.</li>
                            <li><strong>Filter by categories</strong>: churches, museums, monuments, streets and more.</li>
                            <li><strong>Bilingual interface</strong> (Russian / English) for guests from other countries.</li>
                            <li><strong>Dark theme and font size increase</strong> – care for your comfort.</li>
                        </ul>
                    </section>

                    <section class="about-section">
                        <h2>About the Developer</h2>
                        <p>The project was developed as part of a thesis and is constantly being improved. If you have suggestions, comments or want to report an inaccuracy, please contact us.</p>
                    </section>

                    <section class="about-section">
                        <h2>Contacts</h2>
                        <p>Email: <a href="mailto:info@omskguide.ru">info@omskguide.ru</a><br>
                        VKontakte: <a href="https://vk.com/omskguide" target="_blank" rel="noopener">@omskguide</a></p>
                        <p>We always welcome feedback!</p>
                    </section>
                <?php endif; ?>
            </div>
        </article>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>© <?= date('Y') ?> Омск. Историческое наследие.</p>
        </div>
    </footer>

    <script>
        // Тема и размер шрифта (аналогично index.php)
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
</body>
</html>
