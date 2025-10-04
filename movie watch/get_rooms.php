<?php
// get_rooms.php

session_start();
// Sadece giriş yapmış kullanıcıların bu veriye erişebilmesi için kontrol
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Giriş yapmalısınız.']);
    exit();
}

require_once 'db.php';

$public_rooms = [];
try {
    // lobby.php'deki sorgunun aynısı
    $stmt = $pdo->prepare("
        SELECT r.*, u.username as creator_name, 
               (SELECT COUNT(*) FROM room_members WHERE room_id = r.id) as member_count
        FROM rooms r 
        JOIN users u ON r.creator_id = u.id 
        WHERE r.room_type = 'public' AND r.is_active = 1
        ORDER BY r.created_at DESC
    ");
    $stmt->execute();
    $public_rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Başarılı yanıtı JSON olarak gönder
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'rooms' => $public_rooms]);

} catch (PDOException $e) {
    // Hata durumunda JSON formatında hata mesajı gönder
    header('HTTP/1.1 500 Internal Server Error');
    error_log("API odalar getirme hatası: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Sunucu hatası oluştu.']);
}