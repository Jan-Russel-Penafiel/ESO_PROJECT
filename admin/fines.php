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
$status  = getq('status');
$course  = getq('course');
$wheres  = [];
$params  = [];
if (in_array($status, ['unpaid','pending','paid','cancelled'], true)) {
    $wheres[]  = 'f.status = ?';
    $params[]  = $status;
}
if (in_array($course, ['BSCPE','BSCE','BSECE'], true)) {
    $wheres[]  = 's.course = ?';
    $params[]  = $course;
}
$where = $wheres ? 'WHERE ' . implode(' AND ', $wheres) : '';

// Bring in the latest payment details for each fine
$fines = db_all("
    SELECT f.*, s.student_no, s.full_name, s.course AS student_course,
           c.name AS category_name, u.username AS issuer,
           p.id AS payment_id, p.receipt_path, p.reference_no AS pay_ref, p.status AS pay_status
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
      <div class="relative" id="studentPickerWrap">
        <label class="block text-slate-600 mb-1">Student*</label>
        <input type="hidden" name="student_id" id="studentId" required>
        <input type="text" id="studentSearch" autocomplete="off" placeholder="Type name or student no…"
               class="w-full border rounded p-2 text-sm focus:ring focus:ring-emerald-200 outline-none">
        <ul id="studentDropdown"
            class="absolute z-50 bg-white border border-slate-200 rounded shadow-lg w-full max-h-52 overflow-y-auto hidden text-sm">
          <?php foreach ($students as $s): ?>
            <li class="px-3 py-2 hover:bg-emerald-50 cursor-pointer"
                data-id="<?= $s['id'] ?>"
                data-label="<?= e($s['student_no'] . ' · ' . $s['full_name']) ?>">
              <?= e($s['student_no'] . ' · ' . $s['full_name']) ?>
            </li>
          <?php endforeach; ?>
        </ul>
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
    <div class="p-4 border-b">
      <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
        <h2 class="font-semibold text-emerald-700">Issued Fines (<?= count($fines) ?>)</h2>
        <form method="GET" class="text-sm flex items-center gap-2">
          <?php if ($course): ?><input type="hidden" name="course" value="<?= e($course) ?>"><?php endif; ?>
          <select name="status" onchange="this.form.submit()" class="border rounded px-2 py-1">
            <option value="">All Status</option>
            <?php foreach (['unpaid','pending','paid','cancelled'] as $st): ?>
              <option value="<?= $st ?>" <?= $status===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
            <?php endforeach; ?>
          </select>
        </form>
      </div>
      <!-- Course tabs -->
      <div class="flex items-center gap-2 text-xs font-medium overflow-x-auto flex-nowrap">
        <?php
        $courseFilters = ['' => 'All Courses', 'BSCPE' => 'BSCPE', 'BSCE' => 'BSCE', 'BSECE' => 'BSECE'];
        foreach ($courseFilters as $val => $label):
          $active = $course === $val;
          $href   = '?' . http_build_query(array_filter(['status' => $status, 'course' => $val]));
        ?>
           <a href="<?= $href ?>"
             class="px-3 py-1 rounded-full border whitespace-nowrap <?= $active ? 'bg-emerald-600 text-white border-emerald-600' : 'text-slate-600 border-slate-300 hover:bg-emerald-50' ?>">
            <?= $label ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    <!-- Desktop table -->
    <div class="overflow-x-auto desktop-table">
      <table class="w-full text-sm min-w-[680px]">
        <thead class="bg-emerald-50 text-emerald-800">
          <tr>
            <th class="text-left p-2">#</th>
            <th class="text-left p-2">Student</th>
            <th class="text-left p-2">Reason</th>
            <th class="text-right p-2">Amount</th>
            <th class="p-2">Status</th>
            <th class="p-2">Receipt</th>
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
            <td class="p-2 text-center">
              <?php if ($f['receipt_path']): ?>
                <button type="button" onclick="showReceipt('<?= APP_URL ?>/<?= e($f['receipt_path']) ?>')"
                        class="text-xs bg-sky-100 text-sky-700 hover:bg-sky-200 px-2 py-1 rounded border border-sky-200">
                  <i class="bi bi-image"></i> View
                </button>
              <?php elseif ($f['pay_status'] === 'initiated'): ?>
                <span class="text-xs text-amber-500 italic">Awaiting…</span>
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
    <!-- Mobile cards -->
    <div class="mobile-cards">
      <?php if (!$fines): ?>
        <p class="text-center text-slate-400 py-4">No fines found.</p>
      <?php endif; ?>
      <?php foreach ($fines as $f):
        $cls = ['unpaid'=>'bg-red-100 text-red-700','pending'=>'bg-amber-100 text-amber-700','paid'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-slate-100 text-slate-600'][$f['status']];
      ?>
        <div class="record-card">
          <div class="card-row" style="margin-bottom:.45rem;">
            <div>
              <div class="font-semibold text-slate-800"><?= e($f['full_name']) ?></div>
              <div class="text-xs text-slate-400"><?= e($f['student_no']) ?> · F-<?= e($f['id']) ?></div>
            </div>
            <span class="text-xs px-2 py-1 rounded <?= $cls ?>"><?= e(ucfirst($f['status'])) ?></span>
          </div>
          <div class="card-row">
            <span class="card-label">Reason</span>
            <span class="card-val"><?= e($f['reason']) ?><?= $f['category_name'] ? ' <span style="color:#94a3b8">· ' . e($f['category_name']) . '</span>' : '' ?></span>
          </div>
          <div class="card-row">
            <span class="card-label">Amount</span>
            <span class="card-val font-mono font-semibold text-slate-800"><?= peso($f['amount']) ?></span>
          </div>
          <div class="card-row">
            <span class="card-label">Receipt</span>
            <span class="card-val">
              <?php if ($f['receipt_path']): ?>
                <button type="button" onclick="showReceipt('<?= APP_URL ?>/<?= e($f['receipt_path']) ?>')"
                        class="text-xs bg-sky-100 text-sky-700 px-2 py-1 rounded border border-sky-200">
                  <i class="bi bi-image"></i> View
                </button>
              <?php elseif ($f['pay_status'] === 'initiated'): ?>
                <span class="text-amber-500 italic">Awaiting…</span>
              <?php else: ?>—<?php endif; ?>
            </span>
          </div>
          <div class="card-row">
            <span class="card-label">Issued</span>
            <span class="card-val text-slate-500"><?= e(fdate($f['issued_at'], 'M d, Y')) ?></span>
          </div>
          <div class="card-actions">
            <a href="<?= APP_URL ?>/actions/fine_delete.php?id=<?= $f['id'] ?>&_csrf=<?= csrf_token() ?>"
               onclick="return confirm('Delete this fine permanently?')"
               class="text-red-600 text-xs border border-red-200 px-2 py-1 rounded"><i class="bi bi-trash"></i> Delete</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
  // Searchable student picker
  (function () {
    const search = document.getElementById('studentSearch');
    const hidden = document.getElementById('studentId');
    const drop   = document.getElementById('studentDropdown');
    const items  = Array.from(drop.querySelectorAll('li'));

    function showDrop() { drop.classList.remove('hidden'); }
    function hideDrop() { setTimeout(() => drop.classList.add('hidden'), 150); }

    search.addEventListener('focus', showDrop);
    search.addEventListener('blur',  hideDrop);
    search.addEventListener('input', () => {
      const q = search.value.toLowerCase();
      hidden.value = '';
      let any = false;
      items.forEach(li => {
        const match = li.dataset.label.toLowerCase().includes(q);
        li.style.display = match ? '' : 'none';
        if (match) any = true;
      });
      if (any) showDrop(); else hideDrop();
    });

    items.forEach(li => {
      li.addEventListener('mousedown', () => {
        hidden.value  = li.dataset.id;
        search.value  = li.dataset.label;
        drop.classList.add('hidden');
      });
    });
  })();

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

<!-- Receipt image modal -->
<div id="receiptModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/60 p-4" onclick="if(event.target===this)closeReceipt()">
  <div class="w-full max-w-md">
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-200">
      <div class="flex items-center justify-between px-4 py-3 bg-emerald-50 border-b border-emerald-100">
        <h3 class="font-semibold text-emerald-700"><i class="bi bi-image"></i> Receipt Preview</h3>
        <button onclick="closeReceipt()" class="text-slate-400 hover:text-slate-700 text-2xl leading-none">&times;</button>
      </div>
      <div class="p-3 bg-slate-50">
        <div class="bg-white rounded-lg border border-slate-200 p-2">
          <img id="receiptModalImg" src="" alt="Receipt preview" class="w-full rounded-md max-h-[55vh] object-contain">
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  function showReceipt(url) {
    document.getElementById('receiptModalImg').src = url;
    document.getElementById('receiptModal').classList.remove('hidden');
    document.getElementById('receiptModal').classList.add('flex');
  }
  function closeReceipt() {
    document.getElementById('receiptModal').classList.add('hidden');
    document.getElementById('receiptModal').classList.remove('flex');
    document.getElementById('receiptModalImg').src = '';
  }
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeReceipt(); });
</script>

<?php include __DIR__ . '/../templates/footer.php'; ?>
