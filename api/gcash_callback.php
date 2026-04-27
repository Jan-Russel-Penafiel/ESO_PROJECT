<?php
// =====================================================
// Simulated GCash bridge / callback page.
//
// In a real GCash merchant integration this would be:
//   - POSTed to by GCash with a signed webhook, OR
//   - GET-redirected to with a "result=success" param.
//
// For this academic build we present a friendly bridge
// page that shows the payment summary and lets the
// student confirm the transfer. It also offers to
// open the GCash app via deep link.
//
// On confirmation it flips the payment to "pending"
// (admin must verify) OR directly to "success" if the
// AUTO_CONFIRM constant is true (demo mode).
// =====================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

const AUTO_CONFIRM = true; // demo: instantly mark success on user click

$ref = getq('ref');
$pay = $ref ? db_one('SELECT p.*, f.reason, s.full_name FROM payments p
                      JOIN fines f ON f.id = p.fine_id
                      JOIN students s ON s.id = p.student_id
                      WHERE p.reference_no = ?', [$ref]) : null;

if (!$pay) { http_response_code(404); die('Invalid payment reference.'); }

// Handle confirmation submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'confirm') {
    $gcashRef = 'GC-' . strtoupper(bin2hex(random_bytes(5)));
    if (AUTO_CONFIRM) {
        db_exec("UPDATE payments SET status='success', gcash_ref=?, paid_at=NOW() WHERE id=?", [$gcashRef, $pay['id']]);
        db_exec("UPDATE fines SET status='paid', paid_at=NOW() WHERE id=?", [$pay['fine_id']]);
        db_exec('INSERT INTO activity_logs (user_id, action, description) VALUES (NULL, ?, ?)',
                ['gcash_auto_success', "Auto-confirmed payment {$pay['reference_no']} (GCash {$gcashRef})"]);
        $done = 'success';
    } else {
        db_exec("UPDATE payments SET status='pending', gcash_ref=? WHERE id=?", [$gcashRef, $pay['id']]);
        db_exec('INSERT INTO activity_logs (user_id, action, description) VALUES (NULL, ?, ?)',
                ['gcash_pending', "Marked payment {$pay['reference_no']} pending (GCash {$gcashRef})"]);
        $done = 'pending';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GCash Payment · <?= e($pay['reference_no']) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-gradient-to-br from-sky-600 to-sky-800 min-h-screen flex items-center justify-center p-4">

<div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">

  <!-- Pseudo GCash header -->
  <div class="bg-sky-600 text-white text-center py-5">
    <div class="bg-white/20 inline-flex items-center justify-center w-14 h-14 rounded-full mb-2">
      <i class="bi bi-wallet2 text-2xl"></i>
    </div>
    <h1 class="text-lg font-bold">GCash Payment Bridge</h1>
    <p class="text-sky-100 text-xs">Confirm your transfer</p>
  </div>

  <div class="p-6 space-y-4">

    <?php if (isset($done) && $done === 'success'): ?>
      <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 p-4 rounded text-center">
        <i class="bi bi-check-circle text-3xl"></i>
        <p class="font-bold mt-2">Payment Successful</p>
        <p class="text-xs">Your fine has been settled. You may now close this window.</p>
      </div>

    <?php elseif (isset($done) && $done === 'pending'): ?>
      <div class="bg-amber-100 border border-amber-300 text-amber-800 p-4 rounded text-center">
        <i class="bi bi-hourglass-split text-3xl"></i>
        <p class="font-bold mt-2">Payment Submitted</p>
        <p class="text-xs">Awaiting admin verification. You may close this window.</p>
      </div>

    <?php else: ?>
      <dl class="text-sm space-y-2">
        <div class="flex justify-between border-b pb-2"><dt class="text-slate-500">Pay To</dt><dd><strong><?= e(GCASH_MERCHANT_NAME) ?></strong></dd></div>
        <div class="flex justify-between border-b pb-2"><dt class="text-slate-500">GCash Number</dt><dd class="font-mono"><?= e(GCASH_NUMBER) ?></dd></div>
        <div class="flex justify-between border-b pb-2"><dt class="text-slate-500">Reference</dt><dd class="font-mono text-xs"><?= e($pay['reference_no']) ?></dd></div>
        <div class="flex justify-between border-b pb-2"><dt class="text-slate-500">From</dt><dd><?= e($pay['full_name']) ?></dd></div>
        <div class="flex justify-between border-b pb-2"><dt class="text-slate-500">Reason</dt><dd><?= e($pay['reason']) ?></dd></div>
        <div class="flex justify-between text-lg font-bold pt-1"><dt>Amount</dt><dd class="text-sky-700"><?= peso($pay['amount']) ?></dd></div>
      </dl>

      <a href="<?= e(GCASH_DEEPLINK) ?>"
         class="block w-full bg-sky-600 hover:bg-sky-700 text-white text-center font-semibold py-2.5 rounded-lg">
        <i class="bi bi-phone"></i> Open GCash App
      </a>

      <form method="POST" class="space-y-2">
        <input type="hidden" name="action" value="confirm">
        <button type="submit"
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg">
          <i class="bi bi-check2-circle"></i> I Have Sent the Payment
        </button>
        <p class="text-xs text-slate-400 text-center">By confirming, you authorize ESO to verify and post this transfer.</p>
      </form>
    <?php endif; ?>

  </div>
</div>

</body>
</html>
