<?php
session_start();
require "../db.php";
require "../auth.php";

checkAuth();

// Verify admin status
$conn = getDbConnection();
$stmt = $conn->prepare("SELECT user_type FROM users WHERE username = ?");
$stmt->execute([$_SESSION['username']]);
$user_type = $stmt->fetchColumn();

if ($user_type !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Get statistics
$stats = [
    'total_users' => $conn->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'total_accounts' => $conn->query("SELECT COUNT(*) FROM accounts")->fetchColumn(),
    'total_transactions' => $conn->query("SELECT COUNT(*) FROM transactions")->fetchColumn(),
    'total_balance' => $conn->query("SELECT SUM(balance) FROM accounts")->fetchColumn()
];

// Get recent transactions
$stmt = $conn->prepare("
    SELECT t.*, a.account_number, u.username 
    FROM transactions t
    JOIN accounts a ON t.account_id = a.id
    JOIN users u ON a.user_id = u.id
    ORDER BY t.transaction_date DESC
    LIMIT 10
");
$stmt->execute();
$recent_transactions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/styles.css">
    <title>Admin Dashboard</title>
</head>
<body>
    <div class="nav">
        <div class="navItem">
            <div><a href="users.php">Manage Users</a></div>
            <div><a href="interest_rates.php">Interest Rates</a></div>
            <div><a href="../logout.php">Logout</a></div>
            <div><a href="../index.php">Home</a></div>
        </div>
    </div>

    <div class="admin-container">
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="stat-value"><?= number_format($stats['total_users']) ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Accounts</h3>
                <div class="stat-value"><?= number_format($stats['total_accounts']) ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Transactions</h3>
                <div class="stat-value"><?= number_format($stats['total_transactions']) ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Balance</h3>
                <div class="stat-value"> <?= number_format($stats['total_balance'], 2)  ?> NOK</div>
            </div>
        </div>

        <div class="admin-section">
            <h2>Recent Transactions</h2>
            <div class="transaction-list">
                <?php foreach ($recent_transactions as $trans): ?>
                    <div class="transaction-item">
                        <div class="transaction-user">
                            <?= htmlspecialchars($trans['username']) ?>
                        </div>
                        <div class="transaction-type">
                            <?= ucfirst($trans['transaction_type']) ?>
                        </div>
                        <div class="transaction-amount">
                             <?= number_format($trans['amount'], 2) ?> NOK
                        </div>
                        <div class="transaction-date">
                            <?= $trans['transaction_date'] ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html> 