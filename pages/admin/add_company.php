<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
sessionStart();
requireRole('admin');
dbRun('INSERT INTO companies (name,sector,location,description,contact_email,website) VALUES (?,?,?,?,?,?)', [
    $_POST['name'], $_POST['sector'], $_POST['location'], $_POST['description'], $_POST['contact_email'], $_POST['website']
]);
redirect('/dashboard');
