<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$query = trim($_GET['q'] ?? '');
$lang = $_GET['lang'] ?? 'ru';

if (mb_strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$results = advancedFuzzySearch($query, $lang, 10);

header('Content-Type: application/json');
echo json_encode($results);
