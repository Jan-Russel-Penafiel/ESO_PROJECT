<?php
// =====================================================
// Returns JSON status for a given payment reference.
// Polled every few seconds by /student/pay.php so the
// page can react when GCash confirms the transfer.
// =====================================================
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');

$ref = getq('ref');
if ($ref === '') {
    echo json_encode(['ok' => false, 'error' => 'missing ref']); exit;
}

$pay = db_one('SELECT id, status, gcash_ref, paid_at, amount FROM payments WHERE reference_no = ?', [$ref]);
if (!$pay) {
    echo json_encode(['ok' => false, 'error' => 'not found']); exit;
}

echo json_encode([
    'ok'        => true,
    'status'    => $pay['status'],
    'gcash_ref' => $pay['gcash_ref'],
    'amount'    => (float)$pay['amount'],
    'paid_at'   => $pay['paid_at'],
]);
