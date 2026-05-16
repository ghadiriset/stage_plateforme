<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
sessionStart();
requireRole('admin');
$id = (int)$_GET['id'];
if ($id > 0 && $id !== (int)currentUser()['id']) {
    dbRun('DELETE FROM users WHERE id = ?', [$id]);
}
redirect('/dashboard');
