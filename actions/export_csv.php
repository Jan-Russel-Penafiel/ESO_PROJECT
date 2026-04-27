<?php
// Export filtered fines to CSV
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$from = getq('from', date('Y-m-01'));
$to   = getq('to',   date('Y-m-d'));

$rows = db_all("
  SELECT f.id, s.student_no, s.full_name, COALESCE(c.name,'Custom') AS category,
         f.reason, f.amount, f.status, f.issued_at, f.paid_at
  FROM fines f
  JOIN students s ON s.id = f.student_id
  LEFT JOIN fine_categories c ON c.id = f.category_id
  WHERE DATE(f.issued_at) BETWEEN ? AND ?
  ORDER BY f.issued_at DESC", [$from, $to]);

$filename = "eso_fines_{$from}_{$to}.csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Fine ID','Student No','Student Name','Category','Reason','Amount','Status','Issued','Paid']);
foreach ($rows as $r) fputcsv($out, $r);
fclose($out);
exit;
