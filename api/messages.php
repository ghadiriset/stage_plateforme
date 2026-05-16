<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
sessionStart();
header('Content-Type: application/json');

requireAuth();
$user        = currentUser();
$contactId   = (int)($_GET['contact'] ?? 0);
$afterId     = (int)($_GET['after'] ?? 0);

if (!$contactId) { echo json_encode([]); exit; }

$messages = dbAll(
    'SELECT * FROM messages WHERE ((sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)) AND id > ? ORDER BY id ASC',
    [$user['id'], $contactId, $contactId, $user['id'], $afterId]
);

echo json_encode($messages);
