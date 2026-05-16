<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
sessionStart();
requireAuth();
$user = currentUser();

$uploadedUrl = '';
if (!empty($_FILES['pdf_file']['tmp_name'])) {
    $uploadDir = __DIR__ . '/../public/uploads/reports/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $safeName = time() . '-' . preg_replace('/[^a-z0-9\-_]/i', '-', pathinfo($_FILES['pdf_file']['name'], PATHINFO_FILENAME)) . '.pdf';
    if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $uploadDir . $safeName)) {
        $uploadedUrl = '/uploads/reports/' . $safeName;
    }
}

$finalUrl    = $uploadedUrl ?: trim($_POST['file_url'] ?? '');
$studentName = $_POST['student_name'] ?? $user['full_name'];

if (!$finalUrl) { http_response_code(400); echo 'Fichier PDF requis.'; exit; }

dbRun('INSERT INTO reports (title,student_name,year,specialty,summary,file_url) VALUES (?,?,?,?,?,?)', [
    $_POST['title'], $studentName, $_POST['year'] ?? '', $_POST['specialty'] ?? '', $_POST['summary'] ?? '', $finalUrl
]);
redirect('/dashboard');
