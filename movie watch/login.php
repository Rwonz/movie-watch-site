<?php
include 'config.php';

$error = '';
$success = '';

// CSRF koruması için token oluşturma
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Giriş işleme
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // CSRF token kontrolü
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Güvenlik hatası. Lütfen tekrar deneyin.";
    } else {
        $email = clean_input($_POST['email']);
        $password = clean_input($_POST['password']);
        $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
        
        // Brute-force kontrolü
        $ip_address = $_SERVER['REMOTE_ADDR'];
        if (is_brute_force($ip_address)) {
            $error = "Çok fazla başarısız giriş denemesi. Lütfen 15 dakika sonra tekrar deneyin.";
            record_login_attempt($ip_address, 0);
        } 
        // reCAPTCHA doğrulama
        elseif (!verify_recaptcha($recaptcha_response)) {
            $error = "Lütfen robot olmadığınızı doğrulayın.";
            record_login_attempt($ip_address, 0);
        }
        else {
            $stmt = $conn->prepare("SELECT id, password, username, fullname FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $hashed_password, $username, $fullname);
                $stmt->fetch();

                if (password_verify($password, $hashed_password)) {
                    $_SESSION['user_id'] = $id;
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    $_SESSION['fullname'] = $fullname;
                    
                    // Yeni CSRF token oluştur (token yenileme)
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    
                    record_login_attempt($ip_address, 1);
                    header("Location: ana.php");
                    exit();
                } else {
                    $error = "Hatalı şifre.";
                    record_login_attempt($ip_address, 0);
                }
            } else {
                $error = "Bu e-posta adresi ile kayıtlı kullanıcı bulunamadı.";
                record_login_attempt($ip_address, 0);
            }
            $stmt->close();
        }
    }
}

// Şifre sıfırlama başarı mesajı
if (isset($_GET['reset']) && $_GET['reset'] == 'success') {
    $success = "Şifre sıfırlama bağlantısı e-posta adresinize gönderildi!";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FilmArkadaşı - Giriş Yap</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-film"></i>
                <h1>FilmArkadaşı</h1>
                <p>Arkadaşlarınla film izleme keyfi</p>
            </div>

            <?php if ($error): ?>
            <div class="notification error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="notification success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label for="email">E-posta</label>
                    <input type="email" id="email" name="email" placeholder="E-posta adresiniz" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Şifre</label>
                    <input type="password" id="password" name="password" placeholder="Şifreniz" required>
                </div>

                <div class="form-group">
                    <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Giriş Yap</button>
            </form>

            <div class="auth-footer">
                <p>Hesabınız yok mu? <a href="kayit.php">Kayıt Olun</a></p>
                <p><a href="sifre-sifirla.php">Şifrenizi mi unuttunuz?</a></p>
            </div>
        </div>
    </div>
</body>
</html>