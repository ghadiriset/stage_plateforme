<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
sessionStart();
requireRole('admin');
dbRun('INSERT INTO internships (company_id,title,description,duration,location,paid,status) VALUES (?,?,?,?,?,?,?)', [
    (int)$_POST['company_id'], $_POST['title'], $_POST['description'],
    $_POST['duration'], $_POST['location'], $_POST['paid'], $_POST['status'] ?: 'Ouvert'
]);
redirect('/dashboard');
