<?php
// =====================================================
// Student · All Fines (full list)
// =====================================================
require_once __DIR__ . '/../includes/auth.php';
require_role('student');
$pageTitle = 'My Fines';

$student = current_student();
$rows = db_all("
  SELECT f.*, c.description AS category_description,
         p.receipt_path, p.status AS pay_status
  FROM fines f
  LEFT JOIN fine_categories c ON c.id = f.category_id
  LEFT JOIN payments p ON p.id = (
      SELECT id FROM payments WHERE fine_id = f.id ORDER BY id DESC LIMIT 1
  )
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
        <th class="text-left p-2">Description</th>
        <th class="text-right p-2">Amount</th>
        <th class="p-2">Status</th>
        <th class="p-2">Receipt</th>
        <th class="p-2">Issued</th>
        <th class="p-2">Action</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $f): ?>
      <tr class="border-t hover:bg-emerald-50/40">
        <td class="p-2 text-xs text-slate-400">F-<?= e($f['id']) ?></td>
        <td class="p-2"><?= e($f['reason']) ?></td>
        <td class="p-2 text-xs text-slate-500"><?= e($f['category_description'] ?? '—') ?></td>
        <td class="p-2 text-right font-mono"><?= peso($f['amount']) ?></td>
        <td class="p-2 text-center"><?php
          $cls = ['unpaid'=>'bg-red-100 text-red-700','pending'=>'bg-amber-100 text-amber-700','paid'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-slate-100 text-slate-600'][$f['status']];
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
        <td class="p-2 text-center">
          <?php if ($f['status'] === 'unpaid'): ?>
            <a href="<?= APP_URL ?>/student/pay.php?fine_id=<?= $f['id'] ?>"
               class="text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-2 py-1 rounded inline-block">
              <i class="bi bi-credit-card"></i> Pay
            </a>
          <?php elseif ($f['status'] === 'pending'): ?>
            <a href="<?= APP_URL ?>/student/pay.php?fine_id=<?= $f['id'] ?>"
               class="text-xs bg-amber-400 hover:bg-amber-500 text-amber-900 font-semibold px-2 py-1 rounded inline-block border border-amber-500">
              <i class="bi bi-hourglass-split"></i> View
            </a>
          <?php else: ?>
            <span class="text-xs text-slate-400">—</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; if (!$rows): ?>
      <tr><td colspan="8" class="p-4 text-center text-slate-400">No fines on record.</td></tr>
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
            <div class="text-xs text-slate-400"><?= e($f['category_description'] ?? '—') ?> · F-<?= e($f['id']) ?></div>
          </div>
          <span class="text-xs px-2 py-1 rounded <?= $cls ?>"><?= e(ucfirst($f['status'])) ?></span>
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
        <?php if ($f['status'] === 'unpaid' || $f['status'] === 'pending'): ?>
          <div class="card-actions">
            <a href="<?= APP_URL ?>/student/pay.php?fine_id=<?= $f['id'] ?>"
               class="text-xs <?= $f['status'] === 'unpaid' ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-amber-400 hover:bg-amber-500 text-amber-900 font-semibold border border-amber-500' ?> px-3 py-1 rounded">
              <?= $f['status'] === 'unpaid' ? '<i class="bi bi-credit-card"></i> Pay' : '<i class="bi bi-hourglass-split"></i> View' ?>
            </a>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Receipt image modal -->
<style>
  @media (orientation: portrait) {
    #receiptModal .receipt-modal-box { max-width: 22rem; }
    #receiptImgContainer { max-height: 38vh; }
  }
  @media (orientation: landscape) {
    #receiptModal .receipt-modal-box { max-width: 90vw; }
    #receiptImgContainer { max-height: 72vh; }
  }
  #receiptImgContainer {
    overflow: hidden; cursor: grab;
    display: flex; align-items: center; justify-content: center;
    user-select: none;
  }
  #receiptImgContainer.dragging { cursor: grabbing; }
  #receiptModalImg {
    display: block; transform-origin: center center;
    transition: transform .15s ease; max-width: none; max-height: none;
  }
