<?php
require_once __DIR__ . '/config.php';

function getAttractions($lang = null) {
    global $pdo;
    if (!$lang) $lang = $_SESSION['lang'];

    $sql = "SELECT a.id, a.slug, t.title, t.short_description,
                   (SELECT filename FROM images WHERE attraction_id = a.id AND is_primary = 1 LIMIT 1) as primary_image
            FROM attractions a
            JOIN attraction_translations t ON a.id = t.attraction_id AND t.language_code = :lang
            ORDER BY a.id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['lang' => $lang]);
    return $stmt->fetchAll();
}

function getAttractionBySlug($slug, $lang = null) {
    global $pdo;
    if (!$lang) $lang = $_SESSION['lang'] ?? 'ru';

    $sql = "SELECT a.id, a.slug, a.category_id, a.views_count, a.created_at,
                   a.latitude, a.longitude,
                   t.title, t.short_description, t.full_description
            FROM attractions a
            JOIN attraction_translations t ON a.id = t.attraction_id AND t.language_code = :lang
            WHERE a.slug = :slug";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['slug' => $slug, 'lang' => $lang]);
    return $stmt->fetch();
}
function getImages($attraction_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM images WHERE attraction_id = ? ORDER BY sort_order, id");
    $stmt->execute([$attraction_id]);
    return $stmt->fetchAll();
}

function getAllAttractionsAdmin() {
    global $pdo;
    $sql = "SELECT a.id, a.slug,
                   MAX(CASE WHEN t.language_code = 'ru' THEN t.title END) as title_ru,
                   MAX(CASE WHEN t.language_code = 'en' THEN t.title END) as title_en
            FROM attractions a
            LEFT JOIN attraction_translations t ON a.id = t.attraction_id
            GROUP BY a.id
            ORDER BY a.id";
    return $pdo->query($sql)->fetchAll();
}

function getAttractionTranslations($attraction_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT language_code, title, short_description, full_description
                           FROM attraction_translations WHERE attraction_id = ?");
    $stmt->execute([$attraction_id]);
    $result = [];
    while ($row = $stmt->fetch()) {
        $result[$row['language_code']] = $row;
    }
    return $result;
}




/**
 * Получить похожие достопримечательности (случайные, исключая текущую)
 */
function getRelatedAttractions($currentId, $limit = 3, $lang = null) {
    global $pdo;
    if (!$lang) $lang = $_SESSION['lang'] ?? 'ru';

    $sql = "SELECT a.id, a.slug, t.title, t.short_description,
                   (SELECT filename FROM images WHERE attraction_id = a.id AND is_primary = 1 LIMIT 1) as primary_image
            FROM attractions a
            JOIN attraction_translations t ON a.id = t.attraction_id AND t.language_code = :lang
            WHERE a.id != :current_id
            ORDER BY RAND()
            LIMIT :limit";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':current_id', $currentId, PDO::PARAM_INT);
    $stmt->bindValue(':lang', $lang);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Форматирование даты с учётом языка
 */
function formatDate($date, $lang) {
    $timestamp = strtotime($date);
    if ($lang == 'ru') {
        return date('d.m.Y', $timestamp);
    } else {
        return date('F j, Y', $timestamp);
    }
}

/**
 * Генерация оглавления из HTML-контента с заголовками H3
 */
function generateTOC($html) {
    if (empty($html)) return [];

    $toc = [];
    // Ищем все заголовки h1-h6
    preg_match_all('/<h([1-6])>(.*?)<\/h\1>/i', $html, $matches, PREG_SET_ORDER);

    foreach ($matches as $index => $match) {
        $level = $match[1];
        $title = strip_tags($match[2]);
        $id = 'section-' . ($index + 1) . '-' . preg_replace('/[^a-z0-9]+/', '-', strtolower(transliterate($title)));
        $toc[] = ['id' => $id, 'title' => $title, 'level' => $level];
    }
    return $toc;
}

/**
 * Добавление якорей к заголовкам H3 в HTML-контенте
 */
function addAnchorsToHeadings($html) {
    if (empty($html)) return $html;

    $index = 1;
    return preg_replace_callback('/<h([1-6])>(.*?)<\/h\1>/i', function($matches) use (&$index) {
        $level = $matches[1];
        $title = strip_tags($matches[2]);
        $id = 'section-' . $index . '-' . preg_replace('/[^a-z0-9]+/', '-', strtolower(transliterate($title)));
        $index++;
        return "<h{$level} id=\"{$id}\">{$matches[2]}</h{$level}>";
    }, $html);
}

/**
 * Транслитерация русского текста в латиницу для ID
 */
function transliterate($text) {
    $translit = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
        'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
        'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
        'я' => 'ya',
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
        'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
        'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
        'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
        'Я' => 'Ya'
    ];
    return strtr($text, $translit);
}

