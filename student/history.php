<?php
// =====================================================
// Student · Payment History
// =====================================================
require_once __DIR__ . '/../includes/auth.php';
require_role('student');
$pageTitle = 'Payment History';

$student = current_student();
$rows = db_all("
  SELECT p.*, f.reason
  FROM payments p
  JOIN fines f ON f.id = p.fine_id
  WHERE p.student_id = ?
  ORDER BY p.created_at DESC", [$student['id']]);

include __DIR__ . '/../templates/header.php';
include __DIR__ . '/../templates/sidebar.php';
?>

<h1 class="text-2xl font-bold text-emerald-800 mb-6"><i class="bi bi-clock-history"></i> Payment History</h1>

<div class="bg-white rounded-lg shadow overflow-hidden">
  <!-- Desktop table -->
  <div class="overflow-x-auto desktop-table">
  <table class="w-full text-sm">
    <thead class="bg-emerald-50 text-emerald-800">
      <tr>
        <th class="text-left p-2">Reference</th>
        <th class="text-left p-2">Fine</th>
        <th class="text-right p-2">Amount</th>
        <th class="p-2">Method</th>
        <th class="p-2">Status</th>
        <th class="p-2">Date</th>
        <th class="p-2">Action</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $p): ?>
      <?php $cls = ['initiated'=>'bg-slate-100 text-slate-700','pending'=>'bg-amber-100 text-amber-700','success'=>'bg-emerald-100 text-emerald-700','failed'=>'bg-red-100 text-red-700'][$p['status']]; ?>
      <tr class="border-t">
        <td class="p-2 font-mono text-xs"><?= e($p['reference_no']) ?></td>
        <td class="p-2"><?= e($p['reason']) ?></td>
        <td class="p-2 text-right font-mono"><?= peso($p['amount']) ?></td>
        <td class="p-2 text-center text-xs"><?= e($p['payment_method']) ?></td>
        <td class="p-2 text-center">
          <span class="text-xs px-2 py-1 rounded <?= $cls ?>"><?= e(ucfirst($p['status'])) ?></span>
        </td>
        <td class="p-2 text-xs text-slate-500"><?= e(fdate($p['created_at'])) ?></td>
        <td class="p-2 text-center">
          <?php if ($p['status'] === 'success'): ?>
          <a href="<?= APP_URL ?>/student/print_receipt.php?id=<?= $p['id'] ?>&download=1"
             target="_blank"
             class="inline-flex items-center gap-1 text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-2 py-1 rounded transition"
             title="Print Receipt">
            <i class="bi bi-printer"></i> Print
          </a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; if (!$rows): ?>
      <tr><td colspan="7" class="p-4 text-center text-slate-400">No payments yet.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
  <!-- Mobile cards -->
  <div class="mobile-cards">
    <?php if (!$rows): ?>
      <p class="text-center text-slate-400 py-4">No payments yet.</p>
    <?php endif; ?>
    <?php foreach ($rows as $p):
      $cls = ['initiated'=>'bg-slate-100 text-slate-700','pending'=>'bg-amber-100 text-amber-700','success'=>'bg-emerald-100 text-emerald-700','failed'=>'bg-red-100 text-red-700'][$p['status']];
    ?>
      <div class="record-card">
        <div class="card-row" style="margin-bottom:.45rem;">
          <div>
            <div class="font-semibold text-slate-800"><?= e($p['reason']) ?></div>
            <div class="font-mono text-xs text-slate-400"><?= e($p['reference_no']) ?></div>
          </div>
          <span class="text-xs px-2 py-1 rounded <?= $cls ?>"><?= e(ucfirst($p['status'])) ?></span>
        </div>
        <div class="card-row">
          <span class="card-label">Amount</span>
          <span class="card-val font-mono font-semibold text-slate-800"><?= peso($p['amount']) ?></span>
        </div>
        <div class="card-row">
          <span class="card-label">Method</span>
          <span class="card-val"><?= e($p['payment_method']) ?></span>
        </div>
        <div class="card-row">
          <span class="card-label">Date</span>
          <span class="card-val text-slate-500"><?= e(fdate($p['created_at'])) ?></span>
        </div>
        <?php if ($p['status'] === 'success'): ?>
        <div class="card-actions">
           <a href="<?= APP_URL ?>/student/print_receipt.php?id=<?= $p['id'] ?>&download=1"
             target="_blank"
             class="inline-flex items-center gap-1 text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded transition">
            <i class="bi bi-printer"></i> Print Receipt
          </a>
        </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
