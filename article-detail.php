<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($article)) {
    header('Location: ' . BASE_URL . 'articles');
    exit;
}

if (!isset($lang)) {
    $lang = $_SESSION['lang'] ?? 'ru';
}

$pageTitle = !empty($article['meta_title'])
    ? $article['meta_title']
    : ($article['title'] . ' — Статья');

$pageDescription = !empty($article['meta_description'])
    ? $article['meta_description']
    : (!empty($article['short_description']) ? $article['short_description'] : '');

$canonicalUrl = !empty($article['canonical_url'])
    ? $article['canonical_url']
    : BASE_URL . 'article/' . $article['slug'];

$ogImage = !empty($article['og_image'])
    ? BASE_URL . 'uploads/articles/' . $article['og_image']
    : (!empty($article['image'])
        ? BASE_URL . 'uploads/articles/' . $article['image']
        : BASE_URL . 'img/default-og.jpg');

// Связанные статьи
$relatedArticles = $pdo->query("
    SELECT a.id, a.slug, a.image, t.title, t.short_description
    FROM articles a
    JOIN article_translations t ON a.id = t.article_id AND t.language_code = '{$lang}'
    WHERE a.is_published = 1 AND a.id != {$article['id']}
    ORDER BY RAND()
    LIMIT 3
")->fetchAll();

// Время чтения
$text = strip_tags($article['full_content'] ?? '');
$wordCount = preg_match_all('/\p{L}+/u', $text, $matches);
$readTime = ceil($wordCount / 200) ?: 1;
$readTimeText = $lang == 'ru' ? "~{$readTime} мин чтения" : "~{$readTime} min read";

// ToC
$contentHtml = $article['full_content'] ?? '';
preg_match_all('/<(h[23])[^>]*>(.*?)<\/\1>/i', $contentHtml, $headings, PREG_SET_ORDER);

$tocItems = [];
$index = 1;

foreach ($headings as $h) {
    $id = 'toc-' . $index++;
    $tocItems[] = [
        'tag' => $h[1],
        'text' => strip_tags($h[2]),
        'id' => $id
    ];
    $contentHtml = str_replace(
        $h[0],
        '<' . $h[1] . ' id="' . $id . '">' . $h[2] . '</' . $h[1] . '>',
        $contentHtml
    );
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">

    <?php if (!empty($article['robots_index']) && (int)$article['robots_index'] === 0): ?>
        <meta name="robots" content="noindex">
    <?php endif; ?>

    <meta property="og:type" content="article">
    <meta property="og:title" content="<?= htmlspecialchars($article['og_title'] ?: $pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($article['og_description'] ?: $pageDescription) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">

    <link rel="alternate" hreflang="ru" href="<?= BASE_URL ?>ru/">
    <link rel="alternate" hreflang="en" href="<?= BASE_URL ?>en/">
    <link rel="alternate" hreflang="x-default" href="<?= BASE_URL ?>ru/">

    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/hlebnikrosh.css">
    <?php include 'includes/metrica.php'; ?>

    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Article",
            "headline": "<?= htmlspecialchars($article['title']) ?>",
            "description": "<?= htmlspecialchars($pageDescription) ?>",
            "image": "<?= htmlspecialchars($ogImage) ?>",
            "author": {
                "@type": "Person",
                "name": "<?= htmlspecialchars($article['author'] ?? 'omskguide') ?>"
            },
            "datePublished": "<?= date('c', strtotime($article['created_at'])) ?>",
            "dateModified": "<?= date('c', strtotime($article['updated_at'] ?? $article['created_at'])) ?>",
            "mainEntityOfPage": {
                "@type": "WebPage",
                "@id": "<?= htmlspecialchars($canonicalUrl) ?>"
            }
        }
    </script>

    <style>
        .reading-progress {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: var(--primary);
            z-index: 1000;
            width: 0;
            transition: width 0.1s linear;
        }

        .article-page {
            padding: 2rem 0 4rem;
        }

        .article-hero {
            background: linear-gradient(135deg, rgba(179, 78, 58, 0.08) 0%, rgba(143, 59, 42, 0.02) 100%);
            border-radius: var(--radius);
            padding: 2rem;
            margin: 1.5rem 0 1.5rem;
            box-shadow: var(--shadow);
        }

        .article-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 1.15;
            margin-bottom: 1rem;
            color: var(--text);
        }

        .article-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem 1rem;
            align-items: center;
            color: var(--muted);
            font-size: 0.95rem;
        }

        .article-meta span {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: var(--surface);
            border: 1px solid var(--border);
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
        }

        .article-cover {
            margin: 1rem 0 1.5rem;
            overflow: hidden;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            max-height: 320px;
        }

        .article-cover img {
            width: 100%;
            height: 320px;
            display: block;
            object-fit: cover;
            object-position: center center;
        }

        .toc {
            background: var(--surface);
            padding: 1.25rem 1.25rem 0.75rem;
            border-radius: var(--radius);
            margin: 2rem 0;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .toc h4 {
            font-family: 'Playfair Display', serif;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .toc ul {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }

        .toc li {
            margin-bottom: 0.7rem;
        }

        .toc li.ps-3 {
            padding-left: 1rem;
        }

        .toc a {
            color: var(--text);
            text-decoration: none;
            border-bottom: 1px dotted transparent;
        }

        .toc a:hover {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .article-content {
            background: var(--surface);
            padding: 2rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            line-height: 1.8;
            border: 1px solid var(--border);
        }

        .article-content h2,
        .article-content h3 {
            font-family: 'Playfair Display', serif;
            scroll-margin-top: 90px;
            color: var(--text);
        }

        .article-content h2 {
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-size: 2rem;
        }

        .article-content h3 {
            margin-top: 1.5rem;
            margin-bottom: 0.8rem;
            font-size: 1.45rem;
        }

        .article-content p {
            margin-bottom: 1rem;
        }

        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
        }

        .article-share {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
            margin: 2rem 0;
            padding: 1.25rem;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .article-share>span {
            font-weight: 600;
            color: var(--text);
            margin-right: 0.25rem;
        }

        .article-share .btn {
            padding: 0.4rem 1.1rem;
            border-radius: 20px;
            color: #fff;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            border: none;
        }

        .btn.vk {
            background: #4a76a8;
        }

        .btn.telegram {
            background: #2aabee;
        }

        .btn.whatsapp {
            background: #25d366;
        }

        .btn.viber {
            background: #7360f2;
        }

        .btn.email {
            background: #6c757d;
        }

        .btn.copy {
            background: #6c757d;
            cursor: pointer;
        }

        .related-articles {
            margin-top: 3rem;
        }

        .related-articles h3 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            margin-bottom: 1.25rem;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .related-card {
            background: var(--surface);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform .25s ease, box-shadow .25s ease;
            border: 1px solid var(--border);
        }

        .related-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
        }

        .related-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            display: block;
        }

        .related-card-body {
            padding: 1rem 1rem 1.25rem;
        }

        .related-card-body h5 {
            font-family: 'Playfair Display', serif;
            font-size: 1.15rem;
            margin-bottom: 0.75rem;
            line-height: 1.35;
        }

        .author-block {
            border-top: 2px solid var(--border);
            margin-top: 2.5rem;
            padding-top: 1.5rem;
        }

        .copy-toast {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary-dark);
            color: #fff;
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            font-size: 0.9rem;
            z-index: 2000;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
        }

        .copy-toast.show {
            opacity: 1;
        }

        @media (max-width: 768px) {
            .article-hero {
                padding: 1.25rem;
            }

            .article-cover {
                max-height: 220px;
            }

            .article-cover img {
                height: 220px;
            }

            .article-content {
                padding: 1.25rem;
            }

            .article-share {
                padding: 1rem;
            }

            .article-share .btn {
                width: 100%;
                justify-content: center;
            }

            .related-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="reading-progress" id="progressBar"></div>
    <div class="copy-toast" id="copyToast"><?= $lang == 'ru' ? 'Ссылка скопирована!' : 'Link copied!' ?></div>

    <?php $slugForLang = '';
    include 'components/header.php'; ?>

    <main class="container article-page">
        <div class="breadcrumbs">
            <a href="<?= BASE_URL ?>"><?= __('home') ?></a> /
            <a href="<?= BASE_URL ?>articles"><?= $lang == 'ru' ? 'Статьи' : 'Articles' ?></a> /
            <span><?= htmlspecialchars($article['title']) ?></span>
        </div>

        <section class="article-hero">
            <h1><?= htmlspecialchars($article['title']) ?></h1>

            <div class="article-meta">
                <?php if (!empty($article['author'])): ?>
                    <span>✍️ <?= htmlspecialchars($article['author']) ?></span>
                <?php endif; ?>
                <span>⏱️ <?= htmlspecialchars($readTimeText) ?></span>
                <span>📅 <?= date('d.m.Y', strtotime($article['created_at'])) ?></span>
            </div>
        </section>

        <?php if (!empty($article['image'])): ?>
            <div class="article-cover">
                <img
                    src="<?= BASE_URL ?>uploads/articles/<?= htmlspecialchars($article['image']) ?>"
                    alt="<?= htmlspecialchars($article['title']) ?>"
                    loading="lazy">
            </div>
        <?php endif; ?>

        <?php if (!empty($tocItems)): ?>
            <nav class="toc" aria-label="<?= $lang == 'ru' ? 'Содержание статьи' : 'Article contents' ?>">
                <h4><?= $lang == 'ru' ? 'Содержание' : 'Table of Contents' ?></h4>
                <ul>
                    <?php foreach ($tocItems as $toc): ?>
                        <li class="<?= $toc['tag'] == 'h3' ? 'ps-3' : '' ?>">
                            <a href="#<?= htmlspecialchars($toc['id']) ?>"><?= htmlspecialchars($toc['text']) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endif; ?>

        <article class="article-content">
            <?= $contentHtml ?>
        </article>

        <div class="article-share">
            <span><?= $lang == 'ru' ? 'Поделиться' : 'Share' ?>:</span>
            <a href="https://vk.com/share.php?url=<?= urlencode($canonicalUrl) ?>&title=<?= urlencode($pageTitle) ?>" target="_blank" class="btn vk" rel="noopener">VK</a>
            <a href="https://t.me/share/url?url=<?= urlencode($canonicalUrl) ?>&text=<?= urlencode($pageTitle) ?>" target="_blank" class="btn telegram" rel="noopener">Telegram</a>
            <a href="https://api.whatsapp.com/send?text=<?= urlencode($pageTitle . ' ' . $canonicalUrl) ?>" target="_blank" class="btn whatsapp" rel="noopener">WhatsApp</a>
            <a href="viber://forward?text=<?= urlencode($pageTitle . ' ' . $canonicalUrl) ?>" class="btn viber" rel="noopener">Viber</a>
            <a href="mailto:?subject=<?= urlencode($pageTitle) ?>&body=<?= urlencode($canonicalUrl) ?>" class="btn email">Email</a>
            <button type="button" class="btn copy" onclick="copyLink('<?= htmlspecialchars($canonicalUrl, ENT_QUOTES) ?>')">📋 <?= $lang == 'ru' ? 'Копировать' : 'Copy' ?></button>
        </div>

        <?php if (!empty($relatedArticles)): ?>
            <section class="related-articles">
                <h3><?= $lang == 'ru' ? 'Похожие статьи' : 'Related Articles' ?></h3>
                <div class="related-grid">
                    <?php foreach ($relatedArticles as $rel): ?>
                        <article class="related-card">
                            <?php if (!empty($rel['image'])): ?>
                                <img
                                    src="<?= BASE_URL ?>uploads/articles/<?= htmlspecialchars($rel['image']) ?>"
                                    alt="<?= htmlspecialchars($rel['title']) ?>"
                                    loading="lazy">
                            <?php endif; ?>
                            <div class="related-card-body">
                                <h5><?= htmlspecialchars($rel['title']) ?></h5>
                                <p><?= htmlspecialchars($rel['short_description'] ?? '') ?></p>
                                <a href="<?= BASE_URL ?>article/<?= urlencode($rel['slug']) ?>" class="btn btn-sm btn-primary">
                                    <?= $lang == 'ru' ? 'Читать' : 'Read' ?>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($article['author'])): ?>
            <div class="author-block">
                <p><strong><?= htmlspecialchars($article['author']) ?></strong></p>
                <p><?= $lang == 'ru' ? 'Автор путеводителя «Омскъ Исторический»' : 'Author of the "Historical Omsk" guide' ?></p>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'components/footer.php'; ?>

    <script>
        window.addEventListener('scroll', function() {
            var winHeight = window.innerHeight;
            var docHeight = document.documentElement.scrollHeight - winHeight;
            var scrollTop = window.scrollY || window.pageYOffset;
            var progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
            document.getElementById('progressBar').style.width = progress + '%';
        });

        function copyLink(url) {
            navigator.clipboard.writeText(url).then(function() {
                var toast = document.getElementById('copyToast');
                toast.classList.add('show');
                setTimeout(function() {
                    toast.classList.remove('show');
                }, 2000);
            });
        }
    </script>

    <script src="<?= BASE_URL ?>js/theme.js"></script>
</body>

</html>
