<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['room_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'signals' => []]);
    exit();
}

$to_user_id = $_SESSION['user_id'];
$room_id = $_GET['room_id'];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "SELECT id, from_user_id, signal_data FROM webrtc_signals WHERE room_id = ? AND to_user_id = ? ORDER BY timestamp ASC"
    );
    $stmt->execute([$room_id, $to_user_id]);
    $signals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($signals)) {
        $ids_to_delete = array_column($signals, 'id');
        $placeholders = implode(',', array_fill(0, count($ids_to_delete), '?'));
        $delete_stmt = $pdo->prepare("DELETE FROM webrtc_signals WHERE id IN ($placeholders)");
        $delete_stmt->execute($ids_to_delete);
    }
    
    $pdo->commit();

    foreach ($signals as &$signal) {
        $signal['signal_data'] = json_decode($signal['signal_data'], true);
    }

    echo json_encode(['success' => true, 'signals' => $signals]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası.', 'error' => $e->getMessage()]);
}
?>