</style>
<div id="receiptModal" style="position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;background:rgba(15,23,42,.6);padding:.75rem;" onclick="if(event.target===this)closeReceipt()">
  <div class="receipt-modal-box w-full">
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-200">
      <div class="flex items-center justify-between px-4 py-2 bg-emerald-50 border-b border-emerald-100">
        <h3 class="font-semibold text-emerald-700 text-sm"><i class="bi bi-image"></i> Receipt Preview</h3>
        <div class="flex items-center gap-2">
          <button onclick="zoomReceipt(-1)" title="Zoom out" class="text-slate-500 hover:text-emerald-700 text-lg px-1 leading-none">&#8722;</button>
          <span id="receiptZoomLabel" class="text-xs text-slate-500 w-10 text-center">100%</span>
          <button onclick="zoomReceipt(1)"  title="Zoom in"  class="text-slate-500 hover:text-emerald-700 text-lg px-1 leading-none">&#43;</button>
          <button onclick="resetReceiptZoom()" title="Reset zoom" class="text-xs text-slate-400 hover:text-emerald-700 px-1">↺</button>
          <button onclick="closeReceipt()" class="text-slate-400 hover:text-slate-700 text-2xl leading-none ml-1">&times;</button>
        </div>
      </div>
      <div class="p-2 bg-slate-50">
        <div class="bg-white rounded-lg border border-slate-200 p-1">
          <div id="receiptImgContainer">
            <img id="receiptModalImg" src="" alt="Receipt preview" class="rounded-md">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
(function () {
  const modal     = document.getElementById('receiptModal');
  const img       = document.getElementById('receiptModalImg');
  const container = document.getElementById('receiptImgContainer');
  const zoomLabel = document.getElementById('receiptZoomLabel');
  let scale = 1, minScale = 0.25, maxScale = 5;
  let tx = 0, ty = 0, dragging = false, startX, startY, startTx, startTy, lastPinchDist = null;
  function applyTransform(animate) {
    img.style.transition = animate ? 'transform .15s ease' : 'none';
    img.style.transform  = `translate(${tx}px,${ty}px) scale(${scale})`;
    zoomLabel.textContent = Math.round(scale * 100) + '%';
  }
  function clampTranslate() { if (scale <= 1) { tx = 0; ty = 0; } }
  window.showReceipt = function (url) {
    img.src = url; scale = 1; tx = 0; ty = 0;
    applyTransform(false); modal.style.display = 'flex';
  };
  window.closeReceipt = function () { modal.style.display = 'none'; img.src = ''; };
  window.zoomReceipt = function (dir) {
    scale = Math.min(maxScale, Math.max(minScale, scale + dir * 0.25));
    clampTranslate(); applyTransform(true);
  };
  window.resetReceiptZoom = function () { scale = 1; tx = 0; ty = 0; applyTransform(true); };
  container.addEventListener('wheel', e => {
    e.preventDefault();
    scale = Math.min(maxScale, Math.max(minScale, scale + (e.deltaY < 0 ? 0.15 : -0.15)));
    clampTranslate(); applyTransform(false);
  }, { passive: false });
  container.addEventListener('mousedown', e => {
    if (scale <= 1) return;
    dragging = true; startX = e.clientX; startY = e.clientY; startTx = tx; startTy = ty;
    container.classList.add('dragging');
  });
  window.addEventListener('mousemove', e => {
    if (!dragging) return;
    tx = startTx + (e.clientX - startX); ty = startTy + (e.clientY - startY); applyTransform(false);
  });
  window.addEventListener('mouseup', () => { dragging = false; container.classList.remove('dragging'); });
  container.addEventListener('touchstart', e => {
    if (e.touches.length === 2) {
      lastPinchDist = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY);
    } else if (e.touches.length === 1 && scale > 1) {
      dragging = true; startX = e.touches[0].clientX; startY = e.touches[0].clientY; startTx = tx; startTy = ty;
    }
  }, { passive: true });
  container.addEventListener('touchmove', e => {
    if (e.touches.length === 2) {
      e.preventDefault();
      const dist = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY);
      if (lastPinchDist) { scale = Math.min(maxScale, Math.max(minScale, scale * (dist / lastPinchDist))); clampTranslate(); applyTransform(false); }
      lastPinchDist = dist;
    } else if (e.touches.length === 1 && dragging) {
      tx = startTx + (e.touches[0].clientX - startX); ty = startTy + (e.touches[0].clientY - startY); applyTransform(false);
    }
  }, { passive: false });
  container.addEventListener('touchend', () => { lastPinchDist = null; dragging = false; });
  document.addEventListener('keydown', e => {
    if (modal.style.display === 'none') return;
    if (e.key === 'Escape') closeReceipt();
    if (e.key === '+' || e.key === '=') zoomReceipt(1);
    if (e.key === '-') zoomReceipt(-1);
    if (e.key === '0') resetReceiptZoom();
  });
})();
</script>

<?php include __DIR__ . '/../templates/footer.php'; ?>
