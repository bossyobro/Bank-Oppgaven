<?php
session_start();
require "db.php";
require "auth.php";

checkAuth();

$conn = getDbConnection();
$username = $_SESSION['username'];

// Get user info
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

// Get account summaries
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_accounts,
        SUM(balance) as total_balance,
        (SELECT COUNT(*) FROM transactions t JOIN accounts a ON t.account_id = a.id WHERE a.user_id = ?) as total_transactions
    FROM accounts 
    WHERE user_id = ?
");
$stmt->execute([$user['id'], $user['id']]);
$summary = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./static/styles.css">
    <title>Dashboard</title>
</head>

<body>

    <?php if (adminCheck() == True): ?>
        <div class="nav">
            <div class="navItem">
                <div><a href="accounts.php">My Accounts</a></div>
                <div><a href="profile.php">Profile</a></div>
                <div><a href="logout.php">Logout</a></div>
                <div><a href="./admin/dashboard.php">Admin Panel</a></div>
            </div>
        </div>

    <?php else: ?>

        <div class="nav">
            <div class="navItem">
                <div><a href="accounts.php">My Accounts</a></div>
                <div><a href="profile.php">Profile</a></div>
                <div><a href="logout.php">Logout</a></div>
            </div>
        </div>

    <?php endif; ?>

    <div class="Center">
        <div class="wrapper">
            <h2>Welcome, <?= htmlspecialchars($user['name']) ?></h2>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Accounts</h3>
                    <div class="stat-value"><?= number_format($summary['total_accounts']) ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Balance</h3>
                    <div class="stat-value"> <?= number_format($summary['total_balance'], 2) ?> NOK</div>
                </div>
                <div class="stat-card">
                    <h3>Total Transactions</h3>
                    <div class="stat-value"><?= number_format($summary['total_transactions']) ?></div>
                </div>
            </div>

            <a href="accounts.php" class="button">Manage Accounts</a>
        </div>
    </div>
</body>

</html>