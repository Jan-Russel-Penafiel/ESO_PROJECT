<?php
// =====================================================
// Admin Dashboard — KPIs + recent activity
// =====================================================
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$pageTitle = 'Admin Dashboard';

// --- KPI queries ---
$totalStudents = db_one('SELECT COUNT(*) c FROM students')['c'];
$totalFines    = db_one('SELECT COUNT(*) c, COALESCE(SUM(amount),0) a FROM fines');
$paidFines     = db_one("SELECT COUNT(*) c, COALESCE(SUM(amount),0) a FROM fines WHERE status='paid'");
$unpaidFines   = db_one("SELECT COUNT(*) c, COALESCE(SUM(amount),0) a FROM fines WHERE status='unpaid'");
$pendingFines  = db_one("SELECT COUNT(*) c FROM fines WHERE status='pending'");

$recentPayments = db_all("
    SELECT p.*, s.full_name, s.student_no
    FROM payments p
    JOIN students s ON s.id = p.student_id
    ORDER BY p.created_at DESC LIMIT 8");

$recentFines = db_all("
    SELECT f.*, s.full_name, s.student_no
    FROM fines f
    JOIN students s ON s.id = f.student_id
    ORDER BY f.issued_at DESC LIMIT 8");

include __DIR__ . '/../templates/header.php';
include __DIR__ . '/../templates/sidebar.php';
?>

<h1 class="text-2xl font-bold text-emerald-800 mb-6">Welcome back, Admin</h1>

<!-- KPI cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

  <div class="bg-white border-l-4 border-emerald-600 rounded-lg shadow p-4">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-xs text-slate-500 uppercase">Students</p>
        <p class="text-2xl font-bold text-emerald-700"><?= e($totalStudents) ?></p>
      </div>
      <i class="bi bi-people text-3xl text-emerald-300"></i>
    </div>
  </div>

  <div class="bg-white border-l-4 border-amber-500 rounded-lg shadow p-4">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-xs text-slate-500 uppercase">Total Fines</p>
        <p class="text-2xl font-bold text-amber-600"><?= e($totalFines['c']) ?></p>
        <p class="text-xs text-slate-500"><?= peso($totalFines['a']) ?></p>
      </div>
      <i class="bi bi-cash-coin text-3xl text-amber-300"></i>
    </div>
  </div>

  <div class="bg-white border-l-4 border-green-600 rounded-lg shadow p-4">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-xs text-slate-500 uppercase">Collected</p>
        <p class="text-2xl font-bold text-green-700"><?= peso($paidFines['a']) ?></p>
        <p class="text-xs text-slate-500"><?= e($paidFines['c']) ?> paid</p>
      </div>
      <i class="bi bi-check-circle text-3xl text-green-300"></i>
    </div>
  </div>

  <div class="bg-white border-l-4 border-red-600 rounded-lg shadow p-4">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-xs text-slate-500 uppercase">Outstanding</p>
        <p class="text-2xl font-bold text-red-700"><?= peso($unpaidFines['a']) ?></p>
        <p class="text-xs text-slate-500"><?= e($unpaidFines['c']) ?> unpaid · <?= e($pendingFines['c']) ?> pending</p>
      </div>
      <i class="bi bi-exclamation-triangle text-3xl text-red-300"></i>
    </div>
  </div>
</div>

<!-- Two-column tables -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

  <div class="bg-white rounded-lg shadow">
    <div class="px-4 py-3 border-b flex items-center justify-between">
      <h2 class="font-semibold text-emerald-700"><i class="bi bi-cash-coin"></i> Recent Fines</h2>
      <a href="<?= APP_URL ?>/admin/fines.php" class="text-xs text-emerald-600 hover:underline">View all</a>
    </div>
    <div class="overflow-x-auto">
    <table class="w-full text-sm min-w-[380px]">
      <thead class="bg-emerald-50 text-emerald-800">
        <tr><th class="text-left p-2">Student</th><th class="text-left p-2">Reason</th><th class="text-right p-2">Amount</th><th class="p-2">Status</th></tr>
      </thead>
      <tbody>
        <?php foreach ($recentFines as $f): ?>
          <tr class="border-t">
            <td class="p-2"><?= e($f['full_name']) ?><br><span class="text-xs text-slate-400"><?= e($f['student_no']) ?></span></td>
            <td class="p-2"><?= e($f['reason']) ?></td>
            <td class="p-2 text-right"><?= peso($f['amount']) ?></td>
            <td class="p-2 text-center"><?php
              $cls = ['unpaid'=>'bg-red-100 text-red-700','pending'=>'bg-amber-100 text-amber-700','paid'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-slate-100 text-slate-600'][$f['status']];
              echo '<span class="text-xs px-2 py-1 rounded ' . $cls . '">' . e(ucfirst($f['status'])) . '</span>';
            ?></td>
          </tr>
        <?php endforeach; if (!$recentFines): ?>
          <tr><td colspan="4" class="text-center text-slate-400 p-4">No fines yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>

  <div class="bg-white rounded-lg shadow">
    <div class="px-4 py-3 border-b flex items-center justify-between">
      <h2 class="font-semibold text-emerald-700"><i class="bi bi-credit-card-2-back"></i> Recent Payments</h2>
      <a href="<?= APP_URL ?>/admin/payments.php" class="text-xs text-emerald-600 hover:underline">View all</a>
    </div>
    <div class="overflow-x-auto">
    <table class="w-full text-sm min-w-[380px]">
      <thead class="bg-emerald-50 text-emerald-800">
        <tr><th class="text-left p-2">Reference</th><th class="text-left p-2">Student</th><th class="text-right p-2">Amount</th><th class="p-2">Status</th></tr>
      </thead>
      <tbody>
        <?php foreach ($recentPayments as $p): ?>
          <tr class="border-t">
            <td class="p-2 font-mono text-xs"><?= e($p['reference_no']) ?></td>
            <td class="p-2"><?= e($p['full_name']) ?></td>
            <td class="p-2 text-right"><?= peso($p['amount']) ?></td>
            <td class="p-2 text-center"><?php
              $cls = ['initiated'=>'bg-slate-100 text-slate-700','pending'=>'bg-amber-100 text-amber-700','success'=>'bg-emerald-100 text-emerald-700','failed'=>'bg-red-100 text-red-700'][$p['status']];
              echo '<span class="text-xs px-2 py-1 rounded ' . $cls . '">' . e(ucfirst($p['status'])) . '</span>';
            ?></td>
          </tr>
        <?php endforeach; if (!$recentPayments): ?>
          <tr><td colspan="4" class="text-center text-slate-400 p-4">No payments yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
