<?php
require_once 'includes/config.php';
$pageTitle = $lang == 'ru' ? 'Пользовательское соглашение' : 'Terms of Use';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $lang == 'ru' ? 'Политика конфиденциальности сайта «Омскъ Исторический».' : 'Privacy policy of the «Historical Omsk» website.' ?>">
    <title><?= $pageTitle ?> – <?= __('site_title') ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/userprava.css">
    <?php include 'includes/metrica.php'; ?>
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="<?= BASE_URL ?>" class="site-title"><span>Омскъ</span> Исторический</a>
            <div class="nav-links">
                <a href="<?= BASE_URL ?>" class="nav-link"><?= __('home') ?></a>
                <div class="lang-switch">
                    <a href="?lang=ru" class="lang-btn <?= $lang=='ru'?'active':'' ?>">RU</a>
                    <a href="?lang=en" class="lang-btn <?= $lang=='en'?'active':'' ?>">EN</a>
                </div>
            </div>
        </div>
    </header>
    <main class="container">
        <article class="legal-page">
            <?php if ($lang == 'ru'): ?>
                <h1>Пользовательское соглашение</h1>
                <p>Настоящее Соглашение определяет условия использования материалов и сервисов сайта «Омскъ Исторический» (далее — Сайт).</p>

                <p><strong>1. Общие условия</strong><br>
                1.1. Использование Сайта означает полное и безоговорочное принятие Пользователем настоящего Соглашения.<br>
                1.2. Администрация Сайта оставляет за собой право в одностороннем порядке изменять данное Соглашение. Новая редакция вступает в силу с момента её публикации.</p>

                <p><strong>2. Права на контент</strong><br>
                2.1. Все текстовые материалы, фотографии и иные объекты, размещённые на Сайте, являются собственностью Администрации или используются с разрешения правообладателей.<br>
                2.2. Допускается цитирование материалов Сайта в некоммерческих целях с обязательной активной гиперссылкой на источник.<br>
                2.3. Полное копирование контента (в том числе в коммерческих целях) запрещено без письменного согласия Администрации.</p>

                <p><strong>3. Ответственность</strong><br>
                3.1. Администрация не несёт ответственности за возможный ущерб, связанный с использованием или невозможностью использования Сайта.<br>
                3.2. Администрация не гарантирует абсолютную точность и актуальность информации о достопримечательностях, но прилагает все усилия для её проверки.</p>

                <p><strong>4. Возрастные ограничения</strong><br>
                Сайт предназначен для лиц любого возраста и не содержит материалов, запрещённых для детей.</p>

                <p><strong>5. Заключительные положения</strong><br>
                По всем вопросам, связанным с использованием Сайта, обращайтесь по адресу: info@omskguide.ru.</p>
                <p><em>Дата последнего обновления: 18.04.2026</em></p>
            <?php else: ?>
                <h1>Terms of Use</h1>
                <p>This Agreement defines the terms of use of the "Historical Omsk" website.</p>
                <p><strong>1. General Conditions</strong><br>
                1.1. Use of the Website implies full acceptance of this Agreement.<br>
                1.2. The Administration may unilaterally modify this Agreement. The new version becomes effective upon publication.</p>
                <p><strong>2. Content Rights</strong><br>
                2.1. All text, images, and other materials are owned by the Administration or used with permission.<br>
                2.2. Quoting for non-commercial purposes is allowed with an active hyperlink to the source.<br>
                2.3. Full copying of content for commercial purposes is prohibited without written consent.</p>
                <p><strong>3. Liability</strong><br>
                3.1. The Administration is not liable for any damage resulting from the use or inability to use the Website.<br>
                3.2. While we strive for accuracy, we do not guarantee the absolute correctness of all information.</p>
                <p><strong>4. Age Restrictions</strong><br>
                The Website is suitable for all ages and contains no harmful content.</p>
                <p><strong>5. Contact</strong><br>
                For any questions, please email info@omskguide.ru.</p>
                <p><em>Last updated: April 18, 2026</em></p>
            <?php endif; ?>
        </article>
    </main>
    <footer class="site-footer">
        <div class="container">
            <p>© <?= date('Y') ?> Омск. Историческое наследие.</p>
        </div>
    </footer>
</body>
</html>
