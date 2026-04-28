<?php
// =====================================================
// Admin verifies a pending GCash payment as SUCCESS.
// This also flips the linked fine to PAID.
// =====================================================
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
if (!hash_equals($_SESSION['_csrf'] ?? '', getq('_csrf'))) { http_response_code(419); die('CSRF token mismatch.'); }

$id = (int)getq('id');
$pay = db_one('SELECT * FROM payments WHERE id = ?', [$id]);
if (!$pay) { flash('error','Payment not found.'); redirect(APP_URL.'/admin/payments.php'); }

try {
    db()->beginTransaction();
    db_exec("UPDATE payments SET status='success', paid_at=NOW() WHERE id=?", [$id]);
    db_exec("UPDATE fines SET status='paid', paid_at=NOW() WHERE id=?", [$pay['fine_id']]);
    db()->commit();
    log_activity('payment_verify', "Verified payment #{$id}");
    flash('success', 'Payment verified and fine marked as paid.');
} catch (PDOException $e) {
    db()->rollBack();
    flash('error', 'Verification failed: ' . $e->getMessage());
}
redirect(APP_URL . '/admin/payments.php');
