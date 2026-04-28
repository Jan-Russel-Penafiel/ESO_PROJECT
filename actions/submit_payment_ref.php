<?php
// =====================================================
// Student submits their GCash receipt screenshot.
// Marks the payment as 'pending' for admin to verify.
// =====================================================
require_once __DIR__ . '/../includes/auth.php';
require_role('student');
csrf_check();

$student   = current_student();
$paymentId = (int)post('payment_id');
$fineId    = (int)post('fine_id');

// Verify ownership: payment must belong to this student and still be 'initiated'
$payment = db_one("
    SELECT * FROM payments
    WHERE id = ? AND student_id = ? AND status = 'initiated'", [$paymentId, $student['id']]);

if (!$payment) {
    flash('error', 'Payment not found or already submitted.');
    redirect(APP_URL . '/student/dashboard.php');
}

// Handle receipt upload
if (empty($_FILES['receipt']['tmp_name'])) {
    flash('error', 'Please upload your GCash receipt screenshot.');
    redirect(APP_URL . '/student/pay.php?fine_id=' . $fineId);
}

$file     = $_FILES['receipt'];
$maxBytes = 5 * 1024 * 1024; // 5 MB
$allowed  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    flash('error', 'Upload error. Please try again.');
    redirect(APP_URL . '/student/pay.php?fine_id=' . $fineId);
}
if ($file['size'] > $maxBytes) {
    flash('error', 'Receipt image must be under 5 MB.');
    redirect(APP_URL . '/student/pay.php?fine_id=' . $fineId);
}

// Detect MIME from actual file content (not user-supplied type)
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!isset($allowed[$mime])) {
    flash('error', 'Only JPG, PNG, or WEBP images are accepted.');
    redirect(APP_URL . '/student/pay.php?fine_id=' . $fineId);
}

$uploadDir = __DIR__ . '/../uploads/receipts/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$filename = 'rcpt_' . $paymentId . '_' . time() . '.' . $allowed[$mime];
$dest     = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    flash('error', 'Could not save the receipt. Please try again.');
    redirect(APP_URL . '/student/pay.php?fine_id=' . $fineId);
}

// Save path and advance to 'pending'
$receiptPath = 'uploads/receipts/' . $filename;
db_exec("UPDATE payments SET receipt_path = ?, status = 'pending' WHERE id = ?",
        [$receiptPath, $paymentId]);

// Fine stays 'pending' so admin queue picks it up
db_exec("UPDATE fines SET status = 'pending' WHERE id = ? AND status IN ('unpaid','pending')",
        [$payment['fine_id']]);

log_activity('payment_ref_submitted',
    "Student uploaded GCash receipt for payment #{$paymentId}");

flash('success', 'Receipt submitted! Awaiting admin verification.');
redirect(APP_URL . '/student/pay.php?fine_id=' . $fineId);
