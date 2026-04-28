<?php
// Layout header — opens <html> and app shell
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

<!-- Tailwind CSS: CDN, local fallback if offline -->
<link id="tw-css" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.17/dist/tailwind.min.css">
<script>document.getElementById('tw-css').onerror=function(){var l=document.createElement('link');l.rel='stylesheet';l.href='<?= APP_URL ?>/assets/css/tailwind.min.css';this.replaceWith(l);};</script>

<!-- Bootstrap Icons: CDN, local fallback if offline -->
<link id="bi-css" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script>document.getElementById('bi-css').onerror=function(){var l=document.createElement('link');l.rel='stylesheet';l.href='<?= APP_URL ?>/assets/css/bootstrap-icons.min.css';this.replaceWith(l);};</script>

<style>
  *, *::before, *::after { border-color: #e2e8f0; }
  body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
  .sidebar-hidden { transform: translateX(-100%); }
  main table th, main table td { text-align: center !important; vertical-align: middle; }
  @media (min-width: 768px) { .sidebar-hidden { transform: none; } }
  /* Mobile record cards */
  .mobile-cards { display: none; }
  @media (max-width: 639px) {
    .desktop-table { display: none !important; }
    .mobile-cards  { display: grid; gap: 0.75rem; padding: 0.75rem; }
    .record-card   { background:#fff; border:1px solid #d1fae5; border-radius:0.5rem; padding:0.75rem; box-shadow:0 1px 3px rgba(0,0,0,.06); }
    .record-card .card-row { display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem; margin-bottom:0.35rem; font-size:0.78rem; }
    .record-card .card-row:last-child { margin-bottom:0; }
    .record-card .card-label { color:#64748b; font-size:0.7rem; text-transform:uppercase; letter-spacing:.03em; flex-shrink:0; }
    .record-card .card-val   { text-align:right; font-size:0.78rem; }
    .record-card .card-actions { margin-top:0.5rem; padding-top:0.5rem; border-top:1px solid #d1fae5; display:flex; gap:0.5rem; justify-content:flex-end; }
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
        <span class="font-bold text-emerald-700">ESO Fines</span>
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
