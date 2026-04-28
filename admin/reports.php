<?php
// =====================================================
// Admin · Reports (filterable + CSV export)
// =====================================================
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$pageTitle = 'Reports';

$from = getq('from', date('Y-m-01'));
$to   = getq('to',   date('Y-m-d'));

// Summary by status
$summary = db_all("
    SELECT status,
           COUNT(*) AS cnt,
           COALESCE(SUM(amount),0) AS total
    FROM fines
    WHERE DATE(issued_at) BETWEEN ? AND ?
    GROUP BY status", [$from, $to]);

// Top categories
$topCategories = db_all("
    SELECT COALESCE(c.name, 'Uncategorized') AS name,
           COUNT(*) AS cnt,
           COALESCE(SUM(f.amount),0) AS total
    FROM fines f
    LEFT JOIN fine_categories c ON c.id = f.category_id
    WHERE DATE(f.issued_at) BETWEEN ? AND ?
    GROUP BY c.id, c.name
    ORDER BY total DESC LIMIT 10", [$from, $to]);

// Top offenders
$topStudents = db_all("
    SELECT s.student_no, s.full_name, COUNT(*) AS cnt, COALESCE(SUM(f.amount),0) AS total
    FROM fines f
    JOIN students s ON s.id = f.student_id
    WHERE DATE(f.issued_at) BETWEEN ? AND ?
    GROUP BY s.id ORDER BY total DESC LIMIT 10", [$from, $to]);

// Daily collection
$daily = db_all("
    SELECT DATE(paid_at) AS day, COALESCE(SUM(amount),0) AS total
    FROM payments
    WHERE status='success' AND DATE(paid_at) BETWEEN ? AND ?
    GROUP BY DATE(paid_at) ORDER BY day DESC", [$from, $to]);

include __DIR__ . '/../templates/header.php';
include __DIR__ . '/../templates/sidebar.php';
?>

<div class="mb-6">
  <h1 class="text-2xl font-bold text-emerald-800 mb-3"><i class="bi bi-graph-up"></i> Reports</h1>
  <form method="GET" class="flex flex-wrap gap-2 text-sm items-end">
    <div class="flex gap-2 flex-wrap items-end">
      <div>
        <label class="block text-xs text-slate-500">From</label>
        <input type="date" name="from" value="<?= e($from) ?>" class="border rounded p-1 w-36">
      </div>
      <div>
        <label class="block text-xs text-slate-500">To</label>
        <input type="date" name="to" value="<?= e($to) ?>" class="border rounded p-1 w-36">
      </div>
    </div>
    <div class="flex gap-2">
      <button class="bg-emerald-600 text-white px-3 py-1.5 rounded"><i class="bi bi-funnel"></i> Apply</button>
      <a href="<?= APP_URL ?>/actions/export_xlsx.php?from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>"
         class="bg-emerald-700 text-white px-3 py-1.5 rounded"><i class="bi bi-file-earmark-excel"></i> Export XLSX</a>
    </div>
  </form>
</div>

<!-- Summary -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
<?php
$colors = ['unpaid'=>'red','pending'=>'amber','paid'=>'emerald','cancelled'=>'slate'];
foreach (['unpaid','pending','paid','cancelled'] as $st):
    $row = ['cnt'=>0,'total'=>0];
    foreach ($summary as $r) if ($r['status'] === $st) $row = $r;
    $c = $colors[$st]; ?>
  <div class="bg-white border-l-4 border-<?= $c ?>-500 rounded shadow p-4">
    <p class="text-xs text-slate-500 uppercase"><?= ucfirst($st) ?></p>
    <p class="text-2xl font-bold text-<?= $c ?>-700"><?= peso($row['total']) ?></p>
    <p class="text-xs text-slate-500"><?= $row['cnt'] ?> fine(s)</p>
  </div>
<?php endforeach; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

  <div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b font-semibold text-emerald-700"><i class="bi bi-bar-chart"></i> Top Categories</div>
    <!-- Desktop table -->
    <div class="overflow-x-auto desktop-table">
    <table class="w-full text-sm min-w-[280px]">
      <thead class="bg-emerald-50 text-emerald-800">
        <tr><th class="text-left p-2">Category</th><th class="text-right p-2">Count</th><th class="text-right p-2">Total</th></tr>
      </thead>
      <tbody>
      <?php foreach ($topCategories as $c): ?>
        <tr class="border-t">
          <td class="p-2"><?= e($c['name']) ?></td>
          <td class="p-2 text-right"><?= e($c['cnt']) ?></td>
          <td class="p-2 text-right font-mono"><?= peso($c['total']) ?></td>
        </tr>
      <?php endforeach; if (!$topCategories): ?>
        <tr><td colspan="3" class="p-4 text-center text-slate-400">No data.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    </div>
    <!-- Mobile cards -->
    <div class="mobile-cards">
      <?php if (!$topCategories): ?>
        <p class="text-center text-slate-400 py-4">No data.</p>
      <?php endif; ?>
      <?php foreach ($topCategories as $c): ?>
        <div class="record-card">
          <div class="card-row" style="margin-bottom:.35rem;">
            <span class="font-semibold text-slate-800"><?= e($c['name']) ?></span>
            <span class="font-mono font-semibold text-emerald-700"><?= peso($c['total']) ?></span>
          </div>
          <div class="card-row">
            <span class="card-label">Count</span>
            <span class="card-val"><?= e($c['cnt']) ?> fine(s)</span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b font-semibold text-emerald-700"><i class="bi bi-person-bounding-box"></i> Top Offenders</div>
    <!-- Desktop table -->
    <div class="overflow-x-auto desktop-table">
    <table class="w-full text-sm min-w-[280px]">
      <thead class="bg-emerald-50 text-emerald-800">
        <tr><th class="text-left p-2">Student</th><th class="text-right p-2">Count</th><th class="text-right p-2">Total</th></tr>
      </thead>
      <tbody>
      <?php foreach ($topStudents as $s): ?>
        <tr class="border-t">
          <td class="p-2"><?= e($s['full_name']) ?> <span class="text-xs text-slate-400">(<?= e($s['student_no']) ?>)</span></td>
          <td class="p-2 text-right"><?= e($s['cnt']) ?></td>
          <td class="p-2 text-right font-mono"><?= peso($s['total']) ?></td>
        </tr>
      <?php endforeach; if (!$topStudents): ?>
        <tr><td colspan="3" class="p-4 text-center text-slate-400">No data.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    </div>
    <!-- Mobile cards -->
    <div class="mobile-cards">
      <?php if (!$topStudents): ?>
        <p class="text-center text-slate-400 py-4">No data.</p>
      <?php endif; ?>
      <?php foreach ($topStudents as $s): ?>
        <div class="record-card">
          <div class="card-row" style="margin-bottom:.35rem;">
            <div>
              <div class="font-semibold text-slate-800"><?= e($s['full_name']) ?></div>
              <div class="text-xs text-slate-400"><?= e($s['student_no']) ?></div>
            </div>
            <span class="font-mono font-semibold text-emerald-700"><?= peso($s['total']) ?></span>
          </div>
          <div class="card-row">
            <span class="card-label">Fines</span>
            <span class="card-val"><?= e($s['cnt']) ?> fine(s)</span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="bg-white rounded-lg shadow">
  <div class="p-4 border-b font-semibold text-emerald-700"><i class="bi bi-calendar-week"></i> Daily Collection</div>
  <!-- Desktop table -->
  <div class="overflow-x-auto desktop-table">
  <table class="w-full text-sm min-w-[280px]">
    <thead class="bg-emerald-50 text-emerald-800">
      <tr><th class="text-left p-2">Date</th><th class="text-right p-2">Collected</th></tr>
    </thead>
    <tbody>
    <?php foreach ($daily as $d): ?>
      <tr class="border-t">
        <td class="p-2"><?= e(fdate($d['day'], 'D · M d, Y')) ?></td>
        <td class="p-2 text-right font-mono text-emerald-700"><?= peso($d['total']) ?></td>
      </tr>
    <?php endforeach; if (!$daily): ?>
      <tr><td colspan="2" class="p-4 text-center text-slate-400">No collections recorded.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
  <!-- Mobile cards -->
  <div class="mobile-cards">
    <?php if (!$daily): ?>
      <p class="text-center text-slate-400 py-4">No collections recorded.</p>
    <?php endif; ?>
    <?php foreach ($daily as $d): ?>
      <div class="record-card">
        <div class="card-row">
          <span class="card-label">Date</span>
          <span class="font-medium text-slate-700"><?= e(fdate($d['day'], 'D · M d, Y')) ?></span>
        </div>
        <div class="card-row">
          <span class="card-label">Collected</span>
          <span class="font-mono font-semibold text-emerald-700"><?= peso($d['total']) ?></span>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
