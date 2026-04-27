<?php
// =====================================================
// Save (create/update) a student record + linked user
// =====================================================
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
csrf_check();

$id        = (int)post('id');
$studentNo = post('student_no');
$name      = post('full_name');
$email     = post('email');
$contact   = post('contact');
$course    = post('course');
$year      = post('year_level');
$section   = post('section');
$username  = post('username');
$password  = post('password');

if ($studentNo === '' || $name === '' || $email === '' || $username === '') {
    flash('error', 'Required fields are missing.'); redirect(APP_URL . '/admin/students.php');
}

try {
    db()->beginTransaction();

    if ($id) {
        db_exec("UPDATE students SET student_no=?, full_name=?, email=?, contact=?, course=?, year_level=?, section=? WHERE id=?",
            [$studentNo, $name, $email, $contact, $course, $year, $section, $id]);

        // Update linked user (if any)
        $existing = db_one('SELECT id FROM users WHERE student_id = ?', [$id]);
        if ($existing) {
            if ($password !== '') {
                db_exec('UPDATE users SET username=?, email=?, password=? WHERE id=?',
                    [$username, $email, password_hash($password, PASSWORD_BCRYPT), $existing['id']]);
            } else {
                db_exec('UPDATE users SET username=?, email=? WHERE id=?', [$username, $email, $existing['id']]);
            }
        } elseif ($password !== '') {
            db_insert('INSERT INTO users (username,email,password,role,student_id,is_active) VALUES (?,?,?,?,?,1)',
                [$username, $email, password_hash($password, PASSWORD_BCRYPT), 'student', $id]);
        }
        log_activity('student_update', "Updated student #{$id}");
        flash('success', 'Student updated.');
    } else {
        if ($password === '') { db()->rollBack(); flash('error','Password is required for new students.'); redirect(APP_URL.'/admin/students.php'); }
        $sid = db_insert("INSERT INTO students (student_no,full_name,email,contact,course,year_level,section) VALUES (?,?,?,?,?,?,?)",
            [$studentNo, $name, $email, $contact, $course, $year, $section]);
        db_insert('INSERT INTO users (username,email,password,role,student_id,is_active) VALUES (?,?,?,?,?,1)',
            [$username, $email, password_hash($password, PASSWORD_BCRYPT), 'student', $sid]);
        log_activity('student_create', "Added student #{$sid}");
        flash('success', 'Student added with login account.');
    }

    db()->commit();
} catch (PDOException $e) {
    db()->rollBack();
    flash('error', 'Save failed: ' . $e->getMessage());
}
redirect(APP_URL . '/admin/students.php');
