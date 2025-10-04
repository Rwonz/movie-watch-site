<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<h2>Hoşgeldin, <?php echo $_SESSION['username']; ?>!</h2>
<p><a href="logout.php">Çıkış Yap</a></p>
