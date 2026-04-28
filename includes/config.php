<?php
// =====================================================
// Application configuration
// Adjust DB credentials to match your XAMPP MySQL setup
// =====================================================

// --- Database ---
define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_NAME', 'eso_fines');
define('DB_USER', 'root');
define('DB_PASS', '');

// --- App ---
define('APP_NAME', 'ESO Fines Management System');
define('APP_URL',  (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/fine');
define('TIMEZONE', 'Asia/Manila');

// --- GCash (simulated) ---
// In production, replace with real GCash merchant keys / endpoints.
define('GCASH_MERCHANT_NAME', 'ESO OFFICE');
define('GCASH_NUMBER',        '09920157536');
define('GCASH_DEEPLINK',      'gcash://app');           // mobile deep link
define('GCASH_WEB_FALLBACK',  'https://www.gcash.com'); // browser fallback

// --- Session ---
define('SESSION_TIMEOUT_MINUTES', 30);

// --- Boot ---
date_default_timezone_set(TIMEZONE);
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start a single, hardened session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    session_start();
}
