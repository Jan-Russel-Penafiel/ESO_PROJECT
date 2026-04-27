<?php
// =====================================================
// Admin · Manage Students (CRUD)
// =====================================================
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$pageTitle = 'Manage Students';

// Editing? load record
$editId = (int)getq('edit');
$edit   = $editId ? db_one('SELECT s.*, u.id AS uid, u.username FROM students s LEFT JOIN users u ON u.student_id = s.id WHERE s.id = ?', [$editId]) : null;

// Search
$q = trim(getq('q'));
if ($q !== '') {
    $like = "%$q%";
    $rows = db_all("SELECT * FROM students WHERE student_no LIKE ? OR full_name LIKE ? OR email LIKE ? ORDER BY full_name", [$like,$like,$like]);
} else {
    $rows = db_all('SELECT * FROM students ORDER BY full_name');
}

include __DIR__ . '/../templates/header.php';
include __DIR__ . '/../templates/sidebar.php';
?>

<div class="flex items-center justify-between mb-6">
  <h1 class="text-2xl font-bold text-emerald-800"><i class="bi bi-people"></i> Students</h1>
  <a href="?" class="text-sm text-emerald-700 hover:underline"><i class="bi bi-arrow-clockwise"></i> Refresh</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  <!-- Form -->
  <div class="bg-white rounded-lg shadow p-5 lg:col-span-1">
    <h2 class="font-semibold text-emerald-700 mb-3">
      <i class="bi bi-<?= $edit ? 'pencil-square' : 'person-plus' ?>"></i>
      <?= $edit ? 'Edit Student' : 'Add Student' ?>
    </h2>
    <form action="<?= APP_URL ?>/actions/student_save.php" method="POST" class="space-y-3 text-sm">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">

      <div>
        <label class="block text-slate-600 mb-1">Student No.*</label>
        <input name="student_no" required value="<?= e($edit['student_no'] ?? '') ?>" class="w-full border border-slate-300 rounded p-2 focus:ring focus:ring-emerald-200 outline-none">
      </div>
      <div>
        <label class="block text-slate-600 mb-1">Full Name*</label>
        <input name="full_name" required value="<?= e($edit['full_name'] ?? '') ?>" class="w-full border border-slate-300 rounded p-2 focus:ring focus:ring-emerald-200 outline-none">
      </div>
      <div>
        <label class="block text-slate-600 mb-1">Email*</label>
        <input name="email" type="email" required value="<?= e($edit['email'] ?? '') ?>" class="w-full border border-slate-300 rounded p-2 focus:ring focus:ring-emerald-200 outline-none">
      </div>
      <div>
        <label class="block text-slate-600 mb-1">Contact</label>
        <input name="contact" value="<?= e($edit['contact'] ?? '') ?>" class="w-full border border-slate-300 rounded p-2 focus:ring focus:ring-emerald-200 outline-none">
      </div>
      <div class="grid grid-cols-3 gap-2">
        <div>
          <label class="block text-slate-600 mb-1">Course</label>
          <input name="course" value="<?= e($edit['course'] ?? '') ?>" class="w-full border border-slate-300 rounded p-2">
        </div>
        <div>
          <label class="block text-slate-600 mb-1">Year</label>
          <input name="year_level" value="<?= e($edit['year_level'] ?? '') ?>" class="w-full border border-slate-300 rounded p-2">
        </div>
        <div>
          <label class="block text-slate-600 mb-1">Section</label>
          <input name="section" value="<?= e($edit['section'] ?? '') ?>" class="w-full border border-slate-300 rounded p-2">
        </div>
      </div>

      <div class="border-t pt-3">
        <p class="text-xs text-slate-500 mb-2">Login account (auto-created/updated)</p>
        <div>
          <label class="block text-slate-600 mb-1">Username*</label>
          <input name="username" required value="<?= e($edit['username'] ?? '') ?>" class="w-full border border-slate-300 rounded p-2">
        </div>
        <div class="mt-2">
          <label class="block text-slate-600 mb-1"><?= $edit ? 'New Password (leave blank to keep)' : 'Password*' ?></label>
          <input type="password" name="password" <?= $edit ? '' : 'required' ?> class="w-full border border-slate-300 rounded p-2">
        </div>
      </div>

      <div class="flex gap-2 pt-2">
        <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 rounded">
          <i class="bi bi-save"></i> <?= $edit ? 'Update' : 'Save' ?>
        </button>
        <?php if ($edit): ?>
          <a href="?" class="px-4 py-2 border rounded text-slate-600 hover:bg-slate-50">Cancel</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- List -->
  <div class="bg-white rounded-lg shadow lg:col-span-2">
    <div class="p-4 border-b flex flex-col sm:flex-row sm:items-center gap-2 sm:justify-between">
      <h2 class="font-semibold text-emerald-700">All Students (<?= count($rows) ?>)</h2>
      <form method="GET" class="flex gap-2">
        <input name="q" value="<?= e($q) ?>" placeholder="Search..." class="border rounded px-3 py-1 text-sm">
        <button class="bg-emerald-600 text-white px-3 py-1 rounded text-sm"><i class="bi bi-search"></i></button>
      </form>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm min-w-[480px]">
        <thead class="bg-emerald-50 text-emerald-800">
          <tr>
            <th class="text-left p-2">Student No</th>
            <th class="text-left p-2">Name</th>
            <th class="text-left p-2">Course/Year</th>
            <th class="text-left p-2">Email</th>
            <th class="p-2">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr class="border-t hover:bg-emerald-50/40">
            <td class="p-2 font-mono"><?= e($r['student_no']) ?></td>
            <td class="p-2 font-medium"><?= e($r['full_name']) ?></td>
            <td class="p-2 text-xs text-slate-500"><?= e(trim($r['course'] . ' ' . $r['year_level'] . '-' . $r['section'], ' -')) ?></td>
            <td class="p-2 text-xs"><?= e($r['email']) ?></td>
            <td class="p-2 text-center">
              <a href="?edit=<?= $r['id'] ?>" class="text-amber-600 hover:underline text-xs"><i class="bi bi-pencil"></i></a>
              <a href="<?= APP_URL ?>/actions/student_delete.php?id=<?= $r['id'] ?>&_csrf=<?= csrf_token() ?>"
                 onclick="return confirm('Delete this student? Their fines will also be removed.')"
                 class="text-red-600 hover:underline text-xs ml-2"><i class="bi bi-trash"></i></a>
            </td>
          </tr>
        <?php endforeach; if (!$rows): ?>
          <tr><td colspan="5" class="p-6 text-center text-slate-400">No students found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
