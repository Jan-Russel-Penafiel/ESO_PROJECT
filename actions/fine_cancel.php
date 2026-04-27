<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
if (!hash_equals($_SESSION['_csrf'] ?? '', getq('_csrf'))) { http_response_code(419); die('CSRF token mismatch.'); }
$id = (int)getq('id');
if ($id) {
    db_exec("UPDATE fines SET status='cancelled' WHERE id=? AND status='unpaid'", [$id]);
    log_activity('fine_cancel', "Cancelled fine #{$id}");
    flash('success', 'Fine cancelled.');
}
redirect(APP_URL . '/admin/fines.php');
