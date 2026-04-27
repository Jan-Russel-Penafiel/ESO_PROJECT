<?php
// =====================================================
// Authentication / role guards
// =====================================================
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/** Currently logged-in user (or null) */
function current_user() {
    if (empty($_SESSION['user_id'])) return null;
    static $cache = null;
    if ($cache !== null && $cache['id'] === (int)$_SESSION['user_id']) return $cache;
    $cache = db_one('SELECT * FROM users WHERE id = ? AND is_active = 1', [$_SESSION['user_id']]);
    return $cache ?: null;
}

/** Get the student profile linked to the current user (or null) */
function current_student() {
    $u = current_user();
    if (!$u || !$u['student_id']) return null;
    return db_one('SELECT * FROM students WHERE id = ?', [$u['student_id']]);
}

/** Idle-timeout enforcement */
function check_session_timeout() {
    if (!current_user()) return;
    $now = time();
    $limit = SESSION_TIMEOUT_MINUTES * 60;
    if (isset($_SESSION['_last_activity']) && ($now - $_SESSION['_last_activity']) > $limit) {
        session_unset();
        session_destroy();
        redirect(APP_URL . '/index.php?expired=1');
    }
    $_SESSION['_last_activity'] = $now;
}

/** Require any logged-in user */
function require_login() {
    check_session_timeout();
    if (!current_user()) redirect(APP_URL . '/index.php');
}

/** Require a specific role */
function require_role($role) {
    require_login();
    $u = current_user();
    if ($u['role'] !== $role) {
        http_response_code(403);
        die('Access denied.');
    }
}

/** Log an action */
function log_activity($action, $description = '') {
    $uid = $_SESSION['user_id'] ?? null;
    db_exec(
        'INSERT INTO activity_logs (user_id, action, description) VALUES (?, ?, ?)',
        [$uid, $action, $description]
    );
}
