<?php
// create_room.php

session_start();
require_once 'db.php';

// Kullanıcının giriş yaptığından emin ol
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_name = trim($_POST['room_name']);
    $room_type = $_POST['room_type'];
    $max_users = intval($_POST['max_users']);
    $creator_id = $_SESSION['user_id'];

    // Basit doğrulama
    if (empty($room_name) || empty($room_type) || $max_users < 2 || $max_users > 10) {
        // Hata durumunda lobiye geri yönlendir (isteğe bağlı olarak hata mesajı gösterebilirsiniz)
        header("Location: lobby.php?error=invalid_data");
        exit();
    }

    try {
        // Veritabanı işlemlerini bir bütün olarak ele al (ya hepsi başarılı ya da hiçbiri)
        $pdo->beginTransaction();

        // 1. Yeni odayı 'rooms' tablosuna ekle
        $stmt = $pdo->prepare(
            "INSERT INTO rooms (room_name, room_type, max_users, creator_id, is_active) VALUES (?, ?, ?, ?, 1)"
        );
        $stmt->execute([$room_name, $room_type, $max_users, $creator_id]);

        // Az önce oluşturulan odanın ID'sini al
        $new_room_id = $pdo->lastInsertId();

        // 2. Odayı oluşturan kişiyi 'room_members' tablosuna ekle
        $stmt_member = $pdo->prepare(
            "INSERT INTO room_members (room_id, user_id) VALUES (?, ?)"
        );
        $stmt_member->execute([$new_room_id, $creator_id]);

        // Tüm işlemler başarılıysa veritabanına kaydet
        $pdo->commit();

        // Kullanıcıyı yeni oluşturulan film izleme odasına yönlendir
        header("Location: film-izleme.php?room=" . $new_room_id);
        exit();

    } catch (PDOException $e) {
        // Herhangi bir adımda hata olursa tüm işlemleri geri al
        $pdo->rollBack();
        error_log("Oda oluşturma hatası: " . $e->getMessage());
        header("Location: lobby.php?error=creation_failed");
        exit();
    }
} else {
    // POST isteği değilse lobiye yönlendir
    header("Location: lobby.php");
    exit();
}