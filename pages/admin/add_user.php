<?php
// add_user.php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
sessionStart();
requireRole('admin');
$hashed = password_hash($_POST['password'] ?? 'changeme', PASSWORD_BCRYPT);
dbRun('INSERT INTO users (full_name, email, password, role, department, promotion) VALUES (?,?,?,?,?,?)', [
    $_POST['full_name'], $_POST['email'], $hashed, $_POST['role'], $_POST['department'], $_POST['promotion']
]);
redirect('/dashboard');
