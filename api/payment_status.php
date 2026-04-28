<?php
// =====================================================
// Returns JSON status for a given payment reference.
// =====================================================
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');

$ref = getq('ref');
if ($ref === '') {
    echo json_encode(['ok' => false, 'error' => 'missing ref']); exit;
}

$pay = db_one('SELECT id, status, paid_at, amount FROM payments WHERE reference_no = ?', [$ref]);
if (!$pay) {
    echo json_encode(['ok' => false, 'error' => 'not found']); exit;
}

echo json_encode([
    'ok'        => true,
    'status'    => $pay['status'],
    'amount'    => (float)$pay['amount'],
    'paid_at'   => $pay['paid_at'],
]);
