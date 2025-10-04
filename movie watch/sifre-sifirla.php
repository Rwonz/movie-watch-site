<?php
include 'config.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = clean_input($_POST['email']);
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    
    if (empty($email)) {
        $error = "Lütfen e-posta adresinizi girin.";
    } elseif (!verify_recaptcha($recaptcha_response)) {
        $error = "Lütfen robot olmadığınızı doğrulayın.";
    } else {
        // Kullanıcı var mı kontrol et
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            // Şifre sıfırlama token oluştur
            $reset_token = generate_reset_token();
            $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $update_stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
            $update_stmt->bind_param("sss", $reset_token, $reset_expires, $email);
            
            if ($update_stmt->execute()) {
                // Şifre sıfırlama e-postası gönder
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/sifre-yenile.php?token=" . $reset_token;
                $subject = "FilmArkadaşı - Şifre Sıfırlama";
                $message = "
                    <h2>Şifre Sıfırlama İsteği</h2>
                    <p>Şifrenizi sıfırlamak için aşağıdaki bağlantıya tıklayın:</p>
                    <p><a href='$reset_link' style='background: #6a11cb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Şifremi Sıfırla</a></p>
                    <p>Bu bağlantı 1 saat süreyle geçerlidir.</p>
                    <p>Eğer bu isteği siz yapmadıysanız, bu e-postayı dikkate almayın.</p>
                ";
                
                if (send_email($email, $subject, $message)) {
                    $success = "Şifre sıfırlama bağlantısı e-posta adresinize gönderildi!";
                } else {
                    $error = "E-posta gönderilirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.";
                }
            } else {
                $error = "Bir hata oluştu. Lütfen daha sonra tekrar deneyin.";
            }
            $update_stmt->close();
        } else {
            $error = "Bu e-posta adresi ile kayıtlı kullanıcı bulunamadı.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Sıfırla - FilmArkadaşı</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-key"></i>
                <h1>Şifre Sıfırlama</h1>
                <p>Hesabınıza ait e-posta adresini girin</p>
            </div>

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
                    <label for="email">E-posta</label>
                    <input type="email" id="email" name="email" placeholder="E-posta adresiniz" required>
                </div>

                <div class="form-group">
                    <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Sıfırlama Bağlantısı Gönder</button>
            </form>

            <div class="auth-footer">
                <p><a href="login.php">Giriş ekranına dön</a></p>
            </div>
        </div>
    </div>
</body>
</html>