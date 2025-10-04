<?php
session_start();
require_once 'db.php'; // Veritabanı bağlantı dosyanız

// Kullanıcı girişi yapılmamışsa veya gerekli veriler yoksa işlemi sonlandır
if (!isset($_SESSION['user_id']) || !isset($_POST['message']) || !isset($_POST['room_id'])) {
    echo json_encode(['success' => false, 'message' => 'Gerekli bilgiler eksik.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$room_id = intval($_POST['room_id']);
$message = trim($_POST['message']);

if (empty($message) || $room_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Mesaj boş olamaz veya oda ID geçersiz.']);
    exit();
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO chat_messages (room_id, user_id, username, message) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$room_id, $user_id, $username, $message]);
    
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    // Hata durumunda loglama yapabilirsiniz
    // error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Mesaj gönderilirken bir hata oluştu.']);
}
?>