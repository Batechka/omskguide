// js/theme.js – управление тёмной/светлой темой и масштабированием шрифта
(function () {
  const body = document.body;
  const html = document.documentElement;

  // --- Тема (светлая / тёмная) ---
  const savedTheme = localStorage.getItem("theme");
  if (savedTheme === "dark") {
    body.classList.add("dark-theme");
  }

  document.querySelectorAll(".theme-toggle").forEach((btn) => {
    btn.addEventListener("click", () => {
      const theme = btn.dataset.theme;
      body.classList.toggle("dark-theme", theme === "dark");
      localStorage.setItem("theme", theme === "dark" ? "dark" : "light");
    });
  });

  // --- Размер шрифта (A+ / A) ---
  let fontSizeLevel = parseInt(localStorage.getItem("fontSizeLevel")) || 0;

  function applyFontSize() {
    html.classList.remove("font-size-large", "font-size-extra-large");
    if (fontSizeLevel === 1) html.classList.add("font-size-large");
    if (fontSizeLevel === 2) html.classList.add("font-size-extra-large");
  }
  applyFontSize();

  document.querySelectorAll(".font-size-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      if (btn.dataset.size === "increase") {
        fontSizeLevel = Math.min(fontSizeLevel + 1, 2);
      } else if (btn.dataset.size === "reset") {
        fontSizeLevel = 0;
      }
      applyFontSize();
      localStorage.setItem("fontSizeLevel", fontSizeLevel);
    });
  });
})();
