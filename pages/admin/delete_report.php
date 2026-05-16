<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
sessionStart();
requireRole('admin');
$id     = (int)$_GET['id'];
$report = dbGet('SELECT * FROM reports WHERE id = ?', [$id]);
if ($report) {
    if ($report['file_url'] && str_starts_with($report['file_url'], '/uploads/reports/')) {
        $path = __DIR__ . '/../../public' . $report['file_url'];
        if (file_exists($path)) unlink($path);
    }
    dbRun('DELETE FROM reports WHERE id = ?', [$id]);
}
redirect('/dashboard');
