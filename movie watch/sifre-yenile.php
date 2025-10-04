<?php
include 'config.php';

$error = '';
$success = '';
$valid_token = false;

// Token kontrolü
if (isset($_GET['token'])) {
    $token = clean_input($_GET['token']);
    
    $stmt = $conn->prepare("SELECT id, reset_expires FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $reset_expires);
        $stmt->fetch();
        
        // Token süresi kontrolü
        if (strtotime($reset_expires) > time()) {
            $valid_token = true;
            
            // Şifre güncelleme
            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                $password = clean_input($_POST['password']);
                $confirm_password = clean_input($_POST['confirm_password']);
                $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
                
                if (empty($password) || empty($confirm_password)) {
                    $error = "Lütfen tüm alanları doldurun.";
                } elseif ($password !== $confirm_password) {
                    $error = "Şifreler eşleşmiyor!";
                } elseif (strlen($password) < 6) {
                    $error = "Şifre en az 6 karakter olmalıdır.";
                } elseif (!verify_recaptcha($recaptcha_response)) {
                    $error = "Lütfen robot olmadığınızı doğrulayın.";
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $update_stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
                    $update_stmt->bind_param("si", $hash, $user_id);
                    
                    if ($update_stmt->execute()) {
                        $success = "Şifreniz başarıyla güncellendi. Yönlendiriliyorsunuz...";
                        header("Refresh: 3; URL=login.php");
                    } else {
                        $error = "Bir hata oluştu. Lütfen daha sonra tekrar deneyin.";
                    }
                    $update_stmt->close();
                }
            }
        } else {
            $error = "Şifre sıfırlama bağlantısının süresi dolmuş.";
        }
    } else {
        $error = "Geçersiz şifre sıfırlama bağlantısı.";
    }
    $stmt->close();
} else {
    $error = "Geçersiz şifre sıfırlama bağlantısı.";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Şifre Oluştur - FilmArkadaşı</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-key"></i>
                <h1>Yeni Şifre Oluştur</h1>
                <p>Yeni şifrenizi belirleyin</p>
            </div>

            <?php if ($error && !$valid_token): ?>
            <div class="notification error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                <p><a href="sifre-sifirla.php">Yeni şifre sıfırlama isteği gönder</a></p>
            </div>
            <?php elseif ($valid_token): ?>
            
            <?php if ($error): ?>
            <div class="notification error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="notification success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="">
                <div class="form-group">
                    <label for="password">Yeni Şifre</label>
                    <input type="password" id="password" name="password" placeholder="Yeni şifreniz (en az 6 karakter)" required minlength="6">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Şifre Tekrar</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Yeni şifrenizi tekrar girin" required>
                </div>

                <div class="form-group">
                    <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Şifremi Güncelle</button>
            </form>
            
            <?php endif; ?>

            <div class="auth-footer">
                <p><a href="login.php">Giriş ekranına dön</a></p>
            </div>
        </div>
    </div>
</body>
</html>