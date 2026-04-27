<?php
// =====================================================
// Login page (entry point)
// =====================================================
require_once __DIR__ . '/includes/auth.php';

// If already logged in, send to the right dashboard
$u = current_user();
if ($u) {
    redirect(APP_URL . ($u['role'] === 'admin' ? '/admin/dashboard.php' : '/student/dashboard.php'));
}

$expired = isset($_GET['expired']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login · <?= e(APP_NAME) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-600 to-emerald-800 flex items-center justify-center p-4">

<div class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">

  <!-- Brand header -->
  <div class="bg-emerald-600 text-white text-center py-6 px-6">
    <div class="bg-white/20 inline-flex items-center justify-center w-16 h-16 rounded-full mb-2">
      <i class="bi bi-shield-check text-3xl"></i>
    </div>
    <h1 class="text-xl font-bold">ESO Fines Management</h1>
    <p class="text-emerald-100 text-xs mt-1">Secure access portal</p>
  </div>

  <!-- Form -->
  <div class="p-6 space-y-4">
    <?php foreach (flash_pull() as $f): ?>
      <div class="bg-red-100 border border-red-300 text-red-700 text-sm p-3 rounded"><?= e($f['msg']) ?></div>
    <?php endforeach; ?>

    <?php if ($expired): ?>
      <div class="bg-amber-100 border border-amber-300 text-amber-800 text-sm p-3 rounded">
        Your session expired. Please log in again.
      </div>
    <?php endif; ?>

    <form action="<?= APP_URL ?>/actions/login.php" method="POST" class="space-y-4">
      <?= csrf_field() ?>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Username or Email</label>
        <div class="relative">
          <i class="bi bi-person absolute left-3 top-3 text-slate-400"></i>
          <input type="text" name="login" required autofocus
                 class="pl-9 pr-3 py-2 w-full border border-slate-300 rounded-lg focus:border-emerald-500 focus:ring focus:ring-emerald-200 outline-none">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
        <div class="relative">
          <i class="bi bi-lock absolute left-3 top-3 text-slate-400"></i>
          <input type="password" name="password" required
                 class="pl-9 pr-3 py-2 w-full border border-slate-300 rounded-lg focus:border-emerald-500 focus:ring focus:ring-emerald-200 outline-none">
        </div>
      </div>

      <button type="submit"
              class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg transition flex items-center justify-center gap-2">
        <i class="bi bi-box-arrow-in-right"></i> Sign In
      </button>
    </form>

    <div class="text-xs text-slate-500 border-t pt-3 mt-2">
      <p class="font-semibold mb-1">Demo accounts:</p>
      <p>Admin → <span class="text-emerald-700">admin / Admin@123</span></p>
      <p>Student → <span class="text-emerald-700">juan / Student@123</span></p>
    </div>
  </div>
</div>

</body>
</html>
