<?php
session_start();
require "db.php";


$notification = null;

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $conn = getDbConnection();

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit;
    } else {
        $notification = '<div class="error">Invalid username or password</div>';
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
        <form action="" method="POST" class="wrapper">
            <h2>Login</h2>
            <input type="text" placeholder="Username or Email" name="username" required>
            <input type="password" placeholder="Password" name="password" required>
            <input type="submit" value="Login">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </form>
    </div>
</body>

</html>