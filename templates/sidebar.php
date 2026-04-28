<?php
// Sidebar navigation — links depend on user role
$user = current_user();
$role = $user['role'];
?>
<aside id="sidebar"
  class="sidebar-hidden md:translate-x-0 fixed top-0 md:top-[61px]
    left-0 z-20 w-64 shrink-0
              bg-emerald-700 text-white pt-[4.5rem] md:pt-4 transition-transform duration-200
              h-screen md:h-[calc(100vh-61px)] overflow-y-auto"
  <!-- Close button visible only on mobile -->
  <button id="sidebarClose"
    class="md:hidden absolute top-3 right-3 text-white/80 hover:text-white text-2xl leading-none"
    aria-label="Close menu">
    <i class="bi bi-x-lg"></i>
  </button>
  <nav class="px-3 space-y-1 text-sm">

  <?php if ($role === 'admin'): ?>
    <p class="px-3 pt-3 text-emerald-200 uppercase text-xs">Admin Menu</p>
    <a href="<?= APP_URL ?>/admin/dashboard.php" class="flex items-center gap-2 px-3 py-2 rounded <?= is_active('dashboard.php') ?>">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="<?= APP_URL ?>/admin/students.php" class="flex items-center gap-2 px-3 py-2 rounded <?= is_active('students.php') ?>">
      <i class="bi bi-people"></i> Students
    </a>
    <a href="<?= APP_URL ?>/admin/categories.php" class="flex items-center gap-2 px-3 py-2 rounded <?= is_active('categories.php') ?>">
      <i class="bi bi-tags"></i> Fine Categories
    </a>
    <a href="<?= APP_URL ?>/admin/fines.php" class="flex items-center gap-2 px-3 py-2 rounded <?= is_active('fines.php') ?>">
      <i class="bi bi-cash-coin"></i> Fines
    </a>
    <a href="<?= APP_URL ?>/admin/payments.php" class="flex items-center gap-2 px-3 py-2 rounded <?= is_active('payments.php') ?>">
      <i class="bi bi-credit-card-2-back"></i> Payments
    </a>
    <a href="<?= APP_URL ?>/admin/reports.php" class="flex items-center gap-2 px-3 py-2 rounded <?= is_active('reports.php') ?>">
      <i class="bi bi-graph-up"></i> Reports
    </a>
  <?php else: ?>
    <p class="px-3 pt-3 text-emerald-200 uppercase text-xs">Student Menu</p>
    <a href="<?= APP_URL ?>/student/dashboard.php" class="flex items-center gap-2 px-3 py-2 rounded <?= is_active('dashboard.php') ?>">
      <i class="bi bi-speedometer2"></i> My Dashboard
    </a>
    <a href="<?= APP_URL ?>/student/fines.php" class="flex items-center gap-2 px-3 py-2 rounded <?= is_active('fines.php') ?>">
      <i class="bi bi-receipt"></i> My Fines
    </a>
    <a href="<?= APP_URL ?>/student/history.php" class="flex items-center gap-2 px-3 py-2 rounded <?= is_active('history.php') ?>">
      <i class="bi bi-clock-history"></i> Payment History
    </a>
  <?php endif; ?>

  </nav>
</aside>

<main class="flex-1 min-w-0 w-full p-4 md:p-6 md:ml-64">
<?php
// Render any flash messages right under the top bar
foreach (flash_pull() as $f) {
    $color = [
        'success' => 'bg-emerald-100 text-emerald-700 border-emerald-300',
        'error'   => 'bg-red-100 text-red-700 border-red-300',
        'info'    => 'bg-sky-100 text-sky-700 border-sky-300',
        'warning' => 'bg-amber-100 text-amber-700 border-amber-300',
    ][$f['type']] ?? 'bg-slate-100 text-slate-700 border-slate-300';
    echo '<div class="border ' . $color . ' rounded p-3 mb-4 text-sm">' . e($f['msg']) . '</div>';
}
?>
