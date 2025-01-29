<?php
session_start();
require "db.php";
require "auth.php";
require "includes/validators.php";

checkAuth();

$notification = null;
$conn = getDbConnection();
$username = $_SESSION['username'];

// Get user ID
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
$user_id = $stmt->fetchColumn();

// Handle new account creation
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['create_account'])) {
    $account_type = $_POST['account_type'];
    
    try {
        // Generate account number: bank.type.number (e.g., 1234.01.12345)
        $bank_code = "1234";
        $type_code = $account_type === "savings" ? "01" : ($account_type === "checking" ? "02" : "03");
        
        // Generate random 5-digit number and check if it's unique
        do {
            $account_number = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $full_account = $bank_code . "." . $type_code . "." . $account_number;
            
            // Validate the generated account number
            if (!validateAccountNumber($full_account)) {
                throw new Exception("Invalid account number generated");
            }
            
            $stmt = $conn->prepare("SELECT id FROM accounts WHERE account_number = ?");
            $stmt->execute([$full_account]);
        } while ($stmt->fetch());
        
        // Create the account
        $stmt = $conn->prepare("INSERT INTO accounts (user_id, account_number, account_type) VALUES (?, ?, ?)");
        if ($stmt->execute([$user_id, $full_account, $account_type])) {
            // Log account creation
            logActivity($conn, $user_id, 'account_created', json_encode([
                'account_type' => $account_type,
                'account_number' => $full_account
            ]));
            
            $notification = '<div class="success">Account created successfully!</div>';
        } else {
            throw new Exception("Failed to create account");
        }
    } catch (Exception $e) {
        $notification = '<div class="error">' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Get user's accounts
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
                    <?php if ($user_type === 'business'): ?>
                        <option value="business">Business Account</option>
                    <?php endif; ?>
                </select>
                <input type="hidden" name="create_account" value="1">
                <button type="submit">Create Account</button>
            </form>
        </div>
    </div>
</body>
</html> 