<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
csrf_check();

$id   = (int)post('id');
$name = post('name');
$amt  = (float)post('default_amount');
$desc = post('description');

if ($name === '') { flash('error','Name is required.'); redirect(APP_URL.'/admin/categories.php'); }

if ($id) {
    db_exec('UPDATE fine_categories SET name=?, default_amount=?, description=? WHERE id=?',
        [$name, $amt, $desc, $id]);
    flash('success','Category updated.');
} else {
    db_insert('INSERT INTO fine_categories (name, default_amount, description) VALUES (?,?,?)',
        [$name, $amt, $desc]);
    flash('success','Category added.');
}
redirect(APP_URL . '/admin/categories.php');
