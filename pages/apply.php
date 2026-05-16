<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
sessionStart();
requireRole('student');
$user = currentUser();
$internshipId = (int)($_POST['internship_id'] ?? 0);
if ($internshipId > 0) {
    dbRun('INSERT INTO applications (student_id, internship_id, status) VALUES (?,?,?)', [$user['id'], $internshipId, 'En attente']);
}
redirect('/dashboard');
