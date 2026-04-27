<?php
// =====================================================
// Admin · Monitor Payments
// =====================================================
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$pageTitle = 'Payments';

$status = getq('status');
$where  = ''; $params = [];
if (in_array($status, ['initiated','pending','success','failed'], true)) {
    $where = 'WHERE p.status = ?'; $params[] = $status;
}

$payments = db_all("
    SELECT p.*, s.full_name, s.student_no, f.reason, f.amount AS fine_amount
    FROM payments p
    JOIN students s ON s.id = p.student_id
    JOIN fines    f ON f.id = p.fine_id
    $where
    ORDER BY p.created_at DESC", $params);

$totals = db_one("
    SELECT
      COALESCE(SUM(CASE WHEN status='success' THEN amount END),0) AS collected,
      COALESCE(SUM(CASE WHEN status IN ('initiated','pending') THEN amount END),0) AS in_flight,
      COUNT(*) AS total
    FROM payments");

include __DIR__ . '/../templates/header.php';
include __DIR__ . '/../templates/sidebar.php';
?>

<h1 class="text-2xl font-bold text-emerald-800 mb-6"><i class="bi bi-credit-card-2-back"></i> Payments Monitoring</h1>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
  <div class="bg-white border-l-4 border-emerald-600 rounded shadow p-4">
    <p class="text-xs text-slate-500 uppercase">Collected</p>
    <p class="text-2xl font-bold text-emerald-700"><?= peso($totals['collected']) ?></p>
  </div>
  <div class="bg-white border-l-4 border-amber-500 rounded shadow p-4">
    <p class="text-xs text-slate-500 uppercase">In Flight</p>
    <p class="text-2xl font-bold text-amber-600"><?= peso($totals['in_flight']) ?></p>
  </div>
  <div class="bg-white border-l-4 border-sky-500 rounded shadow p-4">
    <p class="text-xs text-slate-500 uppercase">Transactions</p>
    <p class="text-2xl font-bold text-sky-700"><?= e($totals['total']) ?></p>
  </div>
</div>

<div class="bg-white rounded-lg shadow">
  <div class="p-4 border-b flex items-center justify-between flex-wrap gap-2">
    <h2 class="font-semibold text-emerald-700">All Payments (<?= count($payments) ?>)</h2>
    <form method="GET">
      <select name="status" onchange="this.form.submit()" class="border rounded px-2 py-1 text-sm">
        <option value="">All Status</option>
        <?php foreach (['initiated','pending','success','failed'] as $st): ?>
          <option value="<?= $st ?>" <?= $status===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm min-w-[720px]">
      <thead class="bg-emerald-50 text-emerald-800">
        <tr>
          <th class="text-left p-2">Reference</th>
          <th class="text-left p-2">Student</th>
          <th class="text-left p-2">Fine</th>
          <th class="text-right p-2">Amount</th>
          <th class="p-2">Method</th>
          <th class="p-2">Status</th>
          <th class="p-2">GCash Ref</th>
          <th class="p-2">Created</th>
          <th class="p-2">Action</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($payments as $p): ?>
        <tr class="border-t hover:bg-emerald-50/40">
          <td class="p-2 font-mono text-xs"><?= e($p['reference_no']) ?></td>
          <td class="p-2"><?= e($p['full_name']) ?><br><span class="text-xs text-slate-400"><?= e($p['student_no']) ?></span></td>
          <td class="p-2 text-xs"><?= e($p['reason']) ?></td>
          <td class="p-2 text-right font-mono"><?= peso($p['amount']) ?></td>
          <td class="p-2 text-center text-xs"><?= e($p['payment_method']) ?></td>
          <td class="p-2 text-center"><?php
            $cls = ['initiated'=>'bg-slate-100 text-slate-700','pending'=>'bg-amber-100 text-amber-700','success'=>'bg-emerald-100 text-emerald-700','failed'=>'bg-red-100 text-red-700'][$p['status']];
            echo '<span class="text-xs px-2 py-1 rounded ' . $cls . '">' . e(ucfirst($p['status'])) . '</span>';
          ?></td>
          <td class="p-2 font-mono text-xs"><?= e($p['gcash_ref'] ?: '—') ?></td>
          <td class="p-2 text-xs text-slate-500"><?= e(fdate($p['created_at'], 'M d, h:i A')) ?></td>
          <td class="p-2 text-center text-xs">
            <?php if (in_array($p['status'], ['initiated','pending'], true)): ?>
              <a href="<?= APP_URL ?>/actions/payment_verify.php?id=<?= $p['id'] ?>&_csrf=<?= csrf_token() ?>"
                 onclick="return confirm('Verify and mark this payment SUCCESS?')"
                 class="text-emerald-600"><i class="bi bi-check2-circle"></i> Verify</a>
              <a href="<?= APP_URL ?>/actions/payment_fail.php?id=<?= $p['id'] ?>&_csrf=<?= csrf_token() ?>"
                 onclick="return confirm('Mark this payment FAILED?')"
                 class="text-red-600 ml-2"><i class="bi bi-x-circle"></i> Fail</a>
            <?php else: ?>
              <span class="text-slate-400">—</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; if (!$payments): ?>
        <tr><td colspan="9" class="p-4 text-center text-slate-400">No payment records.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
