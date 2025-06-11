<?php
session_start();
require "db.php";
require "auth.php";
require "includes/validators.php";

// Sjekk om brukeren er logget inn
checkAuth();

// Variabel for meldinger til brukeren
$notification = null;
$conn = getDbConnection();
$username = $_SESSION['username'];

// Hent bruker-ID
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
$user_id = $stmt->fetchColumn();





// Håndter opprettelse av ny konto
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['create_account'])) {
    $account_type = $_POST['account_type'];

    try {
        // Generer enkelt kontonummer (bank.type.nummer)
        $bank_code = "1234";
        $type_code = $account_type === "savings" ? "01" : "02";
        $account_number = $bank_code . "." . $type_code . "." . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

        // Opprett ny konto i databasen
        $stmt = $conn->prepare("
            INSERT INTO accounts (user_id, account_number, account_type) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$user_id, $account_number, $account_type]);

        $notification = '<div class="success">Account created successfully</div>';

    } catch (Exception $e) {
        $notification = '<div class="error">Failed to create account</div>';
    }
}

// Hent alle brukerens kontoer
$stmt = $conn->prepare("SELECT * FROM accounts WHERE user_id = ?");
$stmt->execute([$user_id]);
$accounts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./static/styles.css">
    <title>My Accounts</title>
</head>

<body>
    <div class="nav">
        <div class="navItem">
            <div><a href="index.php">Home</a></div>
            <div>User: <?= htmlspecialchars($username) ?></div>
            <div><a href="logout.php">Logout</a></div>
        </div>
    </div>

    <?= $notification ?>

    <div class="Center">
        <div class="wrapper">
            <h2>My Accounts</h2>

            <?php if (empty($accounts)): ?>
                <p>You don't have any accounts yet.</p>
            <?php else: ?>
                <div class="accounts-list">
                    <?php foreach ($accounts as $account): ?>
                        <div class="account-card">
                            <h3><?= ucfirst($account['account_type']) ?> Account</h3>
                            <p>Account Number: <?= htmlspecialchars($account['account_number']) ?></p>
                            <p>Balance: kr <?= number_format($account['balance'], 2) ?></p>
                            <a href="transactions.php?account=<?= $account['id'] ?>" class="button">View Transactions</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="wrapper">
                <h3>Create New Account</h3>
                <select name="account_type" required>
                    <option value="savings">Savings Account</option>
                    <option value="checking">Checking Account</option>
                </select>
                <input type="hidden" name="create_account" value="1">
                <button type="submit">Create Account</button>

            </form>
        </div>
    </div>
</body>

</html>