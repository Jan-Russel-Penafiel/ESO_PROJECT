<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

// CSRF check via querystring (link-based delete)
if (!hash_equals($_SESSION['_csrf'] ?? '', getq('_csrf'))) {
    http_response_code(419); die('CSRF token mismatch.');
}

$id = (int)getq('id');
if ($id) {
    db_exec('DELETE FROM users    WHERE student_id = ?', [$id]);
    db_exec('DELETE FROM students WHERE id = ?',         [$id]);
    log_activity('student_delete', "Deleted student #{$id}");
    flash('success', 'Student deleted.');
}
redirect(APP_URL . '/admin/students.php');
