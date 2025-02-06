<?php

session_start();
require "db.php";
require "auth.php";
require "includes/validators.php";

checkAuth();

$notification = null;
$conn = getDbConnection();
$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    try {
        // Verify password
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!password_verify($_POST['password'], $user['password'])) {
            throw new Exception("Incorrect password");
        }

        $conn->beginTransaction();

        // Log deactivation
        logActivity($conn, $user['id'], 'account_deactivated', null);

        // Deactivate user
        $stmt = $conn->prepare("UPDATE users SET status = 'deactivated' WHERE id = ?");
        $stmt->execute([$user['id']]);

        $conn->commit();
        
        // Logout
        session_destroy();
        header("Location: login.php?msg=account_deactivated");
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        $notification = '<div class="error">' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./static/styles.css">
    <title>Deactivate Account</title>
</head>
<body>
    <div class="nav">
        <div class="navItem">
            <div><a href="profile.php">Back to Profile</a></div>
            <div>User: <?= htmlspecialchars($username) ?></div>
            <div><a href="logout.php">Logout</a></div>
        </div>
    </div>

    <?= $notification ?>

    <div class="Center">
        <div class="wrapper">
            <h2>Deactivate Account</h2>
            <p class="warning">Warning: This action will deactivate your account. You will no longer be able to log in or access your accounts.</p>
            
            <form method="POST" class="deactivate-form">
                <div class="form-group">
                    <label>Enter your password to confirm:</label>
                    <input type="password" name="password" required>
                </div>
                
                <button type="submit" class="delete-button" 
                        onclick="return confirm('Are you sure you want to deactivate your account?')">
                    Deactivate Account
                </button>
            </form>
        </div>
    </div>
</body>
</html>
