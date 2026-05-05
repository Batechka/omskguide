<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

// Транслитерация для slug
function transliterateSlug($text)
{
    $trans = [
        'а' => 'a',
        'б' => 'b',
        'в' => 'v',
        'г' => 'g',
        'д' => 'd',
        'е' => 'e',
        'ё' => 'yo',
        'ж' => 'zh',
        'з' => 'z',
        'и' => 'i',
        'й' => 'y',
        'к' => 'k',
        'л' => 'l',
        'м' => 'm',
        'н' => 'n',
        'о' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'т' => 't',
        'у' => 'u',
        'ф' => 'f',
        'х' => 'h',
        'ц' => 'ts',
        'ч' => 'ch',
        'ш' => 'sh',
        'щ' => 'sch',
        'ъ' => '',
        'ы' => 'y',
        'ь' => '',
        'э' => 'e',
        'ю' => 'yu',
        'я' => 'ya',
        'А' => 'A',
        'Б' => 'B',
        'В' => 'V',
        'Г' => 'G',
        'Д' => 'D',
        'Е' => 'E',
        'Ё' => 'Yo',
        'Ж' => 'Zh',
        'З' => 'Z',
        'И' => 'I',
        'Й' => 'Y',
        'К' => 'K',
        'Л' => 'L',
        'М' => 'M',
        'Н' => 'N',
        'О' => 'O',
        'П' => 'P',
        'Р' => 'R',
        'С' => 'S',
        'Т' => 'T',
        'У' => 'U',
        'Ф' => 'F',
        'Х' => 'H',
        'Ц' => 'Ts',
        'Ч' => 'Ch',
        'Ш' => 'Sh',
        'Щ' => 'Sch',
        'Ъ' => '',
        'Ы' => 'Y',
        'Ь' => '',
        'Э' => 'E',
        'Ю' => 'Yu',
        'Я' => 'Ya'
    ];
    $slug = strtr($text, $trans);
    $slug = preg_replace('/[^A-Za-z0-9\-]+/', '-', $slug);
    return strtolower(trim($slug, '-'));
}

