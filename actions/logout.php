<?php
require_once __DIR__ . '/../includes/auth.php';
log_activity('logout', 'User logged out');
session_unset();
session_destroy();
redirect(APP_URL . '/index.php');
