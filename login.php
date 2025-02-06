<?php
session_start();
require "db.php";
require "PHPGangsta/GoogleAuthenticator.php";
require "includes/validators.php";

$notification = null;

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $conn = getDbConnection();

    if (isset($_POST['2fa_code'])) {
        if (!isset($_SESSION['temp_2fa_secret']) || !isset($_SESSION['temp_username'])) {
            header("Location: login.php");
            exit;
        }

        $ga = new PHPGangsta_GoogleAuthenticator();
        $code = $_POST['2fa_code'];
        $secret = $_SESSION['temp_2fa_secret'];
        $username = $_SESSION['temp_username'];

        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user_id = $stmt->fetchColumn();

        if ($ga->verifyCode($secret, $code, 2)) {
            $_SESSION['username'] = $username;
            unset($_SESSION['temp_2fa_secret']);
            unset($_SESSION['temp_username']);

            logActivity($conn, $user_id, 'login_success', null);

            header("Location: index.php");
            exit;
        } else {
            logActivity($conn, $user_id, 'login_2fa_failed', null);
            $notification = '<div class="error">Invalid verification code. Please try again.</div>';
            $show_2fa = true;
        }
    } else {
        $username = $_POST["username"];
        $password = $_POST["password"];

        $stmt = $conn->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['temp_username'] = $user['username'];
            $_SESSION['temp_2fa_secret'] = $user['two_factor_secret'];
            
            logActivity($conn, $user['id'], 'login_attempt', null);
            
            $show_2fa = true;
        } else {
            // Check if account is deactivated
            $stmt = $conn->prepare("SELECT status FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $status = $stmt->fetchColumn();
            
            if ($status === 'deactivated') {
                $notification = '<div class="error">This account has been deactivated. Please contact support.</div>';
            } else {
                if ($user) {
                    logActivity($conn, $user['id'], 'login_failed', 'Invalid password');
                } else {
                    logActivity($conn, null, 'login_failed', 'User not found: ' . $username);
                }
                $notification = '<div class="error">Username or password is incorrect. Please try again.</div>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./static/styles.css">
    <title>Login</title>
</head>

<body>
    <?= $notification ?>
    <div class="Center">
        <?php if (isset($show_2fa) && $show_2fa): ?>
            <form action="" method="POST" class="wrapper">
                <h2>Two-Factor Authentication</h2>
                <p>Please enter the 6-digit code from your authenticator app</p>
                <input type="text" name="2fa_code" placeholder="Enter 6-digit code" required>
                <input type="submit" value="Verify">
            </form>
        <?php else: ?>
            <form action="" method="POST" class="wrapper">
                <h2>Login</h2>
                <input type="text" placeholder="Username or Email" name="username" required>
                <input type="password" placeholder="Password" name="password" required>
                <input type="submit" value="Login">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>