<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

if (!hash_equals($_SESSION['_csrf'] ?? '', getq('_csrf'))) { http_response_code(419); die('CSRF token mismatch.'); }
$id = (int)getq('id');
if ($id) { db_exec('DELETE FROM fine_categories WHERE id = ?', [$id]); flash('success','Category deleted.'); }
redirect(APP_URL . '/admin/categories.php');
