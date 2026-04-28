<?php
// =====================================================
// Student · All Fines (full list)
// =====================================================
require_once __DIR__ . '/../includes/auth.php';
require_role('student');
$pageTitle = 'My Fines';

$student = current_student();
$rows = db_all("
  SELECT f.*, c.name AS category_name
  FROM fines f LEFT JOIN fine_categories c ON c.id = f.category_id
  WHERE f.student_id = ?
  ORDER BY f.issued_at DESC", [$student['id']]);

include __DIR__ . '/../templates/header.php';
include __DIR__ . '/../templates/sidebar.php';
?>

<h1 class="text-2xl font-bold text-emerald-800 mb-6"><i class="bi bi-receipt"></i> All My Fines</h1>

<div class="bg-white rounded-lg shadow overflow-hidden">
  <!-- Desktop table -->
  <div class="overflow-x-auto desktop-table">
  <table class="w-full text-sm">
    <thead class="bg-emerald-50 text-emerald-800">
      <tr>
        <th class="text-left p-2">#</th>
        <th class="text-left p-2">Reason</th>
        <th class="text-left p-2">Category</th>
        <th class="text-right p-2">Amount</th>
        <th class="p-2">Status</th>
        <th class="p-2">Issued</th>
        <th class="p-2">Action</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $f): ?>
      <tr class="border-t hover:bg-emerald-50/40">
        <td class="p-2 text-xs text-slate-400">F-<?= e($f['id']) ?></td>
        <td class="p-2"><?= e($f['reason']) ?></td>
        <td class="p-2 text-xs text-slate-500"><?= e($f['category_name'] ?? '—') ?></td>
        <td class="p-2 text-right font-mono"><?= peso($f['amount']) ?></td>
        <td class="p-2 text-center"><?php
          $cls = ['unpaid'=>'bg-red-100 text-red-700','pending'=>'bg-amber-100 text-amber-700','paid'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-slate-100 text-slate-600'][$f['status']];
          echo '<span class="text-xs px-2 py-1 rounded ' . $cls . '">' . e(ucfirst($f['status'])) . '</span>';
        ?></td>
        <td class="p-2 text-xs text-slate-500"><?= e(fdate($f['issued_at'], 'M d, Y')) ?></td>
        <td class="p-2 text-center">
          <?php if ($f['status'] === 'unpaid'): ?>
            <a href="<?= APP_URL ?>/student/pay.php?fine_id=<?= $f['id'] ?>"
               class="text-emerald-600 text-xs"><i class="bi bi-qr-code"></i> Pay</a>
          <?php else: ?>—<?php endif; ?>
        </td>
      </tr>
    <?php endforeach; if (!$rows): ?>
      <tr><td colspan="7" class="p-4 text-center text-slate-400">No fines on record.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
  <!-- Mobile cards -->
  <div class="mobile-cards">
    <?php if (!$rows): ?>
      <p class="text-center text-slate-400 py-4">No fines on record.</p>
    <?php endif; ?>
    <?php foreach ($rows as $f):
      $cls = ['unpaid'=>'bg-red-100 text-red-700','pending'=>'bg-amber-100 text-amber-700','paid'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-slate-100 text-slate-600'][$f['status']];
    ?>
      <div class="record-card">
        <div class="card-row" style="margin-bottom:.45rem;">
          <div>
            <div class="font-semibold text-slate-800"><?= e($f['reason']) ?></div>
            <div class="text-xs text-slate-400"><?= e($f['category_name'] ?? '—') ?> · F-<?= e($f['id']) ?></div>
          </div>
          <span class="text-xs px-2 py-1 rounded <?= $cls ?>"><?= e(ucfirst($f['status'])) ?></span>
        </div>
        <div class="card-row">
          <span class="card-label">Amount</span>
          <span class="card-val font-mono font-semibold text-slate-800"><?= peso($f['amount']) ?></span>
        </div>
        <div class="card-row">
          <span class="card-label">Issued</span>
          <span class="card-val text-slate-500"><?= e(fdate($f['issued_at'], 'M d, Y')) ?></span>
        </div>
        <?php if ($f['status'] === 'unpaid'): ?>
          <div class="card-actions">
            <a href="<?= APP_URL ?>/student/pay.php?fine_id=<?= $f['id'] ?>"
               class="inline-flex items-center gap-1 bg-emerald-600 text-white text-xs px-3 py-1.5 rounded">
              <i class="bi bi-qr-code"></i> Pay via GCash
            </a>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
