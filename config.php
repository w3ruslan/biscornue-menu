<?php
define('SHOP_NAME',    'La Biscornue');
define('SHOP_TAGLINE', 'Restaurant grec · Oudon');
define('SHOP_PHONE',   '+33 X XX XX XX XX');
define('SHOP_ADDRESS', 'Oudon, France');
define('ADMIN_PASS',   'biscornue2024');

define('DB_HOST', 'localhost');
define('DB_NAME', 'u870017612_biscornue');
define('DB_USER', 'u870017612_biscornue');
define('DB_PASS', '1234566Ruslan-');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $pdo = new PDO(
        'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    return $pdo;
}
