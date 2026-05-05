<?php
$currentLang = $lang ?? 'ru';
$otherLang = $currentLang === 'ru' ? 'en' : 'ru';

// БЕРЕМ ТОЛЬКО PATH (без ?lang= и мусора)
$cleanPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// удаляем языковой префикс
$cleanPath = preg_replace('#^(ru|en)(/|$)#', '', $cleanPath);

// защита от index.php
if ($cleanPath === 'index.php') {
    $cleanPath = '';
}
?>
<header class="site-header">
    <div class="container header-inner">
        <a href="/<?= $currentLang ?>/" class="site-title">
            <span>Омскъ</span> Исторический
        </a>
        <div class="nav-links">
            <a href="/<?= $currentLang ?>/" class="nav-link"><?= __('home') ?></a>
            <a href="/<?= $currentLang ?>/kuda-shodit-v-omske" class="nav-link"><?= $currentLang == 'ru' ? 'Маршруты' : 'Routes' ?></a>
            <a href="/<?= $currentLang ?>/about" class="nav-link"><?= $currentLang == 'ru' ? 'О проекте' : 'About' ?></a>
            <a href="/<?= $currentLang ?>/articles" class="nav-link"><?= $currentLang == 'ru' ? 'Статьи' : 'Articles' ?></a>
            <?php if (isset($_SESSION['admin_logged_in'])): ?>
                <a href="/<?= $currentLang ?>/admin/" class="nav-link">Админка</a>
                <a href="/<?= $currentLang ?>/admin/logout.php" class="nav-link">Выход</a>
            <?php endif; ?>
            <div class="lang-switch">
                <a href="/ru<?= $cleanPath ? '/' . $cleanPath : '' ?>"
                    class="lang-btn <?= $currentLang == 'ru' ? 'active' : '' ?>">RU</a>

                <a href="/en<?= $cleanPath ? '/' . $cleanPath : '' ?>"
                    class="lang-btn <?= $currentLang == 'en' ? 'active' : '' ?>">EN</a>
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

<script>
    let lastScrollTop = 0;
    const header = document.querySelector('.site-header');

    window.addEventListener('scroll', function() {
        let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (scrollTop > lastScrollTop) {
            header.classList.add('hidden');
        } else {
            header.classList.remove('hidden');
        }
        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    });
</script>
