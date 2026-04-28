<?php
// =====================================================
// Student · Print Receipt (TCPDF)
// =====================================================
require_once __DIR__ . '/../includes/auth.php';
require_login();

$payment_id = (int)($_GET['id'] ?? 0);
if (!$payment_id) { http_response_code(400); die('Invalid request.'); }

$user = current_user();
if (!in_array($user['role'], ['student', 'admin'], true)) {
    http_response_code(403); die('Access denied.');
}

$p = $user['role'] === 'student'
    ? db_one("
     SELECT p.*, f.reason, f.category_id,
         fc.name AS category_name,
         s.full_name, s.student_no, s.course, s.year_level, s.section
     FROM payments p
     JOIN fines f         ON f.id = p.fine_id
     LEFT JOIN fine_categories fc ON fc.id = f.category_id
     JOIN students s      ON s.id = p.student_id
     WHERE p.id = ? AND p.student_id = ?",
     [$payment_id, $user['student_id']]
      )
    : db_one("
     SELECT p.*, f.reason, f.category_id,
         fc.name AS category_name,
         s.full_name, s.student_no, s.course, s.year_level, s.section
     FROM payments p
     JOIN fines f         ON f.id = p.fine_id
     LEFT JOIN fine_categories fc ON fc.id = f.category_id
     JOIN students s      ON s.id = p.student_id
     WHERE p.id = ?",
     [$payment_id]
      );

if (!$p) { http_response_code(404); die('Receipt not found.'); }

require_once __DIR__ . '/../vendor/autoload.php';

// ── Register Dancing Script cursive font ─────────────
$font_ttf  = __DIR__ . '/../assets/fonts/DancingScript-Regular.ttf';
$font_dir  = __DIR__ . '/../vendor/tecnickcom/tcpdf/fonts/';
$cursive_key = TCPDF_FONTS::addTTFfont($font_ttf, 'TrueTypeUnicode', '', 96, $font_dir);

// ── PDF setup ────────────────────────────────────────
class ReceiptPDF extends TCPDF {
    public function Header() {}
    public function Footer() {}
}

// 80 mm wide thermal receipt; height auto via AutoPageBreak
$pdf = new ReceiptPDF('P', 'mm', [80, 220], true, 'UTF-8');
$pdf->SetCreator(APP_NAME);
$pdf->SetAuthor('ESO Office');
$pdf->SetTitle('Receipt - ' . $p['reference_no']);
$pdf->SetMargins(4, 4, 4);
$pdf->SetAutoPageBreak(true, 4);
$pdf->SetFont('helvetica', '', 7);
$pdf->AddPage();

// ── Data prep ────────────────────────────────────────
$date_str  = fdate($p['created_at'], 'M d, Y  h:i A');
$method    = strtoupper($p['payment_method']);
$amount    = number_format((float)$p['amount'], 2);
$status    = strtoupper($p['status']);
$dots      = str_repeat('. ', 26); // dotted separator row

// ── Helper: draw a dotted separator line ─────────────
function dotline($pdf) {
    $pdf->SetFont('courier', '', 6);
    $pdf->SetTextColor(170, 170, 170);
    $pdf->Cell(0, 3, str_repeat('. ', 28), 0, 1, 'C');
    $pdf->SetTextColor(30, 30, 30);
}

// ═══════════════════════════════════════════
// HEADER — Shop name block
// ═══════════════════════════════════════════
$pdf->SetFont('courier', 'B', 12);
$pdf->SetTextColor(30, 30, 30);
$pdf->Cell(0, 6, 'ESO OFFICE', 0, 1, 'C');

$pdf->SetFont('courier', '', 7);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 3.5, 'Isulan Campus, Sultan Kudarat State University', 0, 1, 'C');
$pdf->Ln(1);

dotline($pdf);

// ═══════════════════════════════════════════
// TITLE
// ═══════════════════════════════════════════
$pdf->SetFont('courier', 'B', 9);
$pdf->SetTextColor(30, 30, 30);
$pdf->Cell(0, 5, 'CASH RECEIPT', 0, 1, 'C');
dotline($pdf);

