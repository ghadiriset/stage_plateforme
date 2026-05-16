<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
sessionStart();
header('Content-Type: application/json');

requireAuth();
$user = currentUser();

$data       = json_decode(file_get_contents('php://input'), true);
$receiverId = (int)($data['receiver_id'] ?? 0);
$content    = trim($data['content'] ?? '');

if (!$receiverId || !$content) {
    http_response_code(400);
    echo json_encode(['error' => 'Données invalides.']);
    exit;
}

dbRun('INSERT INTO messages (sender_id, receiver_id, content) VALUES (?,?,?)', [$user['id'], $receiverId, $content]);
$id = (int)getDb()->lastInsertId();

echo json_encode([
    'id'          => $id,
    'sender_id'   => $user['id'],
    'receiver_id' => $receiverId,
    'content'     => $content,
    'created_at'  => date('Y-m-d H:i:s'),
]);
