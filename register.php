<?php
// =====================================================
// Student registration
// =====================================================
require_once __DIR__ . '/includes/auth.php';

// If already logged in, send to the right dashboard
$u = current_user();
if ($u) {
    redirect(APP_URL . ($u['role'] === 'admin' ? '/admin/dashboard.php' : '/student/dashboard.php'));
}

$values = [
    'student_no' => '',
    'full_name'  => '',
    'email'      => '',
    'contact'    => '',
    'course'     => '',
    'year_level' => '',
    'section'    => '',
    'username'   => '',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $values = [
        'student_no' => post('student_no'),
        'full_name'  => post('full_name'),
        'email'      => post('email'),
        'contact'    => post('contact'),
        'course'     => post('course'),
        'year_level' => post('year_level'),
        'section'    => post('section'),
        'username'   => post('username'),
    ];
    $password        = post('password');
    $passwordConfirm = post('password_confirm');

    if ($values['student_no'] === '' || $values['full_name'] === '' || $values['email'] === '' || $values['username'] === '') {
        $errors[] = 'Please fill in all required fields.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $passwordConfirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $dupStudent = db_one('SELECT id FROM students WHERE student_no = ? OR email = ? LIMIT 1',
            [$values['student_no'], $values['email']]);
        if ($dupStudent) {
            $errors[] = 'Student number or email already exists.';
        }

        $dupUser = db_one('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1',
            [$values['username'], $values['email']]);
        if ($dupUser) {
            $errors[] = 'Username or email already exists.';
        }
    }

    if (!$errors) {
        try {
            db()->beginTransaction();
            $sid = db_insert(
                'INSERT INTO students (student_no,full_name,email,contact,course,year_level,section) VALUES (?,?,?,?,?,?,?)',
                [$values['student_no'], $values['full_name'], $values['email'], $values['contact'], $values['course'], $values['year_level'], $values['section']]
            );
            db_insert(
                'INSERT INTO users (username,email,password,role,student_id,is_active) VALUES (?,?,?,?,?,1)',
                [$values['username'], $values['email'], password_hash($password, PASSWORD_BCRYPT), 'student', $sid]
            );
            db()->commit();

            log_activity('student_register', "Student #{$sid} registered");
            flash('success', 'Registration complete. Please sign in.');
            redirect(APP_URL . '/index.php');
        } catch (PDOException $e) {
            db()->rollBack();
            $errors[] = 'Registration failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register · <?= e(APP_NAME) ?></title>
<!-- Tailwind CSS: local, CDN fallback if missing -->
<link id="tw-css" rel="stylesheet" href="<?= APP_URL ?>/assets/css/tailwind.min.css">
<script>document.getElementById('tw-css').onerror=function(){var l=document.createElement('link');l.rel='stylesheet';l.href='https://cdn.jsdelivr.net/npm/tailwindcss@3.4.17/dist/tailwind.min.css';this.replaceWith(l);};</script>

<!-- Bootstrap Icons: local, CDN fallback if missing -->
<link id="bi-css" rel="stylesheet" href="<?= APP_URL ?>/assets/css/bootstrap-icons.min.css">
<script>document.getElementById('bi-css').onerror=function(){var l=document.createElement('link');l.rel='stylesheet';l.href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css';this.replaceWith(l);};</script>
<style>*, *::before, *::after { border-color: #e2e8f0; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-600 to-emerald-800 flex items-start sm:items-center justify-center p-3 sm:p-4">

<div class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
  <div class="bg-emerald-600 text-white text-center py-5 px-6">
    <div class="bg-white/20 inline-flex items-center justify-center w-14 h-14 rounded-full mb-2">
      <i class="bi bi-person-plus text-3xl"></i>
    </div>
    <h1 class="text-xl font-bold">Student Registration</h1>
    <p class="text-emerald-100 text-xs mt-1">Create your student account</p>
  </div>

  <div class="p-4 sm:p-6 space-y-4">
    <?php if ($errors): ?>
      <div class="bg-red-100 border border-red-300 text-red-700 text-sm p-3 rounded">
        <ul class="list-disc list-inside">
          <?php foreach ($errors as $err): ?>
            <li><?= e($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-3" id="registerForm">
      <?= csrf_field() ?>

      <div id="stepPersonal" class="space-y-3">
        <div class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Step 1 of 2</div>
        <h2 class="text-sm font-semibold text-slate-800">Personal Information</h2>

        <!-- Student No. + Full Name: side-by-side on sm+, stacked on mobile -->
        <div class="grid grid-cols-1 sm:grid-cols-[7rem_1fr] gap-3">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Student No. *</label>
            <input type="text" name="student_no" required inputmode="numeric" pattern="\d{5}" minlength="5" maxlength="5"
                   value="<?= e($values['student_no']) ?>"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-base focus:border-emerald-500 focus:ring focus:ring-emerald-200 outline-none">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Full Name *</label>
            <input type="text" name="full_name" required
                   value="<?= e($values['full_name']) ?>"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-base focus:border-emerald-500 focus:ring focus:ring-emerald-200 outline-none">
          </div>
        </div>

        <!-- Email + Contact: side-by-side on sm+, stacked on mobile -->
        <div class="grid grid-cols-1 sm:grid-cols-[1fr_9rem] gap-3">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Email *</label>
            <input type="email" name="email" required
                   value="<?= e($values['email']) ?>"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-base focus:border-emerald-500 focus:ring focus:ring-emerald-200 outline-none">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Contact</label>
            <input type="text" name="contact" inputmode="numeric" pattern="\d{11}" minlength="11" maxlength="11"
                   value="<?= e($values['contact']) ?>"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-base focus:border-emerald-500 focus:ring focus:ring-emerald-200 outline-none">
          </div>
        </div>

        <!-- Course + Year + Section: on mobile Course full-width, Year+Section in a row -->
        <div class="grid grid-cols-1 sm:grid-cols-[1fr_1fr_3.5rem] gap-3">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Course</label>
            <select name="course"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-base focus:border-emerald-500 focus:ring focus:ring-emerald-200 outline-none bg-white">
              <option value="">Select course</option>
              <?php foreach (['BSCPE', 'BSCE', 'BSECE'] as $opt): ?>
                <option value="<?= $opt ?>" <?= $values['course'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="grid grid-cols-[1fr_4rem] sm:contents gap-3">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Year Level</label>
              <select name="year_level"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-base focus:border-emerald-500 focus:ring focus:ring-emerald-200 outline-none bg-white">
                <option value="">Select year</option>
                <?php foreach (['1', '2', '3', '4'] as $opt): ?>
                  <option value="<?= $opt ?>" <?= $values['year_level'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Section</label>
              <input type="text" name="section" maxlength="1" pattern="[A-Za-z]" inputmode="text"
                     value="<?= e($values['section']) ?>"
                     class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-base focus:border-emerald-500 focus:ring focus:ring-emerald-200 outline-none">
            </div>
          </div>
        </div>

        <button type="button" id="toAccount"
                class="w-full bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold py-3 rounded-lg transition flex items-center justify-center gap-2 text-base">
          Next <i class="bi bi-arrow-right"></i>
        </button>
      </div>

      <div id="stepAccount" class="space-y-3 hidden">
        <div class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Step 2 of 2</div>
        <h2 class="text-sm font-semibold text-slate-800">Account Information</h2>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Username *</label>
          <input type="text" name="username" required autocomplete="username"
                 value="<?= e($values['username']) ?>"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-base focus:border-emerald-500 focus:ring focus:ring-emerald-200 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Password *</label>
          <input type="password" name="password" required autocomplete="new-password" minlength="8"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-base focus:border-emerald-500 focus:ring focus:ring-emerald-200 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Confirm Password *</label>
          <input type="password" name="password_confirm" required autocomplete="new-password"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-base focus:border-emerald-500 focus:ring focus:ring-emerald-200 outline-none">
        </div>

        <div class="grid grid-cols-2 gap-3 pt-1">
          <button type="button" id="backPersonal"
                  class="w-full border border-slate-300 text-slate-700 font-semibold py-3 rounded-lg transition active:bg-slate-50 flex items-center justify-center gap-2 text-base">
            <i class="bi bi-arrow-left"></i> Back
          </button>
          <button type="submit"
                  class="w-full bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold py-3 rounded-lg transition flex items-center justify-center gap-2 text-base">
            <i class="bi bi-check2-circle"></i> Register
          </button>
        </div>
      </div>
    </form>

    <div class="text-sm text-slate-500 text-center border-t pt-3">
      Already have an account?
      <a href="<?= APP_URL ?>/index.php" class="text-emerald-700 hover:underline font-medium">Sign in</a>
    </div>
  </div>
</div>

<script>
  (function () {
    const stepPersonal = document.getElementById('stepPersonal');
    const stepAccount = document.getElementById('stepAccount');
    const toAccount = document.getElementById('toAccount');
    const backPersonal = document.getElementById('backPersonal');
    const form = document.getElementById('registerForm');
    const studentNo = form.querySelector('[name="student_no"]');
    const fullName = form.querySelector('[name="full_name"]');
    const email = form.querySelector('[name="email"]');
    const step2Fields = stepAccount.querySelectorAll('input');

    function setStep2Disabled(disabled) {
      step2Fields.forEach((field) => { field.disabled = disabled; });
    }

    function showAccount() {
      setStep2Disabled(false);
      stepPersonal.classList.add('hidden');
      stepAccount.classList.remove('hidden');
    }

    function showPersonal() {
      setStep2Disabled(true);
      stepAccount.classList.add('hidden');
      stepPersonal.classList.remove('hidden');
    }

    function step1Valid() {
      if (!studentNo.checkValidity()) {
        studentNo.reportValidity();
        return false;
      }
      if (!fullName.checkValidity()) {
        fullName.reportValidity();
        return false;
      }
      if (!email.checkValidity()) {
        email.reportValidity();
        return false;
      }
      return true;
    }

    setStep2Disabled(true);

    toAccount.addEventListener('click', function () {
      if (step1Valid()) {
        showAccount();
      }
    });
    backPersonal.addEventListener('click', showPersonal);
  })();
</script>

</body>
</html>
