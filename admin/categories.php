<?php
// =====================================================
// Admin · Manage Fine Categories
// =====================================================
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$pageTitle = 'Fine Categories';

$editId = (int)getq('edit');
$edit   = $editId ? db_one('SELECT * FROM fine_categories WHERE id = ?', [$editId]) : null;
$rows   = db_all('SELECT * FROM fine_categories ORDER BY name');

include __DIR__ . '/../templates/header.php';
include __DIR__ . '/../templates/sidebar.php';
?>

<h1 class="text-2xl font-bold text-emerald-800 mb-6"><i class="bi bi-tags"></i> Fine Categories</h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  <div class="bg-white rounded-lg shadow p-5">
    <h2 class="font-semibold text-emerald-700 mb-3">
      <?= $edit ? 'Edit Category' : 'Add Category' ?>
    </h2>
    <form action="<?= APP_URL ?>/actions/category_save.php" method="POST" class="space-y-3 text-sm">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
      <div>
        <label class="block text-slate-600 mb-1">Name*</label>
        <input name="name" required value="<?= e($edit['name'] ?? '') ?>" class="w-full border rounded p-2">
      </div>
      <div>
        <label class="block text-slate-600 mb-1">Default Amount (₱)*</label>
        <input name="default_amount" type="number" min="0" step="0.01" required
               value="<?= e($edit['default_amount'] ?? '') ?>" class="w-full border rounded p-2">
      </div>
      <div>
        <label class="block text-slate-600 mb-1">Description</label>
        <textarea name="description" rows="2" class="w-full border rounded p-2"><?= e($edit['description'] ?? '') ?></textarea>
      </div>
      <div class="flex gap-2 pt-2">
        <button class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded">
          <i class="bi bi-save"></i> <?= $edit ? 'Update' : 'Save' ?>
        </button>
        <?php if ($edit): ?><a href="?" class="px-4 py-2 border rounded">Cancel</a><?php endif; ?>
      </div>
    </form>
  </div>

  <div class="bg-white rounded-lg shadow lg:col-span-2">
    <div class="p-4 border-b">
      <h2 class="font-semibold text-emerald-700">Category List (<?= count($rows) ?>)</h2>
    </div>
    <div class="overflow-x-auto">
    <table class="w-full text-sm min-w-[360px]">
      <thead class="bg-emerald-50 text-emerald-800">
        <tr><th class="text-left p-2">Name</th><th class="text-right p-2">Default Amount</th><th class="text-left p-2">Description</th><th class="p-2">Actions</th></tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr class="border-t hover:bg-emerald-50/40">
          <td class="p-2 font-medium"><?= e($r['name']) ?></td>
          <td class="p-2 text-right"><?= peso($r['default_amount']) ?></td>
          <td class="p-2 text-slate-500"><?= e($r['description']) ?></td>
          <td class="p-2 text-center">
            <a href="?edit=<?= $r['id'] ?>" class="text-amber-600 text-xs"><i class="bi bi-pencil"></i></a>
            <a href="<?= APP_URL ?>/actions/category_delete.php?id=<?= $r['id'] ?>&_csrf=<?= csrf_token() ?>"
               onclick="return confirm('Delete this category?')"
               class="text-red-600 text-xs ml-2"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
      <?php endforeach; if (!$rows): ?>
        <tr><td colspan="4" class="p-4 text-center text-slate-400">No categories yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
