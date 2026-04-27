<?php
// =====================================================
// Issue a new fine
// =====================================================
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
csrf_check();

$studentId  = (int)post('student_id');
$categoryId = post('category_id') !== '' ? (int)post('category_id') : null;
$reason     = post('reason');
$amount     = (float)post('amount');

if (!$studentId || $reason === '' || $amount <= 0) {
    flash('error', 'Please fill in student, reason and a positive amount.');
    redirect(APP_URL . '/admin/fines.php');
}

$me = current_user();
db_insert("INSERT INTO fines (student_id, category_id, amount, reason, status, issued_by)
           VALUES (?,?,?,?,'unpaid',?)", [$studentId, $categoryId, $amount, $reason, $me['id']]);
log_activity('fine_issue', "Issued fine of " . peso($amount) . " to student #{$studentId}");
flash('success', 'Fine issued successfully.');
redirect(APP_URL . '/admin/fines.php');
