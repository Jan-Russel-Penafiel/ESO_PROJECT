<?php
// =====================================================
// Student · Pay a Fine via GCash (static InstaPay QR)
//
// Flow:
//  1. Read fine, validate ownership and unpaid status.
//  2. Create or reuse an 'initiated' payment row.
//  3. Show the static gcash.jpg InstaPay QR + amount.
//  4. Student pays via GCash and uploads
//     the receipt screenshot.
//  5. Receipt saved → payment becomes 'pending'.
//  6. Admin verifies and approves or disapproves.
// =====================================================
require_once __DIR__ . '/../includes/auth.php';
require_role('student');
$pageTitle = 'Pay Fine';

$student = current_student();
$fineId  = (int)getq('fine_id');

$fine = db_one("
  SELECT f.id, f.student_id, f.category_id, f.amount, f.reason, f.status, f.issued_at,
         c.name AS category_name
  FROM fines f LEFT JOIN fine_categories c ON c.id = f.category_id
  WHERE f.id = ? AND f.student_id = ?", [$fineId, $student['id']]);

if (!$fine) { flash('error', 'Fine not found.'); redirect(APP_URL . '/student/dashboard.php'); }
if ($fine['status'] === 'paid')      { flash('info',    'This fine is already paid.');    redirect(APP_URL . '/student/dashboard.php'); }
if ($fine['status'] === 'cancelled') { flash('warning', 'This fine was cancelled.');      redirect(APP_URL . '/student/dashboard.php'); }

// Reuse existing in-flight payment or create a fresh one
$payment = db_one("
  SELECT id, fine_id, student_id, amount, reference_no, payment_method,
    status, qr_payload, created_at, paid_at
  FROM payments
  WHERE fine_id = ? AND status IN ('initiated','pending')
  ORDER BY id DESC LIMIT 1", [$fineId]);

if (!$payment) {
    $ref = generate_reference();
    $pid = db_insert("
        INSERT INTO payments (fine_id, student_id, amount, reference_no, payment_method, status)
        VALUES (?, ?, ?, ?, 'GCASH', 'initiated')",
        [$fineId, $student['id'], $fine['amount'], $ref]);
    $payment = db_one('SELECT id, fine_id, student_id, amount, reference_no, payment_method, status, qr_payload, created_at, paid_at FROM payments WHERE id = ?', [$pid]);
    log_activity('payment_initiated', "Payment {$payment['reference_no']} for fine F-{$fineId}");
}

include __DIR__ . '/../templates/header.php';
include __DIR__ . '/../templates/sidebar.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
  <h1 class="text-2xl font-bold text-emerald-800"><i class="bi bi-qr-code"></i> Pay via GCash</h1>
  <a href="<?= APP_URL ?>/student/dashboard.php"
     class="inline-flex items-center gap-1 text-sm text-slate-500 hover:underline self-start sm:self-auto">
    <i class="bi bi-arrow-left"></i> Back to Dashboard
  </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

  <!-- Left: Fine summary + instructions -->
  <div class="bg-white rounded-lg shadow p-6 space-y-5">

    <div>
      <h2 class="font-semibold text-emerald-700 mb-3"><i class="bi bi-receipt"></i> Fine Summary</h2>
      <dl class="text-sm space-y-2">
        <div class="flex justify-between border-b pb-2"><dt class="text-slate-500">System Ref</dt><dd class="font-mono text-xs"><?= e($payment['reference_no']) ?></dd></div>
        <div class="flex justify-between border-b pb-2"><dt class="text-slate-500">Reason</dt><dd><?= e($fine['reason']) ?></dd></div>
        <div class="flex justify-between border-b pb-2"><dt class="text-slate-500">Category</dt><dd><?= e($fine['category_name'] ?? 'Custom') ?></dd></div>
        <div class="flex justify-between border-b pb-2"><dt class="text-slate-500">Issued</dt><dd><?= e(fdate($fine['issued_at'])) ?></dd></div>
        <div class="flex justify-between text-xl font-bold pt-2"><dt>Total Due</dt><dd class="text-emerald-700"><?= peso($fine['amount']) ?></dd></div>
      </dl>
    </div>

    <div class="bg-sky-50 border border-sky-200 rounded-lg p-4 text-sm space-y-1">
      <p class="font-semibold text-sky-800"><i class="bi bi-info-circle"></i> How to pay:</p>
      <ol class="list-decimal list-inside text-sky-700 space-y-1 text-xs mt-1">
        <li>Open <strong>GCash</strong> → tap <strong>Pay QR</strong></li>
        <li>Scan the <strong>InstaPay QR code</strong> on the right</li>
        <li>Enter <strong><?= peso($fine['amount']) ?></strong> as the amount</li>
        <li>Confirm the transfer to <strong><?= e(GCASH_MERCHANT_NAME) ?></strong></li>
        <li>Save a screenshot of your <strong>GCash receipt</strong></li>
        <li>Upload it below and click <strong>Submit</strong></li>
      </ol>
    </div>

    <!-- Receipt upload form / pending state -->
    <?php if ($payment['status'] === 'initiated'): ?>
      <form action="<?= APP_URL ?>/actions/submit_payment_ref.php" method="POST" enctype="multipart/form-data" class="space-y-3">
        <?= csrf_field() ?>
        <input type="hidden" name="payment_id" value="<?= $payment['id'] ?>">
        <input type="hidden" name="fine_id"    value="<?= $fine['id'] ?>">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">
            GCash Receipt Screenshot <span class="text-red-500">*</span>
          </label>
          <div id="dropZone"
               class="border-2 border-dashed border-slate-300 rounded-lg p-4 text-center cursor-pointer hover:border-emerald-400 hover:bg-emerald-50 transition-colors">
            <i class="bi bi-cloud-upload text-2xl text-slate-400" id="dropIcon"></i>
            <p class="text-sm text-slate-500 mt-1" id="dropText">Click or drag &amp; drop your receipt image here</p>
            <p class="text-xs text-slate-400 mt-0.5">JPG, PNG, or WEBP · max 5 MB</p>
            <input type="file" name="receipt" id="receiptFile" required accept="image/jpeg,image/png,image/webp"
                   class="hidden">
          </div>
          <!-- Preview -->
          <div id="previewWrap" class="hidden mt-2">
            <img id="previewImg" src="" alt="Receipt preview" class="rounded-lg border max-h-48 mx-auto">
            <p id="previewName" class="text-xs text-center text-slate-500 mt-1"></p>
          </div>
        </div>
        <button type="submit"
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg flex items-center justify-center gap-2">
          <i class="bi bi-send"></i> Submit Receipt
        </button>
      </form>

    <?php elseif ($payment['status'] === 'pending'): ?>
      <div class="bg-amber-50 border border-amber-300 rounded-lg p-4 text-sm">
        <p class="font-semibold text-amber-800"><i class="bi bi-hourglass-split"></i> Receipt Submitted — Awaiting Verification</p>
        <p class="text-amber-700 mt-1">Your GCash receipt has been submitted. The admin is reviewing your payment.</p>
        <p class="text-xs text-amber-600 mt-2">This page refreshes automatically every 10 seconds.</p>
      </div>
    <?php endif; ?>

  </div>

  <!-- Right: Static InstaPay QR -->
  <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center text-center">
    <h2 class="font-semibold text-emerald-700 mb-1"><i class="bi bi-qr-code-scan"></i> Scan with GCash</h2>

    <div class="border-4 border-emerald-600 rounded-xl p-2 bg-white shadow-inner">
      <img src="<?= APP_URL ?>/gcash.jpg" alt="InstaPay GCash QR" class="w-64 h-64 object-contain">
    </div>

    <div class="mt-4 space-y-1">
      <p class="text-sm font-semibold text-slate-700"><?= e(GCASH_MERCHANT_NAME) ?></p>
      <p class="text-emerald-700 font-bold text-lg"><?= e(GCASH_NUMBER) ?></p>
      <p class="text-2xl font-bold text-slate-800"><?= peso($fine['amount']) ?></p>
    </div>
  </div>
</div>

<?php if ($payment['status'] === 'initiated'): ?>
<script>
  (function () {
    const zone    = document.getElementById('dropZone');
    const input   = document.getElementById('receiptFile');
    const wrap    = document.getElementById('previewWrap');
    const img     = document.getElementById('previewImg');
    const name    = document.getElementById('previewName');
    const icon    = document.getElementById('dropIcon');
    const text    = document.getElementById('dropText');

    zone.addEventListener('click', () => input.click());

    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('border-emerald-400','bg-emerald-50'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('border-emerald-400','bg-emerald-50'));
    zone.addEventListener('drop', e => {
      e.preventDefault();
      zone.classList.remove('border-emerald-400','bg-emerald-50');
      if (e.dataTransfer.files.length) { input.files = e.dataTransfer.files; showPreview(e.dataTransfer.files[0]); }
    });

    input.addEventListener('change', () => { if (input.files.length) showPreview(input.files[0]); });

    function showPreview(file) {
      const reader = new FileReader();
      reader.onload = e => { img.src = e.target.result; };
      reader.readAsDataURL(file);
      name.textContent = file.name;
      wrap.classList.remove('hidden');
      icon.className = 'bi bi-check-circle text-2xl text-emerald-500';
      text.textContent = 'Receipt selected — change by clicking above';
    }
  })();
</script>
<?php endif; ?>

<?php if ($payment['status'] === 'pending'): ?>
<script>
  // Auto-refresh every 10s so student sees approval without manual reload
  setTimeout(() => location.reload(), 10000);
</script>
<?php endif; ?>

<?php include __DIR__ . '/../templates/footer.php'; ?>
