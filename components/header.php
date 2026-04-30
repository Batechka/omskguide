<?php
// Ожидается, что переменная $lang определена в вызывающем файле
// Для страниц с ЧПУ (достопримечательности, маршруты) нужно передать $slugForLang
$slugParam = isset($slugForLang) && $slugForLang ? '&slug=' . urlencode($slugForLang) : '';
?>
<header class="site-header">
    <div class="container header-inner">
        <a href="<?= BASE_URL ?>" class="site-title">
            <span>Омскъ</span> Исторический
        </a>
        <div class="nav-links">
            <a href="<?= BASE_URL ?>" class="nav-link"><?= __('home') ?></a>
            <a href="<?= BASE_URL ?>kuda-shodit-v-omske" class="nav-link"><?= $lang == 'ru' ? 'Маршруты' : 'Routes' ?></a>
            <a href="<?= BASE_URL ?>about" class="nav-link"><?= $lang == 'ru' ? 'О проекте' : 'About' ?></a>
            <?php if (isset($_SESSION['admin_logged_in'])): ?>
                <a href="<?= BASE_URL ?>admin/" class="nav-link">Админка</a>
                <a href="<?= BASE_URL ?>admin/logout.php" class="nav-link">Выход</a>
            <?php endif; ?>
            <div class="lang-switch">
                <a href="?lang=ru<?= $slugParam ?>" class="lang-btn <?= $lang=='ru'?'active':'' ?>">RU</a>
                <a href="?lang=en<?= $slugParam ?>" class="lang-btn <?= $lang=='en'?'active':'' ?>">EN</a>
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
        // скролл вниз → прячем шапку
        header.classList.add('hidden');
    } else {
        // скролл вверх → показываем шапку
        header.classList.remove('hidden');
    }
    lastScrollTop = scrollTop <= 0 ? 0 : scrollTop; // для мобильных
});
</script>
