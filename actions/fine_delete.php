<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
if (!hash_equals($_SESSION['_csrf'] ?? '', getq('_csrf'))) { http_response_code(419); die('CSRF token mismatch.'); }
$id = (int)getq('id');
if ($id) {
    db_exec('DELETE FROM fines WHERE id = ?', [$id]);
    log_activity('fine_delete', "Deleted fine #{$id}");
    flash('success', 'Fine deleted.');
}
redirect(APP_URL . '/admin/fines.php');
