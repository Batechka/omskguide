<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_URL', 'http://localhost/omsk/'); // измените на свой домен
define('UPLOAD_DIR', __DIR__ . '/../uploads/attractions/');
define('UPLOAD_URL', BASE_URL . 'uploads/attractions/');
define('ROUTES_UPLOAD_URL', BASE_URL . 'uploads/routes/');


$db_host = 'localhost';
$db_name = 'omsk';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Ошибка подключения к БД: ' . $e->getMessage());
}

// Определение языка
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'ru'; // по умолчанию русский
}
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ru'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'];

// Загрузка языковых строк интерфейса
$lang_file = __DIR__ . "/../lang/{$lang}.php";
if (file_exists($lang_file)) {
    $messages = include $lang_file;
} else {
    $messages = [];
}

function __($key)
{
    global $messages;
    return $messages[$key] ?? $key;
}