$id = $_GET['id'] ?? null;
$article = null;
$translations = ['ru' => [], 'en' => []];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch();
    if (!$article) die('Статья не найдена');
    $stmt = $pdo->prepare("SELECT * FROM article_translations WHERE article_id = ?");
    $stmt->execute([$id]);
    while ($row = $stmt->fetch()) $translations[$row['language_code']] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slug = trim($_POST['slug'] ?? '');
    if (empty($slug) && !empty($_POST['title_ru'])) {
        $slug = transliterateSlug($_POST['title_ru']);
    }
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $robots_index = isset($_POST['robots_index']) ? 1 : 0;

    // Категория
    $category_id = $_POST['category_id'] ?? null;
    if ($category_id === '') $category_id = null;

    // SEO поля
    $meta_title = $_POST['meta_title'] ?? null;
    $meta_description = $_POST['meta_description'] ?? null;
    $meta_keywords = $_POST['meta_keywords'] ?? null;
    $og_title = $_POST['og_title'] ?? null;
    $og_description = $_POST['og_description'] ?? null;
    $canonical_url = $_POST['canonical_url'] ?? null;
    $focus_keyword = $_POST['focus_keyword'] ?? null;
    $author = $_POST['author'] ?? null;

    // Загрузка OG изображения
    $og_image = $article['og_image'] ?? null;
    if (isset($_FILES['og_image']) && $_FILES['og_image']['error'] === UPLOAD_ERR_OK) {
        if ($og_image) {
            $old = __DIR__ . '/../uploads/articles/' . $og_image;
            if (file_exists($old)) unlink($old);
        }
        $ext = pathinfo($_FILES['og_image']['name'], PATHINFO_EXTENSION);
        $filename = 'og_' . uniqid() . '.' . $ext;
        $target = __DIR__ . '/../uploads/articles/' . $filename;
        if (!is_dir(dirname($target))) mkdir(dirname($target), 0755, true);
        if (move_uploaded_file($_FILES['og_image']['tmp_name'], $target)) $og_image = $filename;
    }

    // Главное изображение статьи
    $image = $article['image'] ?? null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        if ($image) {
            $old = __DIR__ . '/../uploads/articles/' . $image;
            if (file_exists($old)) unlink($old);
        }
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'article_' . uniqid() . '.' . $ext;
        $target = __DIR__ . '/../uploads/articles/' . $filename;
        if (!is_dir(dirname($target))) mkdir(dirname($target), 0755, true);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) $image = $filename;
    }

    if (empty($slug)) {
        $error = 'Slug обязателен';
    } else {
        try {
            if ($id) {
                // UPDATE с category_id
                $stmt = $pdo->prepare("UPDATE articles SET slug=?, is_published=?, image=?, meta_title=?, meta_description=?, meta_keywords=?, og_title=?, og_description=?, og_image=?, canonical_url=?, robots_index=?, focus_keyword=?, author=?, category_id=? WHERE id=?");
                $stmt->execute([$slug, $is_published, $image, $meta_title, $meta_description, $meta_keywords, $og_title, $og_description, $og_image, $canonical_url, $robots_index, $focus_keyword, $author, $category_id, $id]);
            } else {
                // INSERT с category_id
                $stmt = $pdo->prepare("INSERT INTO articles (slug, is_published, image, meta_title, meta_description, meta_keywords, og_title, og_description, og_image, canonical_url, robots_index, focus_keyword, author, category_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$slug, $is_published, $image, $meta_title, $meta_description, $meta_keywords, $og_title, $og_description, $og_image, $canonical_url, $robots_index, $focus_keyword, $author, $category_id]);
                $id = $pdo->lastInsertId();
            }

            $allowed_tags = '<h1><h2><h3><h4><h5><h6><p><br><strong><b><em><i><u><ul><ol><li><blockquote><img><a><table><tr><td><span><figure><figcaption>';

            foreach (['ru', 'en'] as $lang_code) {
                $title = strip_tags($_POST["title_$lang_code"] ?? '');
                $short = strip_tags($_POST["short_$lang_code"] ?? '');
                $full = strip_tags($_POST["full_$lang_code"] ?? '', $allowed_tags);

                $stmt = $pdo->prepare("INSERT INTO article_translations (article_id, language_code, title, short_description, full_content)
                    VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE title=VALUES(title), short_description=VALUES(short_description), full_content=VALUES(full_content)");
                $stmt->execute([$id, $lang_code, $title, $short, $full]);
            }
            header('Location: articles.php?msg=Сохранено');
            exit;
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Редактировать' : 'Новая' ?> статья</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Summernote -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css" rel="stylesheet">
    <style>
        .note-editor.note-frame.fullscreen {
            background: #fff;
        }

        .note-editable {
            background: #fff;
        }

        .note-dropdown-menu,
        .note-editor .dropdown-menu {
            z-index: 9999 !important;
        }

        .navbar {
            position: relative;
            z-index: 1;
        }

        .seo-panel {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
        }

        .seo-item {
            margin-bottom: 0.5rem;
        }

        .seo-good {
            color: green;
        }

        .seo-medium {
            color: orange;
        }

        .seo-bad {
            color: red;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand">Статья</span>
            <div>
                <a href="articles.php" class="btn btn-outline-light btn-sm">К списку</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Выход</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <?php if (isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data" id="mainForm">
            <div class="row">
                <div class="col-lg-9">
                    <div class="card mb-4">
                        <div class="card-header">Основные настройки</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Slug (URL)</label>
                                    <input type="text" name="slug" id="slugInput" class="form-control" value="<?= htmlspecialchars($article['slug'] ?? '') ?>">
                                    <small>Генерируется автоматически из русского заголовка.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Категория</label>
                                    <select name="category_id" class="form-select">
                                        <option value="">Без категории</option>
                                        <?php
                                        $cats = $pdo->query("
                                            SELECT c.id, ct.name
                                            FROM article_categories c
                                            JOIN article_category_translations ct ON c.id = ct.category_id AND ct.language_code = 'ru'
                                            ORDER BY ct.name
                                        ")->fetchAll();
                                        foreach ($cats as $cat):
                                            $selected = (isset($article['category_id']) && $article['category_id'] == $cat['id']) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $cat['id'] ?>" <?= $selected ?>><?= htmlspecialchars($cat['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Изображение статьи</label>
                                    <?php if (!empty($article['image'])): ?>
                                        <div><img src="<?= BASE_URL ?>uploads/articles/<?= htmlspecialchars($article['image']) ?>" style="max-width:150px;"></div>
                                    <?php endif; ?>
                                    <input type="file" name="image" class="form-control">
                                </div>
                            </div>
                            <div class="form-check mb-2">
                                <input type="checkbox" name="is_published" class="form-check-input" id="publishedCheck" <?= !isset($article) || $article['is_published'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="publishedCheck">Опубликована</label>
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-tabs">
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#ru" type="button">Русский</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#en" type="button">English</button></li>
                    </ul>
                    <div class="tab-content mt-3">
                        <?php foreach (['ru', 'en'] as $lang_code): ?>
                            <div class="tab-pane fade <?= $lang_code == 'ru' ? 'show active' : '' ?>" id="<?= $lang_code ?>">
                                <div class="mb-3">
                                    <label>Заголовок (<?= strtoupper($lang_code) ?>)</label>
                                    <input type="text" name="title_<?= $lang_code ?>" class="form-control title-input" data-lang="<?= $lang_code ?>" value="<?= htmlspecialchars($translations[$lang_code]['title'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label>Краткое описание</label>
                                    <textarea name="short_<?= $lang_code ?>" class="form-control" rows="2"><?= htmlspecialchars($translations[$lang_code]['short_description'] ?? '') ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label>Полный текст</label>
                                    <textarea name="full_<?= $lang_code ?>" class="summernote"><?= htmlspecialchars($translations[$lang_code]['full_content'] ?? '') ?></textarea>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- SEO ПАНЕЛЬ -->
                <div class="col-lg-3">
                    <div class="seo-panel">
                        <h5>SEO Анализ</h5>
                        <div id="seoReport"></div>
                    </div>
                    <div class="mt-4">
                        <h5>SEO Поля</h5>
                        <div class="mb-2">
                            <label>Meta Title</label>
                            <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($article['meta_title'] ?? '') ?>">
                        </div>
                        <div class="mb-2">
                            <label>Meta Description</label>
                            <textarea name="meta_description" class="form-control" rows="2"><?= htmlspecialchars($article['meta_description'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-2">
                            <label>Meta Keywords</label>
                            <input type="text" name="meta_keywords" class="form-control" value="<?= htmlspecialchars($article['meta_keywords'] ?? '') ?>">
                        </div>
                        <div class="mb-2">
                            <label>OG Title</label>
                            <input type="text" name="og_title" class="form-control" value="<?= htmlspecialchars($article['og_title'] ?? '') ?>">
                        </div>
                        <div class="mb-2">
                            <label>OG Description</label>
                            <textarea name="og_description" class="form-control" rows="2"><?= htmlspecialchars($article['og_description'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-2">
                            <label>OG Image</label>
                            <?php if (!empty($article['og_image'])): ?>
                                <div><img src="<?= BASE_URL ?>uploads/articles/<?= htmlspecialchars($article['og_image']) ?>" style="max-width:100px;"></div>
                            <?php endif; ?>
                            <input type="file" name="og_image" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label>Canonical URL</label>
                            <input type="text" name="canonical_url" class="form-control" value="<?= htmlspecialchars($article['canonical_url'] ?? '') ?>">
                        </div>
                        <div class="mb-2">
                            <label>Robots Index</label>
                            <select name="robots_index" class="form-select">
                                <option value="1" <?= !isset($article) || $article['robots_index'] ? 'selected' : '' ?>>index</option>
                                <option value="0" <?= isset($article) && !$article['robots_index'] ? 'selected' : '' ?>>noindex</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label>Focus Keyword</label>
                            <input type="text" name="focus_keyword" class="form-control" value="<?= htmlspecialchars($article['focus_keyword'] ?? '') ?>">
                        </div>
                        <div class="mb-2">
                            <label>Автор</label>
                            <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($article['author'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Сохранить</button>
            <a href="articles.php" class="btn btn-secondary mt-3">Отмена</a>
        </form>
    </div>

    <!-- Правильный порядок JS: jQuery -> Bootstrap Bundle -> Summernote -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Инициализация Summernote
            function initSummernote(selector) {
                return $(selector).summernote({
                    height: 400,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline', 'clear']],
                        ['fontname', ['fontname']],
                        ['para', ['ul', 'ol', 'paragraph', 'h2']], // кнопка H2
                        ['insert', ['link', 'picture', 'video', 'table']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ],
                    callbacks: {
                        onImageUpload: function(files) {
                            for (let i = 0; i < files.length; i++) {
                                var formData = new FormData();
                                formData.append('image', files[i]);
                                $.ajax({
                                    url: 'upload_image.php',
                                    method: 'POST',
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    success: function(url) {
                                        $(this).summernote('insertImage', url, function($img) {
                                            $img.attr('alt', 'Изображение статьи');
                                            $img.attr('loading', 'lazy');
                                        });
                                    }.bind(this),
                                    error: function() {
                                        alert('Ошибка загрузки изображения');
                                    }
                                });
                            }
                        },
                        onPaste: function(e) {
                            // Очистка вставленного HTML
                            e.preventDefault();
                            var text = (e.originalEvent || e).clipboardData.getData('text/plain');
                            document.execCommand('insertText', false, text);
                        }
                    }
                });
            }

            // Инициализация всех редакторов
            $('.summernote').each(function() {
                initSummernote(this);
            });

            // Переинициализация при переключении вкладок
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                var targetId = $(e.target).attr('data-bs-target');
                $(targetId).find('.summernote').each(function() {
                    if (!$(this).next('.note-editor').length) initSummernote(this);
                });
            });

            // Генерация slug при вводе русского заголовка
            function generateSlug(text) {
                var slug = text.replace(/\s+/g, '-')
                    .replace(/[^\u0000-\u007F]/g, function(c) {
                        return translit[c] || c;
                    })
                    .replace(/[^a-z0-9\-]/g, '')
                    .toLowerCase()
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '');
                return slug;
            }
            // Упрощённая транслитерация прямо в JS
            var translit = {
                'а': 'a',
                'б': 'b',
                'в': 'v',
                'г': 'g',
                'д': 'd',
                'е': 'e',
                'ё': 'yo',
                'ж': 'zh',
                'з': 'z',
                'и': 'i',
                'й': 'y',
                'к': 'k',
                'л': 'l',
                'м': 'm',
                'н': 'n',
                'о': 'o',
                'п': 'p',
                'р': 'r',
                'с': 's',
                'т': 't',
                'у': 'u',
                'ф': 'f',
                'х': 'h',
                'ц': 'ts',
                'ч': 'ch',
                'ш': 'sh',
                'щ': 'sch',
                'ъ': '',
                'ы': 'y',
                'ь': '',
                'э': 'e',
                'ю': 'yu',
                'я': 'ya',
                'А': 'A',
                'Б': 'B',
                'В': 'V',
                'Г': 'G',
                'Д': 'D',
                'Е': 'E',
                'Ё': 'Yo',
                'Ж': 'Zh',
                'З': 'Z',
                'И': 'I',
                'Й': 'Y',
                'К': 'K',
                'Л': 'L',
                'М': 'M',
                'Н': 'N',
                'О': 'O',
                'П': 'P',
                'Р': 'R',
                'С': 'S',
                'Т': 'T',
                'У': 'U',
                'Ф': 'F',
                'Х': 'H',
                'Ц': 'Ts',
                'Ч': 'Ch',
                'Ш': 'Sh',
                'Щ': 'Sch',
                'Ъ': '',
                'Ы': 'Y',
                'Ь': '',
                'Э': 'E',
                'Ю': 'Yu',
                'Я': 'Ya'
            };
            $('.title-input[data-lang="ru"]').on('input', function() {
                var slug = generateSlug($(this).val());
                $('#slugInput').val(slug);
            });

            // SEO панель (простая проверка)
            function updateSEO() {
                var focus = $('input[name="focus_keyword"]').val().toLowerCase();
                var metaTitle = $('input[name="meta_title"]').val();
                var metaDesc = $('textarea[name="meta_description"]').val();
                var h1 = $('.summernote').eq(0).summernote('code').match(/<h1[^>]*>/i);
                var firstParagraph = $('.summernote').eq(0).summernote('code').match(/<p>(.*?)<\/p>/);
                var url = $('#slugInput').val();
                var html = '';
                html += '<div class="seo-item"><strong>Ключевое слово:</strong> ' + (focus ? '✅ задано' : '❌ не задано') + '</div>';
                html += '<div class="seo-item"><strong>Meta Title:</strong> ' + (metaTitle.length >= 50 && metaTitle.length <= 60 ? '🟢' : metaTitle.length ? '🟡' : '🔴') + ' (' + metaTitle.length + '/50-60)</div>';
                html += '<div class="seo-item"><strong>Meta Description:</strong> ' + (metaDesc.length >= 120 && metaDesc.length <= 160 ? '🟢' : metaDesc.length ? '🟡' : '🔴') + ' (' + metaDesc.length + '/120-160)</div>';
                html += '<div class="seo-item"><strong>H1 в тексте:</strong> ' + (h1 ? '🟢' : '🔴') + '</div>';
                if (focus) {
                    html += '<div class="seo-item"><strong>Ключ в Title:</strong> ' + (metaTitle.toLowerCase().includes(focus) ? '🟢' : '🔴') + '</div>';
                    html += '<div class="seo-item"><strong>Ключ в URL:</strong> ' + (url.toLowerCase().includes(focus.toLowerCase().replace(/\s+/g, '-')) ? '🟢' : '🔴') + '</div>';
                    if (firstParagraph) {
                        html += '<div class="seo-item"><strong>Ключ в первом абзаце:</strong> ' + (firstParagraph[1].toLowerCase().includes(focus) ? '🟢' : '🔴') + '</div>';
                    }
                }
                $('#seoReport').html(html);
            }
            $('input[name="focus_keyword"], input[name="meta_title"], textarea[name="meta_description"], #slugInput').on('input', updateSEO);
            setInterval(updateSEO, 2000); // обновление по таймеру
        });
    </script>
    <script>
        document.addEventListener('click', function(e) {
            if (e.target.closest('.dropdown-toggle')) {
                const toggle = e.target.closest('.dropdown-toggle');
                const dropdown = bootstrap.Dropdown.getOrCreateInstance(toggle);
                dropdown.toggle();
            }
        });
    </script>
</body>

</html>
