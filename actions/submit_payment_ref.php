<?php
// =====================================================
// Student submits their GCash reference number after
// paying. Marks the payment as 'pending' for admin
// to verify.
// =====================================================
require_once __DIR__ . '/../includes/auth.php';
require_role('student');
csrf_check();

$student   = current_student();
$paymentId = (int)post('payment_id');
$fineId    = (int)post('fine_id');
$gcashRef  = trim(post('gcash_ref'));

if ($gcashRef === '') {
    flash('error', 'Please enter your GCash reference number.');
    redirect(APP_URL . '/student/pay.php?fine_id=' . $fineId);
}

if (!preg_match('/^\d{13}$/', $gcashRef)) {
    flash('error', 'GCash reference number must be exactly 13 digits.');
    redirect(APP_URL . '/student/pay.php?fine_id=' . $fineId);
}

// Verify ownership: payment must belong to this student and still be 'initiated'
$payment = db_one("
    SELECT * FROM payments
    WHERE id = ? AND student_id = ? AND status = 'initiated'", [$paymentId, $student['id']]);

if (!$payment) {
    flash('error', 'Payment not found or already submitted.');
    redirect(APP_URL . '/student/dashboard.php');
}

// Save the GCash reference and advance to 'pending'
db_exec("UPDATE payments SET gcash_ref = ?, status = 'pending' WHERE id = ?",
        [$gcashRef, $paymentId]);

// Fine stays 'pending' so admin queue picks it up
db_exec("UPDATE fines SET status = 'pending' WHERE id = ? AND status IN ('unpaid','pending')",
        [$payment['fine_id']]);

log_activity('payment_ref_submitted',
    "Student submitted GCash ref '{$gcashRef}' for payment #{$paymentId}");

flash('success', 'Reference number submitted! Awaiting admin verification.');
redirect(APP_URL . '/student/pay.php?fine_id=' . $fineId);
