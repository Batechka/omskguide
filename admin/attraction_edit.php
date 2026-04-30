<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

$id = $_GET['id'] ?? null;
$attraction = null;
$translations = ['ru' => [], 'en' => []];
$images = [];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM attractions WHERE id = ?");
    $stmt->execute([$id]);
    $attraction = $stmt->fetch();
    if (!$attraction) die('Достопримечательность не найдена');
    $translations = getAttractionTranslations($id);
    $images = getImages($id);
}

// Обработка POST-запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slug = trim($_POST['slug'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;
    if ($category_id === '') {
        $category_id = null;
    }

    // Загрузка аудиофайла
    $audio_file = $attraction['audio_file'] ?? null;
    if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
        // Удаляем старый файл, если есть
        if (!empty($audio_file)) {
            $oldFile = __DIR__ . '/../uploads/audio/' . $audio_file;
            if (file_exists($oldFile)) unlink($oldFile);
        }
        $ext = pathinfo($_FILES['audio_file']['name'], PATHINFO_EXTENSION);
        $filename = 'audio_' . uniqid() . '.' . $ext;
        $target = __DIR__ . '/../uploads/audio/' . $filename;
        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0755, true);
        }
        if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $target)) {
            $audio_file = $filename;
        }
    }

    if (empty($slug)) {
        $error = 'Slug не может быть пустым';
    } else {
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE attractions SET slug = ?, category_id = ?, latitude = ?, longitude = ?, audio_file = ? WHERE id = ?");
                $stmt->execute([$slug, $category_id, $latitude, $longitude, $audio_file, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO attractions (slug, category_id, latitude, longitude, audio_file) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$slug, $category_id, $latitude, $longitude, $audio_file]);
                $id = $pdo->lastInsertId();
            }

            $allowed_tags = '<h1><h2><h3><h4><h5><h6><p><br><strong><b><em><i><u><ul><ol><li><blockquote>';

            foreach (['ru', 'en'] as $lang_code) {
                $title = strip_tags($_POST["title_$lang_code"] ?? '');
                $short = strip_tags($_POST["short_$lang_code"] ?? '');
                $full = strip_tags($_POST["full_$lang_code"] ?? '', $allowed_tags);

                $stmt = $pdo->prepare("INSERT INTO attraction_translations
                    (attraction_id, language_code, title, short_description, full_description)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    short_description = VALUES(short_description),
                    full_description = VALUES(full_description)");
                $stmt->execute([$id, $lang_code, $title, $short, $full]);
            }

            require_once '../includes/indexnow.php';
            $pageUrl = BASE_URL . urlencode($slug);
            indexnow_send($pageUrl);
            header('Location: index.php?msg=Сохранено');
            exit;
        } catch (PDOException $e) {
            $error = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Редактирование' : 'Добавление' ?> достопримечательности</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CodeMirror -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/material.min.css">
    <style>
        .image-thumb { max-width: 150px; max-height: 150px; margin: 5px; }
        .image-container { display: inline-block; position: relative; }
        .delete-image-btn { position: absolute; top: 5px; right: 5px; }
        .editor-toolbar { margin-bottom: 8px; display: flex; flex-wrap: wrap; gap: 5px; }
        .preview-box {
            min-height: 200px;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 1rem;
            background: #fff;
        }
        .preview-box h1, .preview-box h2, .preview-box h3,
        .preview-box h4, .preview-box h5, .preview-box h6 { margin-top: 1.5rem; }
        .preview-box p { margin-bottom: 1rem; }
        .preview-box ul { padding-left: 2rem; margin-bottom: 1rem; }
        .preview-box blockquote {
            border-left: 4px solid #ccc;
            padding-left: 1rem;
            margin: 1rem 0;
            color: #666;
        }
        .visual-editor {
            min-height: 250px;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 1rem;
            background: #fff;
            overflow-y: auto;
        }
        .visual-editor:focus { outline: 2px solid #86b7fe; }
        .visual-editor h1, .visual-editor h2, .visual-editor h3,
        .visual-editor h4, .visual-editor h5, .visual-editor h6 { margin-top: 1rem; }
        .CodeMirror {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            height: auto;
            min-height: 250px;
            font-size: 14px;
        }
        .nav-pills .nav-link { cursor: pointer; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand"><?= $id ? 'Редактирование' : 'Новая достопримечательность' ?></span>
            <div>
                <a href="index.php" class="btn btn-outline-light btn-sm">К списку</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Выход</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" id="mainForm">
            <div class="card mb-4">
                <div class="card-header">Основные настройки</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Широта (latitude)</label>
                        <input type="text" name="latitude" class="form-control"
                            value="<?= htmlspecialchars($attraction['latitude'] ?? '') ?>"
                            placeholder="54.9833">
                        <small class="text-muted">Например: 54.9833 (центр Омска)</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Долгота (longitude)</label>
                        <input type="text" name="longitude" class="form-control"
                            value="<?= htmlspecialchars($attraction['longitude'] ?? '') ?>"
                            placeholder="73.3667">
                        <small class="text-muted">Например: 73.3667</small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Slug (URL-идентификатор)</label>
                        <input type="text" name="slug" class="form-control"
                               value="<?= htmlspecialchars($attraction['slug'] ?? '') ?>" required>
                        <small class="text-muted">Только латинские буквы, цифры и дефис. Например: omskaya-krepost</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Аудиогид (MP3)</label>
                        <?php if (!empty($attraction['audio_file'])): ?>
                            <div class="mb-2">
                                <audio controls style="max-width:100%">
                                    <source src="<?= BASE_URL ?>uploads/audio/<?= htmlspecialchars($attraction['audio_file']) ?>" type="audio/mpeg">
                                    Ваш браузер не поддерживает аудио.
                                </audio>
                                <br><small>Текущий файл. Загрузите новый, чтобы заменить.</small>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="audio_file" class="form-control" accept=".mp3,audio/mpeg">
                        <small class="text-muted">Загрузите MP3-файл с записью голоса диктора. Оставьте пустым, если не хотите менять.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Категория</label>
                        <select name="category_id" class="form-select">
                            <option value="">Без категории</option>
                            <?php
                            $cats = $pdo->query("
                                SELECT c.id, ct.name
                                FROM categories c
                                JOIN category_translations ct ON c.id = ct.category_id AND ct.language_code = 'ru'
                                ORDER BY ct.name
                            ")->fetchAll();
                            foreach($cats as $cat):
                                $selected = (isset($attraction['category_id']) && $attraction['category_id'] == $cat['id']) ? 'selected' : '';
                            ?>
                                <option value="<?= $cat['id'] ?>" <?= $selected ?>><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Языковые вкладки -->
            <ul class="nav nav-tabs" id="langTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="ru-tab" data-bs-toggle="tab" data-bs-target="#ru" type="button" role="tab">Русский</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="en-tab" data-bs-toggle="tab" data-bs-target="#en" type="button" role="tab">English</button>
                </li>
            </ul>

            <div class="tab-content mt-3" id="langTabsContent">
                <?php foreach (['ru', 'en'] as $lang_code): ?>
                    <div class="tab-pane fade <?= $lang_code === 'ru' ? 'show active' : '' ?>" id="<?= $lang_code ?>" role="tabpanel">
                        <div class="mb-3">
                            <label class="form-label">Заголовок (<?= strtoupper($lang_code) ?>)</label>
                            <input type="text" name="title_<?= $lang_code ?>" class="form-control"
                                   value="<?= htmlspecialchars($translations[$lang_code]['title'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Краткое описание (<?= strtoupper($lang_code) ?>)</label>
                            <textarea name="short_<?= $lang_code ?>" class="form-control" rows="3"><?= htmlspecialchars($translations[$lang_code]['short_description'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Полное описание (<?= strtoupper($lang_code) ?>)</label>

                            <!-- Три вкладки редактора -->
                            <ul class="nav nav-pills mb-2">
                                <li class="nav-item">
                                    <button class="nav-link active" type="button" data-editor-tab="visual_<?= $lang_code ?>">Визуальный</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" type="button" data-editor-tab="html_<?= $lang_code ?>">HTML</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" type="button" data-editor-tab="preview_<?= $lang_code ?>">Предпросмотр</button>
                                </li>
                            </ul>

                            <!-- Панель инструментов (расширенная) -->
                            <div class="editor-toolbar" id="toolbar_<?= $lang_code ?>">
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-command="bold" data-target="<?= $lang_code ?>"><b>Ж</b></button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-command="italic" data-target="<?= $lang_code ?>"><i>К</i></button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-command="underline" data-target="<?= $lang_code ?>"><u>Ч</u></button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-command="formatBlock" data-value="h1" data-target="<?= $lang_code ?>">H1</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-command="formatBlock" data-value="h2" data-target="<?= $lang_code ?>">H2</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-command="formatBlock" data-value="h3" data-target="<?= $lang_code ?>">H3</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-command="formatBlock" data-value="h4" data-target="<?= $lang_code ?>">H4</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-command="formatBlock" data-value="h5" data-target="<?= $lang_code ?>">H5</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-command="formatBlock" data-value="h6" data-target="<?= $lang_code ?>">H6</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-command="formatBlock" data-value="p" data-target="<?= $lang_code ?>">¶</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-command="insertUnorderedList" data-target="<?= $lang_code ?>">• Список</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-command="formatBlock" data-value="blockquote" data-target="<?= $lang_code ?>">“ ”</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-command="removeFormat" data-target="<?= $lang_code ?>">Очистить</button>
                            </div>

                            <!-- Визуальный редактор -->
                            <div id="visual_<?= $lang_code ?>" class="editor-pane visual-pane">
                                <div id="visual_editor_<?= $lang_code ?>" class="visual-editor" contenteditable="true">
                                    <?= $translations[$lang_code]['full_description'] ?? '' ?>
                                </div>
                            </div>

                            <!-- HTML редактор (CodeMirror) -->
                            <div id="html_<?= $lang_code ?>" class="editor-pane html-pane" style="display:none;">
                                <textarea id="full_<?= $lang_code ?>" name="full_<?= $lang_code ?>" style="display:none;"><?= htmlspecialchars($translations[$lang_code]['full_description'] ?? '') ?></textarea>
                                <div id="cm_<?= $lang_code ?>"></div>
                            </div>

                            <!-- Предпросмотр -->
                            <div id="preview_<?= $lang_code ?>" class="editor-pane preview-pane" style="display:none;">
                                <div class="preview-box" id="preview_content_<?= $lang_code ?>"></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Сохранить</button>
                <a href="index.php" class="btn btn-secondary">Отмена</a>
            </div>
        </form>

        <!-- Блок управления изображениями -->
        <?php if ($id): ?>
            <div class="card mt-4">
                <div class="card-header">Изображения</div>
                <div class="card-body">
                    <div id="imageList" class="mb-3">
                        <?php foreach ($images as $img): ?>
                            <div class="image-container border rounded p-2 me-2 mb-2">
                                <img src="<?= UPLOAD_URL . htmlspecialchars($img['filename']) ?>" class="image-thumb" alt="<?= htmlspecialchars($img['alt_text'] ?: $translations['ru']['title'] ?? 'Достопримечательность Омска') ?>">
                                <a href="delete_image.php?id=<?= $img['id'] ?>"
                                class="btn btn-danger btn-sm delete-image-btn"
                                onclick="return confirm('Удалить изображение?')"
                                title="Удалить изображение"
                                aria-label="Удалить изображение">×</a>
                                <?php if (!$img['is_primary']): ?>
                                    <a href="set_primary.php?id=<?= $img['id'] ?>"
                                    class="btn btn-outline-primary btn-sm mt-1"
                                    title="Сделать главным"
                                    aria-label="Сделать изображение главным">Сделать главным</a>
                                <?php else: ?>
                                    <span class="badge bg-success mt-1" title="Это изображение используется как основное">Главное</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <form action="image_upload.php" method="post" enctype="multipart/form-data" id="uploadForm">
                        <input type="hidden" name="attraction_id" value="<?= $id ?>">
                        <div class="input-group">
                            <input type="file" name="image" class="form-control" accept="image/*" required>
                            <button type="submit" class="btn btn-success">Загрузить</button>
                        </div>
                        <div class="mt-2">
                            <input type="text" name="alt" class="form-control"
                                placeholder="Описание изображения (для скринридеров и SEO)"
                                aria-label="Альтернативный текст">
                            <small class="text-muted">Если оставить пустым, будет использовано название достопримечательности.</small>
                        </div>
                    </form>
                    <div id="uploadStatus" class="mt-2"></div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info mt-4">Изображения можно будет добавить после сохранения достопримечательности.</div>
        <?php endif; ?>
    </div>

    <!-- Скрипты -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- CodeMirror -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/matchbrackets.min.js"></script>

    <script>
        // Объекты для хранения редакторов и ссылок
        const editors = {};
        const visualEditors = {};
        const previewDivs = {};
        const panes = {};

        // Инициализация для конкретного языка
        function initEditor(lang) {
            const visualEditor = document.getElementById(`visual_editor_${lang}`);
            const textarea = document.getElementById(`full_${lang}`);
            const cmContainer = document.getElementById(`cm_${lang}`);
            const previewDiv = document.getElementById(`preview_content_${lang}`);

            visualEditors[lang] = visualEditor;
            previewDivs[lang] = previewDiv;

            // Панели вкладок
            panes[lang] = {
                visual: document.getElementById(`visual_${lang}`),
                html: document.getElementById(`html_${lang}`),
                preview: document.getElementById(`preview_${lang}`)
            };

            // CodeMirror
            const editor = CodeMirror(cmContainer, {
                value: textarea.value,
                mode: 'htmlmixed',
                theme: 'material',
                lineNumbers: true,
                matchBrackets: true,
                autoCloseTags: true
            });
            editors[lang] = editor;

            // Синхронизация визуальный -> HTML
            visualEditor.addEventListener('input', function() {
                const html = visualEditor.innerHTML;
                editor.setValue(html);
                textarea.value = html;
            });

            // Синхронизация HTML -> визуальный
            editor.on('change', function() {
                const html = editor.getValue();
                if (document.activeElement !== visualEditor) {
                    visualEditor.innerHTML = html;
                }
                textarea.value = html;
            });

            // Инициализация содержимого
            visualEditor.innerHTML = textarea.value;

            // Обработчики кнопок тулбара
            document.querySelectorAll(`#toolbar_${lang} .btn`).forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const command = btn.dataset.command;
                    const value = btn.dataset.value || null;

                    if (command === 'formatBlock' && value) {
                        document.execCommand(command, false, value);
                    } else if (command === 'insertUnorderedList') {
                        document.execCommand('insertUnorderedList', false, null);
                    } else if (command === 'removeFormat') {
                        document.execCommand('removeFormat', false, null);
                    } else {
                        document.execCommand(command, false, null);
                    }

                    visualEditor.focus();
                    // Синхронизация после команды
                    const html = visualEditor.innerHTML;
                    editor.setValue(html);
                    textarea.value = html;
                });
            });

            // Переключение вкладок
            const tabs = document.querySelectorAll(`[data-editor-tab]`);
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const target = this.dataset.editorTab; // например "visual_ru"
                    const [mode, l] = target.split('_'); // mode = visual, l = ru
                    if (l !== lang) return;

                    // Скрываем все панели
                    Object.values(panes[lang]).forEach(p => p.style.display = 'none');
                    // Показываем нужную
                    panes[lang][mode].style.display = 'block';

                    // Обновляем активную кнопку
                    document.querySelectorAll(`[data-editor-tab^="${mode}"]`).forEach(t => t.classList.remove('active'));
                    this.classList.add('active');

                    // Специальные действия при переключении
                    if (mode === 'preview') {
                        previewDiv.innerHTML = visualEditor.innerHTML;
                    } else if (mode === 'html') {
                        editor.refresh();
                    } else if (mode === 'visual') {
                        setTimeout(() => visualEditor.focus(), 50);
                    }
                });
            });
        }

        // Инициализация для обоих языков
        initEditor('ru');
        initEditor('en');

        // При переключении языковых вкладок Bootstrap нужно обновить CodeMirror
        document.querySelectorAll('#langTabs button[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', (e) => {
                const targetId = e.target.getAttribute('data-bs-target').substring(1);
                if (editors[targetId]) {
                    setTimeout(() => editors[targetId].refresh(), 20);
                }
            });
        });

        // Перед отправкой формы синхронизируем textarea
        document.getElementById('mainForm').addEventListener('submit', function() {
            for (let lang in editors) {
                const textarea = document.getElementById(`full_${lang}`);
                textarea.value = editors[lang].getValue();
            }
        });

        // AJAX-загрузка изображений
        document.getElementById('uploadForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('image_upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    document.getElementById('uploadStatus').innerHTML = '<div class="alert alert-danger">Ошибка загрузки</div>';
                }
            });
        });
    </script>
</body>
</html>