/**
 * Получить все категории на нужном языке
 */
function getCategories($lang = null) {
    global $pdo;
    if (!$lang) $lang = $_SESSION['lang'] ?? 'ru';
    $sql = "SELECT c.id, c.slug, ct.name
            FROM categories c
            JOIN category_translations ct ON c.id = ct.category_id AND ct.language_code = ?
            ORDER BY ct.name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$lang]);
    return $stmt->fetchAll();
}

/**
 * Получить достопримечательности с фильтром по категории и поиском
 */
function getFilteredAttractions($category_id = null, $search = '', $lang = null) {
    global $pdo;
    if (!$lang) $lang = $_SESSION['lang'] ?? 'ru';

    $sql = "SELECT a.id, a.slug, a.views_count, t.title, t.short_description,
                   (SELECT filename FROM images WHERE attraction_id = a.id AND is_primary = 1 LIMIT 1) as primary_image
            FROM attractions a
            JOIN attraction_translations t ON a.id = t.attraction_id AND t.language_code = :lang";
    $params = ['lang' => $lang];

    if ($category_id) {
        $sql .= " AND a.category_id = :category_id";
        $params['category_id'] = $category_id;
    }
    if ($search) {
        $sql .= " AND (t.title LIKE :search OR t.short_description LIKE :search)";
        $params['search'] = '%' . $search . '%';
    }
    $sql .= " ORDER BY a.id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Поиск с автодополнением
 */
function searchSuggestions($query, $lang = 'ru') {
    global $pdo;
    $sql = "SELECT a.id, a.slug, t.title
            FROM attractions a
            JOIN attraction_translations t ON a.id = t.attraction_id AND t.language_code = ?
            WHERE t.title LIKE ? OR t.short_description LIKE ?
            LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$lang, "%$query%", "%$query%"]);
    return $stmt->fetchAll();
}
/**
 * Нечёткий поиск достопримечательностей
 */
function fuzzySearchAttractions($query, $lang = 'ru', $maxDistance = 3) {
    global $pdo;
    $sql = "SELECT a.id, a.slug, t.title
            FROM attractions a
            JOIN attraction_translations t ON a.id = t.attraction_id AND t.language_code = ?
            WHERE t.title IS NOT NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$lang]);
    $all = $stmt->fetchAll();

    $results = [];
    $queryLower = mb_strtolower(trim($query));

    foreach ($all as $item) {
        $titleLower = mb_strtolower($item['title']);
        if (mb_strpos($titleLower, $queryLower) !== false) {
            $results[] = $item;
            continue;
        }
        $lev = levenshtein($queryLower, $titleLower);
        $maxLen = max(mb_strlen($queryLower), mb_strlen($titleLower));
        $similarity = 1 - ($lev / $maxLen);
        if ($similarity > 0.6 && $lev <= $maxDistance) {
            $results[] = $item;
        }
    }
    return $results;
}
/**
 * Транслитерация русского текста в латиницу (упрощённая)
 */
function rus2lat($text) {
    $trans = [
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo','ж'=>'zh','з'=>'z','и'=>'i',
        'й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t',
        'у'=>'u','ф'=>'f','х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'sch','ъ'=>'','ы'=>'y','ь'=>'',
        'э'=>'e','ю'=>'yu','я'=>'ya'
    ];
    $text = mb_strtolower($text);
    return strtr($text, $trans);
}

