<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['room_id']) || !isset($_POST['message'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters.']);
    exit();
}

require_once 'db.php';

$room_id = intval($_POST['room_id']);
$user_id = $_SESSION['user_id'];
$message = trim($_POST['message']);

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty.']);
    exit();
}

try {
    $stmt = $pdo->prepare("INSERT INTO chat_messages (room_id, user_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$room_id, $user_id, $message]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    // Gerçek uygulamada hata loglanmalı
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}