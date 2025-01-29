<?php
require "db.php";
require "PHPGangsta/GoogleAuthenticator.php";
session_start();
$notification = null;
$navn = null;

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $email = $_POST["email"];
    $telefon = $_POST["telefon"];
    $fornavn = $_POST["fornavn"];
    $etternavn = $_POST["etternavn"];
    $navn = $fornavn . " " . $etternavn;
    $address = $_POST["adresse"];
    $user_type = $_POST["user_type"];

    if (strlen($password) < 8) {
        $notification = '<div class="error">Password must be at least 8 characters long.</div>';
    } else {
        $conn = getDbConnection();

        if (isset($_POST['username']) && ($_POST['password'])) {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ? OR phone = ?");
            $stmt->execute([$username, $email, $telefon]);
            
            if ($stmt->fetch()) {
                $notification = '<div class="error">Username, E-Mail or phone number is already taken</div>';
            } else {
                // Generate 2FA secret
                $ga = new PHPGangsta_GoogleAuthenticator();
                $secret = $ga->createSecret();
                
                $stmt = $conn->prepare("INSERT INTO users (username, password, email, phone, name, address, user_type, two_factor_secret) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $username, 
                    password_hash($password, PASSWORD_DEFAULT), 
                    $email, 
                    $telefon, 
                    $navn,
                    $address,
                    $user_type,
                    $secret
                ]);

                // Store 2FA setup data in session
                $_SESSION['temp_2fa_secret'] = $secret;
                $_SESSION['temp_username'] = $username;
                
                // Redirect to 2FA setup page
                header("Location: setup_2fa.php");
                exit;
            }
        }
    }
}

// if (isset($_SESSION['username']) && ($_SESSION['password'])) {
//     header("location: index.php");
// }



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./static/styles.css">
    <title>Register</title>
</head>
<body>

<?= $notification ?>

<div class="Center">
    <form action="" method="POST" class="wrapper">
        <h2>Register New Account</h2>
        
        <select name="user_type" required>
            <option value="personal">Personal Account</option>
            <option value="business">Business Account</option>
        </select>
        
        <input type="text" placeholder="First Name" name="fornavn" required>
        <input type="text" placeholder="Last Name" name="etternavn" required>
        <input type="text" placeholder="Address" name="adresse" required>
        <input type="tel" placeholder="Phone Number" name="telefon" required 
               pattern="[0-9]{8}" title="Please enter a valid 8-digit phone number">
        <input type="email" placeholder="E-Mail" name="email" required>
        <input type="text" placeholder="Username" name="username" required>
        <input type="password" placeholder="Password" name="password" 
               pattern=".{8,}" title="Password must be at least 8 characters long" required>
        <input type="submit" value="Register">
        
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </form>
</div>

</body>
</html>
