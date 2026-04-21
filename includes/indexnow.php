<?php
function indexnow_send($url) {
    $key = 'cad230e48fbfe728dc5f2cc3bffd66836ba719868f321ea571f960260b6ee885'; // замените на сгенерированный ключ
    $host = parse_url(BASE_URL, PHP_URL_HOST);
    $data = json_encode([
        'host' => $host,
        'key' => $key,
        'urlList' => [$url]
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

    @file_get_contents('https://api.indexnow.org/indexnow', false, $context);
}
