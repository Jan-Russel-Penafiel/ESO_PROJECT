<?php
// Admin marks a stuck payment as FAILED, fine reverts to UNPAID
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
if (!hash_equals($_SESSION['_csrf'] ?? '', getq('_csrf'))) { http_response_code(419); die('CSRF token mismatch.'); }

$id = (int)getq('id');
$pay = db_one('SELECT * FROM payments WHERE id = ?', [$id]);
if (!$pay) { flash('error','Payment not found.'); redirect(APP_URL.'/admin/payments.php'); }

try {
    db()->beginTransaction();
    db_exec("UPDATE payments SET status='failed' WHERE id=?", [$id]);
    db_exec("UPDATE fines    SET status='unpaid' WHERE id=? AND status='pending'", [$pay['fine_id']]);
    db()->commit();
    log_activity('payment_fail', "Failed payment #{$id}");
    flash('success', 'Payment marked as failed.');
} catch (PDOException $e) {
    db()->rollBack();
    flash('error', 'Update failed: ' . $e->getMessage());
}
redirect(APP_URL . '/admin/payments.php');
