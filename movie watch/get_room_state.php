<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_GET['room_id'])) {
    echo json_encode(['error' => 'Authentication or room ID missing']);
    exit();
}

require_once 'db.php';
$room_id = intval($_GET['room_id']);
$response = [];

// Kullanıcıları al
try {
    $stmt = $pdo->prepare("SELECT u.id, u.username FROM room_members rm JOIN users u ON rm.user_id = u.id WHERE rm.room_id = ?");
    $stmt->execute([$room_id]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response['users'] = ['success' => true, 'users' => $users];
} catch (PDOException $e) {
    $response['users'] = ['success' => false, 'message' => 'Failed to fetch users.'];
}

// Sohbet mesajlarını al (Örnek olarak son 50 mesaj)
try {
    $stmt = $pdo->prepare(
        "SELECT c.message, u.username 
         FROM chat_messages c 
         JOIN users u ON c.user_id = u.id 
         WHERE c.room_id = ? 
         ORDER BY c.created_at ASC LIMIT 50"
    );
    $stmt->execute([$room_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response['chat'] = ['success' => true, 'messages' => $messages];
} catch (PDOException $e) {
    $response['chat'] = ['success' => false, 'message' => 'Failed to fetch messages.'];
}

echo json_encode($response);