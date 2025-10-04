<?php
// leave_room.php

session_start();
require_once 'db.php';

// Sadece giriş yapmış kullanıcıların işlem yapabildiğinden emin ol
if (!isset($_SESSION['user_id'])) {
    exit();
}

// Tarayıcıdan gönderilen JSON verisini al
$data = json_decode(file_get_contents('php://input'), true);
$room_id = isset($data['room_id']) ? intval($data['room_id']) : 0;
$user_id = $_SESSION['user_id'];

if ($room_id > 0) {
    try {
        $pdo->beginTransaction();

        // 1. Oda kurucusunun kim olduğunu öğren
        $stmt_creator = $pdo->prepare("SELECT creator_id FROM rooms WHERE id = ?");
        $stmt_creator->execute([$room_id]);
        $creator_id = $stmt_creator->fetchColumn();

        // 2. Kullanıcıyı odadan sil
        $stmt_delete = $pdo->prepare("DELETE FROM room_members WHERE room_id = ? AND user_id = ?");
        $stmt_delete->execute([$room_id, $user_id]);

        // 3. Odada hala birileri var mı diye kontrol et
        $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM room_members WHERE room_id = ?");
        $stmt_count->execute([$room_id]);
        $remaining_members = $stmt_count->fetchColumn();

        // 4. Eğer odada kimse kalmadıysa VEYA ayrılan kişi oda kurucusu ise, odayı ve tüm verilerini temizle
        if ($remaining_members == 0 || $user_id == $creator_id) {
            
            // a. Odaya ait tüm sohbet mesajlarını sil
            $stmt_delete_messages = $pdo->prepare("DELETE FROM messages WHERE room_id = ?");
            $stmt_delete_messages->execute([$room_id]);

            // b. Odada kalmış olabilecek diğer tüm üyeleri sil (kurucu ayrıldığında önemlidir)
            $stmt_delete_all_members = $pdo->prepare("DELETE FROM room_members WHERE room_id = ?");
            $stmt_delete_all_members->execute([$room_id]);
            
            // c. Odanın kendisini sil
            $stmt_delete_room = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
            $stmt_delete_room->execute([$room_id]);
        }

        $pdo->commit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Oda temizleme hatası: ' . $e->getMessage());
    }
}