// ── Item rows ────────────────────────────────────────
// Nature of collection
$pdf->SetFont('courier', '', 7);
$pdf->SetTextColor(30, 30, 30);
$pdf->Cell(45, 4, 'Nature of Collection', 0, 0, 'L');
$pdf->Cell(0,  4, '',                     0, 1, 'R');
$pdf->SetFont('courier', 'I', 7);
$pdf->SetTextColor(60, 60, 60);
$pdf->Cell(45, 3.5, '  ' . $p['reason'], 0, 0, 'L');
$pdf->Cell(0,  3.5, 'PHP ' . $amount,    0, 1, 'R');
$pdf->SetTextColor(30, 30, 30);

$pdf->Ln(0.5);

// Payer
$pdf->SetFont('courier', '', 7);
$pdf->Cell(45, 4, 'Payer',              0, 0, 'L');
$pdf->Cell(0,  4, $p['full_name'],      0, 1, 'R');

// Student No
$pdf->Cell(45, 4, 'Student No.',        0, 0, 'L');
$pdf->Cell(0,  4, $p['student_no'],     0, 1, 'R');

// Campus
$pdf->Cell(45, 4, 'Campus',             0, 0, 'L');
$pdf->Cell(0,  4, 'Isulan Campus',      0, 1, 'R');

// Date
$pdf->Cell(45, 4, 'Date',               0, 0, 'L');
$pdf->Cell(0,  4, $date_str,            0, 1, 'R');

// Reference No.
$pdf->Cell(45, 4, 'Reference No.',      0, 0, 'L');
$pdf->Cell(0,  4, $p['reference_no'],   0, 1, 'R');

dotline($pdf);

// ═══════════════════════════════════════════
// TOTAL block
// ═══════════════════════════════════════════
$pdf->SetFont('courier', 'B', 9);
$pdf->Cell(45, 5, 'Total',           0, 0, 'L');
$pdf->Cell(0,  5, 'PHP ' . $amount, 0, 1, 'R');

$pdf->Ln(0.5);

// Payment details
$pdf->SetFont('courier', '', 7);
$pdf->Cell(45, 4, 'Method',             0, 0, 'L');
$pdf->Cell(0,  4, $method,              0, 1, 'R');

// Status
$sc = _status_rgb($p['status']);
$pdf->SetFont('courier', 'B', 7);
$pdf->SetTextColor($sc[0], $sc[1], $sc[2]);
$pdf->Cell(45, 4, 'Status',             0, 0, 'L');
$pdf->Cell(0,  4, $status,              0, 1, 'R');
$pdf->SetTextColor(30, 30, 30);

dotline($pdf);

// ═══════════════════════════════════════════
// SIGNATURE
// ═══════════════════════════════════════════
$pdf->Ln(2);
$pdf->SetFont('courier', '', 6.5);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 3.5, 'Authorized by:', 0, 1, 'C');

// Cursive "Admin" via Dancing Script
$pdf->SetFont($cursive_key, '', 18);
$pdf->SetTextColor(20, 70, 40);
$pdf->Cell(0, 8, 'Admin', 0, 1, 'C');

// Signature underline
$pdf->SetDrawColor(80, 80, 80);
$y = $pdf->GetY();
$pdf->Line(20, $y, 60, $y);
$pdf->Ln(1);

$pdf->SetFont('courier', '', 6.5);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 3.5, 'ESO Officer / Authorized Signature', 0, 1, 'C');
$pdf->Ln(3);

// ── Output ───────────────────────────────────────────
$force_download = isset($_GET['download']) && $_GET['download'] === '1';
$pdf->IncludeJS('print(true);');
$filename = 'receipt-' . $p['reference_no'] . '.pdf';
$pdf->Output($filename, $force_download ? 'D' : 'I');
exit;

// ── Helpers ──────────────────────────────────────────
function _status_rgb($s) {
    return [
        'initiated' => [100, 116, 139],
        'pending'   => [180,  93,   9],
        'success'   => [ 21, 128,  61],
        'failed'    => [220,  38,  38],
    ][$s] ?? [30, 30, 30];
}
