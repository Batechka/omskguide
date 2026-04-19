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

    <!-- Общие скрипты (тема, шрифт) -->
    <script>
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
    <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
