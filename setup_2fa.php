<?php
require "PHPGangsta/GoogleAuthenticator.php";
session_start();

if (!isset($_SESSION['temp_2fa_secret']) || !isset($_SESSION['temp_username'])) {
    header("Location: register.php");
    exit;
}

$ga = new PHPGangsta_GoogleAuthenticator();
$secret = $_SESSION['temp_2fa_secret'];
$username = $_SESSION['temp_username'];
$qrCodeUrl = $ga->getQRCodeGoogleUrl($username, $secret, 'MyBank');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $code = $_POST['verification_code'];
    
    if ($ga->verifyCode($secret, $code, 2)) {
        // Code is valid, complete registration
        unset($_SESSION['temp_2fa_secret']);
        unset($_SESSION['temp_username']);
        
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid verification code. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./static/styles.css">
    <title>Setup 2FA</title>
</head>
<body>
<div class="Center">
    <div class="wrapper">
        <h2>Set Up Two-Factor Authentication</h2>
        <p>1. Install Google Authenticator on your phone</p>
        <p>2. Scan this QR code with the app:</p>
        
        <img src="<?= $qrCodeUrl ?>" alt="QR Code">
        
        <p>Or enter this code manually: <?= $secret ?></p>
        
        <form method="POST" class="wrapper">
            <input type="text" name="verification_code" 
                   placeholder="Enter the 6-digit code" required>
            <input type="submit" value="Verify">
        </form>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
    </div>
</div>
</body>
</html> 