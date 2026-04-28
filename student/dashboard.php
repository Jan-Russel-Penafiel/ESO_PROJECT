<?php
// =====================================================
// Student · Dashboard with real-time fine status
// =====================================================
require_once __DIR__ . '/../includes/auth.php';
require_role('student');
$pageTitle = 'My Dashboard';

$student = current_student();
if (!$student) { flash('error', 'No student profile linked to your account.'); redirect(APP_URL . '/actions/logout.php'); }

$kpi = db_one("
  SELECT
    COALESCE(SUM(CASE WHEN status='unpaid'  THEN amount END),0) AS unpaid,
    COALESCE(SUM(CASE WHEN status='pending' THEN amount END),0) AS pending,
    COALESCE(SUM(CASE WHEN status='paid'    THEN amount END),0) AS paid,
    COUNT(*) AS total
  FROM fines WHERE student_id = ?", [$student['id']]);

$activeFines = db_all("
  SELECT f.*, c.name AS category_name
  FROM fines f LEFT JOIN fine_categories c ON c.id = f.category_id
  WHERE f.student_id = ? AND f.status IN ('unpaid','pending')
  ORDER BY f.issued_at DESC", [$student['id']]);

include __DIR__ . '/../templates/header.php';
include __DIR__ . '/../templates/sidebar.php';
?>

<div class="bg-gradient-to-r from-emerald-600 to-emerald-700 text-white rounded-xl p-6 mb-6 shadow">
  <h1 class="text-2xl font-bold">Hello, <?= e($student['full_name']) ?></h1>
  <p class="text-emerald-100 text-sm mt-1">
    <?= e($student['student_no']) ?> · <?= e($student['course'] . ' ' . $student['year_level'] . '-' . $student['section']) ?>
  </p>
</div>

<!-- Real-time KPIs (auto-refresh every 15s) -->
<div id="kpiBox" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
  <div class="bg-white border-l-4 border-red-600 rounded shadow p-4">
    <p class="text-xs text-slate-500 uppercase">Unpaid</p>
    <p class="text-2xl font-bold text-red-700"><?= peso($kpi['unpaid']) ?></p>
  </div>
  <div class="bg-white border-l-4 border-amber-500 rounded shadow p-4">
    <p class="text-xs text-slate-500 uppercase">Pending</p>
    <p class="text-2xl font-bold text-amber-600"><?= peso($kpi['pending']) ?></p>
  </div>
  <div class="bg-white border-l-4 border-emerald-600 rounded shadow p-4">
    <p class="text-xs text-slate-500 uppercase">Paid</p>
    <p class="text-2xl font-bold text-emerald-700"><?= peso($kpi['paid']) ?></p>
  </div>
  <div class="bg-white border-l-4 border-slate-400 rounded shadow p-4">
    <p class="text-xs text-slate-500 uppercase">Total Fines</p>
    <p class="text-2xl font-bold text-slate-700"><?= e($kpi['total']) ?></p>
  </div>
</div>

<div class="bg-white rounded-lg shadow">
  <div class="p-4 border-b font-semibold text-emerald-700">
    <i class="bi bi-exclamation-circle"></i> Active Fines
  </div>
  <!-- Desktop table -->
  <div class="overflow-x-auto desktop-table">
  <table class="w-full text-sm min-w-[480px]">
    <thead class="bg-emerald-50 text-emerald-800">
      <tr>
        <th class="text-left p-2">Reason</th>
        <th class="text-right p-2">Amount</th>
        <th class="p-2">Status</th>
        <th class="p-2">Date Issued</th>
        <th class="p-2">Pay</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($activeFines as $f): ?>
      <tr class="border-t hover:bg-emerald-50/40">
        <td class="p-2"><?= e($f['reason']) ?> <span class="text-xs text-slate-400">· <?= e($f['category_name'] ?? 'Custom') ?></span></td>
        <td class="p-2 text-right font-mono"><?= peso($f['amount']) ?></td>
        <td class="p-2 text-center"><?php
          $cls = ['unpaid'=>'bg-red-100 text-red-700','pending'=>'bg-amber-100 text-amber-700'][$f['status']] ?? 'bg-slate-100 text-slate-700';
          echo '<span class="text-xs px-2 py-1 rounded ' . $cls . '">' . e(ucfirst($f['status'])) . '</span>';
        ?></td>
        <td class="p-2 text-xs text-slate-500"><?= e(fdate($f['issued_at'], 'M d, Y')) ?></td>
        <td class="p-2 text-center">
          <?php if ($f['status'] === 'unpaid'): ?>
            <a href="<?= APP_URL ?>/student/pay.php?fine_id=<?= $f['id'] ?>"
               class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs px-3 py-1 rounded">
              <i class="bi bi-qr-code"></i> Pay via GCash
            </a>
          <?php else: ?>
            <span class="text-xs text-amber-600">Awaiting verification</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; if (!$activeFines): ?>
      <tr><td colspan="5" class="p-6 text-center text-slate-400">
        <i class="bi bi-emoji-smile text-2xl text-emerald-400"></i><br>
        No active fines. You're all clear!
      </td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
  <!-- Mobile cards -->
  <div class="mobile-cards">
    <?php if (!$activeFines): ?>
      <div class="text-center text-slate-400 py-6">
        <i class="bi bi-emoji-smile" style="font-size:2rem;color:#34d399;display:block;margin-bottom:.5rem;"></i>
        No active fines. You're all clear!
      </div>
    <?php endif; ?>
    <?php foreach ($activeFines as $f):
      $cls = ['unpaid'=>'bg-red-100 text-red-700','pending'=>'bg-amber-100 text-amber-700'][$f['status']] ?? 'bg-slate-100 text-slate-700';
    ?>
      <div class="record-card">
        <div class="card-row" style="margin-bottom:.45rem;">
          <div>
            <div class="font-semibold text-slate-800"><?= e($f['reason']) ?></div>
            <div class="text-xs text-slate-400"><?= e($f['category_name'] ?? 'Custom') ?></div>
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
        <div class="card-actions">
          <?php if ($f['status'] === 'unpaid'): ?>
            <a href="<?= APP_URL ?>/student/pay.php?fine_id=<?= $f['id'] ?>"
               class="inline-flex items-center gap-1 bg-emerald-600 text-white text-xs px-3 py-1.5 rounded">
              <i class="bi bi-qr-code"></i> Pay via GCash
            </a>
          <?php else: ?>
            <span class="text-xs text-amber-600"><i class="bi bi-hourglass-split"></i> Awaiting verification</span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

<script>
  // Refresh page silently every 15s to reflect status changes
  setTimeout(() => location.reload(), 15000);
</script>

<?php include __DIR__ . '/../templates/footer.php'; ?>
