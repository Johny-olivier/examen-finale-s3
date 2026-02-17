<?php
date_default_timezone_set('UTC');

error_reporting(E_ALL);

// Character encoding
if (function_exists('mb_internal_encoding') === true) {
	mb_internal_encoding('UTF-8');
}

// Default Locale Change as needed or feel free to remove.
if (function_exists('setlocale') === true) {
	setlocale(LC_ALL, 'en_US.UTF-8');
}

// Get the $app var to use below
if (empty($app) === true) {
	$app = Flight::app();
}

// Refer to this constant to get the project root directory
if (!defined('PROJECT_ROOT')) {
	$racineProjet = realpath(__DIR__ . '/../..');
	define('PROJECT_ROOT', $racineProjet !== false ? $racineProjet : __DIR__ . '/../..');
}

$app->path(PROJECT_ROOT);

require_once __DIR__ . "/constant.php";

// Core config variables
$app->set('flight.base_url', BASE_URL);           // Base URL for your app. Change if app is in a subdirectory (e.g., '/myapp/')
$app->set('flight.case_sensitive', false);    // Set true for case sensitive routes. Default: false
$app->set('flight.log_errors', true);         // Log errors to file. Recommended: true in production
$app->set('flight.handle_errors', false);     // Let Tracy handle errors if false. Set true to use Flight's error handler
$app->set('flight.views.path', PROJECT_ROOT . '/app/views'); // Path to views/templates
$app->set('flight.views.extension', '.php');  // View file extension (e.g., '.php', '.latte')
$app->set('flight.content_length', false);    // Send content length header. Usually false unless required by proxy

// Generate a CSP nonce for each request and store in $app
$nonce = bin2hex(random_bytes(16));
$app->set('csp_nonce', $nonce);

return [
	'database' => [
		// MySQL Example:
		'host'     => '127.0.0.1',      // Database host (e.g., 'localhost', 'db.example.com')
		'dbname'   => 'bngrc_db',   // Database name (e.g., 'flightphp')
		'user'     => 'root',  // Database user (e.g., 'root')
		'password' => '',  // Database password (never commit real passwords)
	],
];
