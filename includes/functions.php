<?php
// =====================================================
// Generic helpers (escaping, redirects, flash messages)
// =====================================================
require_once __DIR__ . '/config.php';

/** Safe HTML output */
function e($val) {
    return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
}

/** Format peso amount */
function peso($n) {
    return '₱' . number_format((float)$n, 2);
}

/** Friendly date */
function fdate($d, $fmt = 'M d, Y h:i A') {
    if (!$d) return '—';
    $ts = is_numeric($d) ? (int)$d : strtotime($d);
    return $ts ? date($fmt, $ts) : '—';
}

/** Safe redirect */
function redirect($path) {
    header('Location: ' . $path);
    exit;
}

/** Set flash message displayed once on next page */
function flash($type, $message) {
    $_SESSION['_flash'][] = ['type' => $type, 'msg' => $message];
}

/** Pop and return all flash messages */
function flash_pull() {
    $items = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $items;
}

/** CSRF token handling */
function csrf_token() {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}
function csrf_field() {
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}
function csrf_check() {
    $sent = $_POST['_csrf'] ?? '';
    if (!hash_equals($_SESSION['_csrf'] ?? '', $sent)) {
        http_response_code(419);
        die('CSRF token mismatch.');
    }
}

/** Quick post helper with default */
function post($key, $default = '') {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}
function getq($key, $default = '') {
    return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}

/** Generate a unique payment reference number */
function generate_reference() {
    return 'ESO-' . strtoupper(bin2hex(random_bytes(4))) . '-' . date('ymdHis');
}

/** Active sidebar link helper */
function is_active($file) {
    return basename($_SERVER['SCRIPT_NAME']) === $file
        ? 'bg-emerald-700 text-white'
        : 'text-emerald-50 hover:bg-emerald-600/40';
}
