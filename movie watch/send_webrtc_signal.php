<?php
session_start();
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['user_id']) || !isset($data['to_user_id']) || !isset($data['signal_data']) || !isset($data['room_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Eksik bilgi.']);
    exit();
}

$from_user_id = $_SESSION['user_id'];
$to_user_id = $data['to_user_id'];
$room_id = $data['room_id'];
$signal_data = json_encode($data['signal_data']);

try {
    $stmt = $pdo->prepare(
        "INSERT INTO webrtc_signals (room_id, from_user_id, to_user_id, signal_data) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$room_id, $from_user_id, $to_user_id, $signal_data]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası.']);
}
?>