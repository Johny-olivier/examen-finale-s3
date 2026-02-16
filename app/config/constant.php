<?php

if (PHP_SAPI === 'cli') {
    define('BASE_URL', '/');
    return;
}

// Permet de forcer une URL de base sur un hebergement FTP si necessaire.
$urlBaseForcee = getenv('APP_BASE_URL');
if ($urlBaseForcee !== false && trim($urlBaseForcee) !== '') {
    define('BASE_URL', rtrim(trim($urlBaseForcee), '/') . '/');
    return;
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

$scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
$basePath = '/' . trim(dirname($scriptName), '/');
if ($basePath === '/.') {
    $basePath = '/';
}

$baseUrl = $scheme . '://' . $host;
if ($basePath === '/') {
    define('BASE_URL', $baseUrl . '/');
    return;
}

define('BASE_URL', $baseUrl . rtrim($basePath, '/') . '/');
