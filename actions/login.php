<?php
// =====================================================
// Login handler — verifies credentials, opens session
// =====================================================
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_URL . '/index.php');
}
csrf_check();

$login = post('login');
$pass  = post('password');

if ($login === '' || $pass === '') {
    flash('error', 'Please enter both username and password.');
    redirect(APP_URL . '/index.php');
}

// Look up by username OR email
$user = db_one(
    'SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1',
    [$login, $login]
);

if (!$user || !password_verify($pass, $user['password'])) {
    flash('error', 'Invalid credentials.');
    redirect(APP_URL . '/index.php');
}

// Open session
session_regenerate_id(true);
$_SESSION['user_id']        = (int)$user['id'];
$_SESSION['_last_activity'] = time();

log_activity('login', 'User logged in: ' . $user['username']);

redirect(APP_URL . ($user['role'] === 'admin' ? '/admin/dashboard.php' : '/student/dashboard.php'));
