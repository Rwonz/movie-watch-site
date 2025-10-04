<?php
session_start();
require_once 'db.php'; // Veritabanı bağlantı dosyanız

// Kullanıcı girişi veya oda ID'si yoksa işlemi sonlandır
if (!isset($_SESSION['user_id']) || !isset($_GET['room_id'])) {
    echo json_encode(['success' => false, 'users' => []]);
    exit();
}

$room_id = intval($_GET['room_id']);
if ($room_id <= 0) {
    echo json_encode(['success' => false, 'users' => []]);
    exit();
}

try {
    // room_members tablosundan kullanıcı ID'lerini ve users tablosundan kullanıcı adlarını çekiyoruz.
    $stmt = $pdo->prepare(
        "SELECT u.id, u.username 
         FROM room_members rm
         JOIN users u ON rm.user_id = u.id
         WHERE rm.room_id = ?"
    );
    $stmt->execute([$room_id]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'users' => $users]);

} catch (PDOException $e) {
    // Hata durumunda loglama yapabilirsiniz.
    // error_log("Kullanıcıları çekerken hata: " . $e->getMessage());
    echo json_encode(['success' => false, 'users' => []]);
}
?>