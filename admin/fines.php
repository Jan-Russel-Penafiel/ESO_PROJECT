<?php
// =====================================================
// Admin · Issue & Manage Fines
// =====================================================
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$pageTitle = 'Manage Fines';

$students   = db_all('SELECT id, student_no, full_name FROM students ORDER BY full_name');
$categories = db_all('SELECT * FROM fine_categories WHERE is_active = 1 ORDER BY name');

// Filter
$status = getq('status');
$where  = '';
$params = [];
if (in_array($status, ['unpaid','pending','paid','cancelled'], true)) {
    $where = 'WHERE f.status = ?';
    $params[] = $status;
}

// Bring in the latest payment's gcash_ref + payment id for each fine
$fines = db_all("
    SELECT f.*, s.student_no, s.full_name, c.name AS category_name, u.username AS issuer,
           p.id AS payment_id, p.gcash_ref, p.reference_no AS pay_ref, p.status AS pay_status
    FROM fines f
    JOIN students s   ON s.id = f.student_id
    LEFT JOIN fine_categories c ON c.id = f.category_id
    JOIN users u      ON u.id = f.issued_by
    LEFT JOIN payments p ON p.id = (
        SELECT id FROM payments WHERE fine_id = f.id ORDER BY id DESC LIMIT 1
    )
    $where
    ORDER BY f.issued_at DESC", $params);

include __DIR__ . '/../templates/header.php';
include __DIR__ . '/../templates/sidebar.php';
?>

<h1 class="text-2xl font-bold text-emerald-800 mb-6"><i class="bi bi-cash-coin"></i> Fines</h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  <!-- Issue Fine -->
  <div class="bg-white rounded-lg shadow p-5 lg:col-span-1">
    <h2 class="font-semibold text-emerald-700 mb-3"><i class="bi bi-plus-circle"></i> Issue New Fine</h2>
    <form action="<?= APP_URL ?>/actions/fine_save.php" method="POST" class="space-y-3 text-sm">
      <?= csrf_field() ?>
      <div>
        <label class="block text-slate-600 mb-1">Student*</label>
        <select name="student_id" required class="w-full border rounded p-2">
          <option value="">— Select student —</option>
          <?php foreach ($students as $s): ?>
            <option value="<?= $s['id'] ?>"><?= e($s['student_no'] . ' · ' . $s['full_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block text-slate-600 mb-1">Category</label>
        <select name="category_id" id="catSelect" class="w-full border rounded p-2">
          <option value="">— Custom (no category) —</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>" data-amount="<?= $c['default_amount'] ?>" data-name="<?= e($c['name']) ?>">
              <?= e($c['name']) ?> (<?= peso($c['default_amount']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block text-slate-600 mb-1">Reason*</label>
        <input name="reason" id="reasonInput" required class="w-full border rounded p-2">
      </div>

      <div>
        <label class="block text-slate-600 mb-1">Amount (₱)*</label>
        <input name="amount" id="amountInput" type="number" min="1" step="0.01" required class="w-full border rounded p-2">
      </div>

      <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded font-semibold">
        <i class="bi bi-save"></i> Issue Fine
      </button>
    </form>
  </div>

  <!-- Fines List -->
  <div class="bg-white rounded-lg shadow lg:col-span-2">
    <div class="p-4 border-b flex items-center justify-between flex-wrap gap-2">
      <h2 class="font-semibold text-emerald-700">Issued Fines (<?= count($fines) ?>)</h2>
      <form method="GET" class="text-sm">
        <select name="status" onchange="this.form.submit()" class="border rounded px-2 py-1">
          <option value="">All Status</option>
          <?php foreach (['unpaid','pending','paid','cancelled'] as $st): ?>
            <option value="<?= $st ?>" <?= $status===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm min-w-[680px]">
        <thead class="bg-emerald-50 text-emerald-800">
          <tr>
            <th class="text-left p-2">#</th>
            <th class="text-left p-2">Student</th>
            <th class="text-left p-2">Reason</th>
            <th class="text-right p-2">Amount</th>
            <th class="p-2">Status</th>
            <th class="text-left p-2">GCash Ref</th>
            <th class="p-2">Issued</th>
            <th class="p-2">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($fines as $f): ?>
          <tr class="border-t hover:bg-emerald-50/40">
            <td class="p-2 text-xs text-slate-400">F-<?= e($f['id']) ?></td>
            <td class="p-2"><?= e($f['full_name']) ?><br><span class="text-xs text-slate-400"><?= e($f['student_no']) ?></span></td>
            <td class="p-2"><?= e($f['reason']) ?><br><span class="text-xs text-slate-400"><?= e($f['category_name']) ?></span></td>
            <td class="p-2 text-right font-mono"><?= peso($f['amount']) ?></td>
            <td class="p-2 text-center"><?php
              $cls = [
                'unpaid'    => 'bg-red-100 text-red-700',
                'pending'   => 'bg-amber-100 text-amber-700',
                'paid'      => 'bg-emerald-100 text-emerald-700',
                'cancelled' => 'bg-slate-100 text-slate-600',
              ][$f['status']];
              echo '<span class="text-xs px-2 py-1 rounded ' . $cls . '">' . e(ucfirst($f['status'])) . '</span>';
            ?></td>

            <!-- GCash Reference Number column -->
            <td class="p-2">
              <?php if ($f['gcash_ref']): ?>
                <span class="font-mono text-xs text-slate-700 bg-slate-100 px-1 py-0.5 rounded"><?= e($f['gcash_ref']) ?></span>
              <?php elseif ($f['pay_status'] === 'initiated'): ?>
                <span class="text-xs text-amber-500 italic">Awaiting ref…</span>
              <?php else: ?>
                <span class="text-xs text-slate-400">—</span>
              <?php endif; ?>
            </td>

            <td class="p-2 text-xs text-slate-500"><?= e(fdate($f['issued_at'], 'M d, Y')) ?></td>
            <td class="p-2 text-center text-xs">
              <a href="<?= APP_URL ?>/actions/fine_delete.php?id=<?= $f['id'] ?>&_csrf=<?= csrf_token() ?>"
                 onclick="return confirm('Delete this fine permanently?')" class="text-red-500">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; if (!$fines): ?>
          <tr><td colspan="8" class="p-4 text-center text-slate-400">No fines found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  // Auto-fill reason + amount when a category is chosen
  const cat    = document.getElementById('catSelect');
  const amt    = document.getElementById('amountInput');
  const reason = document.getElementById('reasonInput');
  cat.addEventListener('change', () => {
    const opt = cat.options[cat.selectedIndex];
    if (opt && opt.dataset.amount) {
      amt.value = opt.dataset.amount;
      if (!reason.value) reason.value = opt.dataset.name;
    }
  });
</script>

<?php include __DIR__ . '/../templates/footer.php'; ?>
