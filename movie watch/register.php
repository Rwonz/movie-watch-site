<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        echo "Lütfen tüm alanları doldurun.";
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hash);

    if ($stmt->execute()) {
        echo "Kayıt başarılı! <a href='login.php'>Giriş Yap</a>";
    } else {
        echo "Hata: " . $stmt->error;
    }
    $stmt->close();
}
?>

<form method="POST" action="">
    <h2>Kayıt Ol</h2>
    Kullanıcı Adı: <input type="text" name="username" required><br><br>
    Şifre: <input type="password" name="password" required><br><br>
    <button type="submit">Kayıt Ol</button>
</form>