/**
 * Русский метафон (упрощённый фонетический код)
 */
function russianMetaphone($word) {
    $word = mb_strtolower($word);
    // Заменяем похожие звуки
    $replacements = [
        'дж'=>'ж','дч'=>'ч','тс'=>'ц','тц'=>'ц','сч'=>'щ','зч'=>'щ','зд'=>'ст',
        'о'=>'а','е'=>'и','ё'=>'о','ю'=>'у','я'=>'а'
    ];
    foreach ($replacements as $from => $to) {
        $word = str_replace($from, $to, $word);
    }
    // Удаляем мягкий/твёрдый знак и удвоения
    $word = preg_replace('/[ьъ]/u', '', $word);
    $word = preg_replace('/(.)\1+/u', '$1', $word);
    return $word;
}

/**
 * Разбиение строки на триграммы
 */
function trigrams($str) {
    $str = mb_strtolower($str);
    $trigrams = [];
    $len = mb_strlen($str);
    for ($i = 0; $i < $len - 2; $i++) {
        $trigrams[] = mb_substr($str, $i, 3);
    }
    return $trigrams;
}

/**
 * Коэффициент сходства на основе триграмм
 */
function trigramSimilarity($str1, $str2) {
    $tri1 = trigrams($str1);
    $tri2 = trigrams($str2);
    if (empty($tri1) || empty($tri2)) return 0;
    $intersection = array_intersect($tri1, $tri2);
    return count($intersection) / max(count($tri1), count($tri2));
}

/**
 * Продвинутый нечёткий поиск достопримечательностей
 */
function advancedFuzzySearch($query, $lang = 'ru', $limit = 10) {
    global $pdo;
    $sql = "SELECT a.id, a.slug, t.title
            FROM attractions a
            JOIN attraction_translations t ON a.id = t.attraction_id AND t.language_code = ?
            WHERE t.title IS NOT NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$lang]);
    $items = $stmt->fetchAll();

    $queryOrig = trim($query);
    $queryLower = mb_strtolower($queryOrig);

    // Варианты запроса для сравнения
    $queryVariants = [
        $queryLower,                          // оригинал
        rus2lat($queryLower),                 // транслитерация
        russianMetaphone($queryLower)         // метафон
    ];

    $scored = [];
    foreach ($items as $item) {
        $title = $item['title'];
        $titleLower = mb_strtolower($title);
        $titleLat = rus2lat($titleLower);
        $titleMetaphone = russianMetaphone($titleLower);

        $maxScore = 0;

        // Проверяем прямое вхождение
        if (mb_strpos($titleLower, $queryLower) !== false) {
            $maxScore = 1.0;
        } elseif (mb_strpos($titleLat, $queryLower) !== false) {
            $maxScore = 0.9;
        } else {
            // Сравнение по триграммам
            foreach ($queryVariants as $qVar) {
                $score1 = trigramSimilarity($qVar, $titleLower);
                $score2 = trigramSimilarity($qVar, $titleLat);
                $score3 = trigramSimilarity(russianMetaphone($qVar), $titleMetaphone);
                $best = max($score1, $score2, $score3);
                if ($best > $maxScore) $maxScore = $best;
            }

            // Если запрос короткий, добавляем расстояние Левенштейна
            if (mb_strlen($queryLower) <= 5) {
                $lev = levenshtein($queryLower, $titleLower);
                $maxLen = max(mb_strlen($queryLower), mb_strlen($titleLower));
                $levScore = 1 - ($lev / $maxLen);
                $maxScore = max($maxScore, $levScore * 0.8);
            }
        }

        if ($maxScore > 0.3) {
            $item['score'] = $maxScore;
            $scored[] = $item;
        }
    }

    // Сортировка по убыванию релевантности
    usort($scored, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });

    return array_slice($scored, 0, $limit);
}
?>
