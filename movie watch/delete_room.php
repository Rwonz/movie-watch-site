<?php
// delete_room.php

session_start();
require_once 'db.php';

// Yanıtı JSON formatında göndereceğimizi belirtelim
header('Content-Type: application/json');

// 1. Güvenlik: Kullanıcı giriş yapmış mı?
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bu işlem için giriş yapmalısınız.']);
    exit();
}

// 2. Güvenlik: POST ile oda ID'si gönderildi mi?
if (!isset($_POST['room_id'])) {
    echo json_encode(['success' => false, 'message' => 'Oda ID eksik.']);
    exit();
}

$room_id = intval($_POST['room_id']);
$user_id = $_SESSION['user_id'];

try {
    // 3. EN ÖNEMLİ GÜVENLİK KONTROLÜ: Bu kullanıcı gerçekten bu odanın sahibi mi?
    $stmt = $pdo->prepare("SELECT creator_id FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $creator_id = $stmt->fetchColumn();

    if ($creator_id != $user_id) {
        // Eğer odanın sahibi değilse, işlemi reddet
        echo json_encode(['success' => false, 'message' => 'Bu odayı silme yetkiniz yok.']);
        exit();
    }

    // 4. Yetki doğrulandı, silme işlemine başla (Transaction ile güvenli silme)
    $pdo->beginTransaction();

    // Odaya ait tüm sohbet mesajlarını sil
    $pdo->prepare("DELETE FROM messages WHERE room_id = ?")->execute([$room_id]);

    // Odaya ait tüm üyelik kayıtlarını sil
    $pdo->prepare("DELETE FROM room_members WHERE room_id = ?")->execute([$room_id]);

    // Odanın kendisini sil
    $pdo->prepare("DELETE FROM rooms WHERE id = ?")->execute([$room_id]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Oda başarıyla silindi.']);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Oda silme hatası: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Sunucu hatası oluştu.']);
}