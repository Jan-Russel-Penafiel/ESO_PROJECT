<?php
// Layout header — opens <html>, loads Tailwind, opens shell
require_once __DIR__ . '/../includes/auth.php';
$pageTitle = $pageTitle ?? 'Dashboard';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> · <?= e(APP_NAME) ?></title>

<!-- Tailwind CSS via CDN (theme: emerald + white) -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: { brand: { DEFAULT: '#059669', dark: '#047857', light: '#d1fae5' } }
      }
    }
  };
</script>

<!-- Bootstrap Icons for menu/icon glyphs -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
  body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
  .sidebar-hidden { transform: translateX(-100%); }
  main table th, main table td { text-align: center !important; vertical-align: middle; }
  @media (min-width: 768px) { .sidebar-hidden { transform: none; } }
  /* Compact table cells on mobile */
  @media (max-width: 639px) {
    main table th, main table td { padding: 6px 5px !important; font-size: 0.72rem !important; }
  }
</style>
</head>
<body class="bg-emerald-50 text-slate-800 min-h-screen">

<!-- Top bar -->
<header class="bg-white border-b border-emerald-100 sticky top-0 z-30 shadow-sm">
  <div class="flex items-center justify-between px-4 md:px-6 py-3">
    <div class="flex items-center gap-3">
      <button id="sidebarToggle" class="md:hidden text-emerald-700 text-2xl"><i class="bi bi-list"></i></button>
      <a href="<?= APP_URL ?>" class="flex items-center gap-2">
        <span class="bg-emerald-600 text-white p-2 rounded-lg"><i class="bi bi-shield-check"></i></span>
        <span class="font-bold text-emerald-700 hidden sm:block">ESO Fines</span>
      </a>
    </div>
    <div class="flex items-center gap-3">
      <span class="hidden sm:flex items-center gap-2 text-sm text-slate-600">
        <i class="bi bi-person-circle text-emerald-600 text-lg"></i>
        <?= e($user['username']) ?>
        <span class="text-xs uppercase bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded"><?= e($user['role']) ?></span>
      </span>
      <a href="<?= APP_URL ?>/actions/logout.php" class="text-sm text-red-600 hover:underline flex items-center gap-1">
        <i class="bi bi-box-arrow-right"></i> Logout
      </a>
    </div>
  </div>
</header>

<div class="flex">
<!-- Mobile sidebar backdrop -->
<div id="sidebarBackdrop" class="fixed inset-0 bg-black/50 z-10 md:hidden" style="display:none;" aria-hidden="true"></div>
