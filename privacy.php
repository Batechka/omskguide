<?php
require_once 'includes/config.php';

$pageTitle = $lang == 'ru' ? 'Политика конфиденциальности' : 'Privacy Policy';
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
                <h1>Политика обработки персональных данных</h1>
                <p><strong>1. Общие положения</strong><br>
                Настоящая Политика конфиденциальности регулирует отношения между Администрацией сайта «Омскъ Исторический» (далее — Сайт) и Пользователем, связанные с обработкой персональных данных при использовании Сайта.</p>

                <p><strong>2. Какие данные мы собираем</strong><br>
                Мы можем собирать следующую информацию:<br>
                - Технические данные: IP-адрес, тип браузера, язык, время доступа, cookie-файлы.<br>
                - Данные, предоставленные Пользователем добровольно: имя, адрес электронной почты (при заполнении формы подписки или обратной связи).</p>

                <p><strong>3. Цели обработки данных</strong><br>
                Сбор данных осуществляется исключительно в целях:<br>
                - Улучшения качества работы Сайта и удобства его использования.<br>
                - Анализа посещаемости с помощью сервиса Яндекс.Метрика.<br>
                - Предоставления ответов на обращения Пользователя (если был указан email).<br>
                - Информирования о новых материалах (при наличии подписки).</p>

                <p><strong>4. Передача данных третьим лицам</strong><br>
                Мы не передаём персональные данные Пользователей третьим лицам, за исключением случаев, предусмотренных законодательством РФ, или когда это необходимо для работы сервисов аналитики (Яндекс.Метрика). Данные, передаваемые в Метрику, обезличены.</p>

                <p><strong>5. Использование cookie</strong><br>
                Сайт использует cookie-файлы для сохранения настроек интерфейса (язык, тема, размер шрифта) и сбора статистики. Продолжая использовать Сайт, вы соглашаетесь с использованием cookie.</p>

                <p><strong>6. Права Пользователя</strong><br>
                Пользователь вправе в любой момент отозвать согласие на обработку данных, направив запрос на электронную почту Администрации: info@omskguide.ru. Также вы можете удалить сохранённые настройки, очистив cookie в своём браузере.</p>

                <p><strong>7. Срок хранения данных</strong><br>
                Персональные данные хранятся не дольше, чем это необходимо для целей их обработки, после чего подлежат удалению.</p>

                <p><strong>8. Контакты</strong><br>
                По всем вопросам, связанным с обработкой персональных данных, обращайтесь по адресу: info@omskguide.ru.</p>
                <p><em>Дата последнего обновления: 18.04.2026</em></p>
            <?php else: ?>
                <h1>Privacy Policy</h1>
                <p><strong>1. General Provisions</strong><br>
                This Privacy Policy governs the relationship between the Administration of the "Historical Omsk" website and the User regarding the processing of personal data.</p>
                <p><strong>2. Data We Collect</strong><br>
                We may collect technical data such as IP address, browser type, language, access time, and cookies. If you voluntarily provide your name or email (e.g., via a subscription form), we store that information as well.</p>
                <p><strong>3. Purposes of Data Processing</strong><br>
                Data is used solely to improve the website, analyze traffic via Yandex.Metrica, respond to inquiries, and send updates if subscribed.</p>
                <p><strong>4. Data Sharing</strong><br>
                We do not share personal data with third parties except as required by law or for analytics (anonymized data in Yandex.Metrica).</p>
                <p><strong>5. Cookies</strong><br>
                The website uses cookies to remember interface settings (language, theme, font size) and for analytics. By continuing to use the site, you consent to our use of cookies.</p>
                <p><strong>6. User Rights</strong><br>
                You may withdraw consent at any time by contacting info@omskguide.ru. You can also delete cookies via your browser settings.</p>
                <p><strong>7. Data Retention</strong><br>
                Personal data is stored only as long as necessary for the stated purposes.</p>
                <p><strong>8. Contact</strong><br>
                For any privacy-related questions, email info@omskguide.ru.</p>
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
