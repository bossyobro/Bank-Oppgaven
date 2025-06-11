<?php
require "db.php";
session_start();
$notification = null;
$navn = null;

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Hent data fra registreringsskjemaet
    $username = $_POST["username"];
    $password = $_POST["password"];
    $email = $_POST["email"];
    $telefon = $_POST["telefon"];
    $fornavn = $_POST["fornavn"];
    $etternavn = $_POST["etternavn"];
    $navn = $fornavn . " " . $etternavn;
    $address = $_POST["adresse"];
    $user_type = $_POST["user_type"];



    // Sjekk at passordet er langt nok og at den inneholder minst et tall og en bokstav
    if (strlen($password) < 8) {
        $notification = '<div class="error">Password must be at least 8 characters long.</div>';
    } elseif (!preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $password)){
        $notification = '<div class="error">Password must contain at least 1 number and 1 charact</div>';
    }else {
        $conn = getDbConnection();

        // Sjekk om brukernavn, epost eller telefon allerede er i bruk
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ? OR phone = ?");
        $stmt->execute([$username, $email, $telefon]);
        
        if ($stmt->fetch()) {
            $notification = '<div class="error">Username, email or phone number is already taken</div>';
        } else {
            // Opprett ny bruker
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, phone, name, address, user_type) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $username, 
                password_hash($password, PASSWORD_DEFAULT), 
                $email, 
                $telefon, 
                $navn,
                $address,
                $user_type
            ]);

            // Logg inn brukeren automatisk
            $_SESSION['username'] = $username;
            header("Location: index.php");
            exit;
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
               pattern="[0-9]{8}" title="Vennligst skriv inn et gyldig 8-sifret telefonnummer">
        <input type="email" placeholder="E-Mail" name="email" required>
        <input type="text" placeholder="Username" name="username" required>
        <input type="password" placeholder="Password" name="password" 
               pattern=".{8,}" title="Passordet må være minst 8 tegn langt" required>
        <input type="submit" value="Register">
        
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </form>
</div>

</body>
</html>


