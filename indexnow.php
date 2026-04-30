<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Получаем все достопримечательности
$attractions = $pdo->query("SELECT slug FROM attractions")->fetchAll();

$key = 'ваш_ключ_indexnow'; // замените на ваш реальный ключ
$host = parse_url(BASE_URL, PHP_URL_HOST);
$urls = [];

foreach ($attractions as $attr) {
    $urls[] = BASE_URL . urlencode($attr['slug']);
}

$data = json_encode([
    'host' => $host,
    'key' => $key,
    'urlList' => $urls
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n" .
                    "Content-Length: " . strlen($data) . "\r\n",
        'content' => $data,
        'timeout' => 5
    ]
]);

$result = @file_get_contents('https://api.indexnow.org/indexnow', false, $context);
echo $result !== false ? 'Все URL отправлены в IndexNow' : 'Ошибка отправки';
