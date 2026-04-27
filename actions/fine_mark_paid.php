<?php
// Manual mark-as-paid (e.g. cash payment over the counter)
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
if (!hash_equals($_SESSION['_csrf'] ?? '', getq('_csrf'))) { http_response_code(419); die('CSRF token mismatch.'); }

$id = (int)getq('id');
$fine = db_one('SELECT * FROM fines WHERE id = ?', [$id]);
if (!$fine) { flash('error','Fine not found.'); redirect(APP_URL.'/admin/fines.php'); }

try {
    db()->beginTransaction();
    db_exec("UPDATE fines SET status='paid', paid_at=NOW() WHERE id=?", [$id]);

    // Insert a manual payment row so the audit trail is complete
    $ref = generate_reference();
    db_insert("INSERT INTO payments (fine_id, student_id, amount, reference_no, payment_method, status, paid_at)
               VALUES (?,?,?,?,'CASH','success', NOW())",
        [$id, $fine['student_id'], $fine['amount'], $ref]);
    db()->commit();
    log_activity('fine_mark_paid', "Manual paid fine #{$id}");
    flash('success', 'Fine marked as paid.');
} catch (PDOException $e) {
    db()->rollBack();
    flash('error', 'Update failed: ' . $e->getMessage());
}
redirect(APP_URL . '/admin/fines.php');
