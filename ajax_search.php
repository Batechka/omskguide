<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$query = trim($_GET['q'] ?? '');
$lang = $_GET['lang'] ?? 'ru';
if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}
$results = searchSuggestions($query, $lang);
header('Content-Type: application/json');
echo json_encode($results);
