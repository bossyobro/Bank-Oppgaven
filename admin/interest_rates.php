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

$notification = null;

// Handle interest rate updates
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    try {
        $conn->beginTransaction();
        
        foreach ($_POST['rates'] as $account_type => $rate) {
            $stmt = $conn->prepare("
                UPDATE accounts 
                SET interest_rate = ? 
                WHERE account_type = ?
            ");
            $stmt->execute([(float)$rate, $account_type]);
        }
        
        $conn->commit();
        $notification = '<div class="success">Interest rates updated successfully!</div>';
        
    } catch (Exception $e) {
        $conn->rollBack();
        $notification = '<div class="error">Failed to update interest rates.</div>';
    }
}

// Get current rates
$stmt = $conn->prepare("
    SELECT account_type, interest_rate 
    FROM accounts 
    GROUP BY account_type
");
$stmt->execute();
$current_rates = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/styles.css">
    <title>Manage Interest Rates</title>
</head>
<body>
    <div class="nav">
        <div class="navItem">
            <div><a href="../admin/dashboard.php">Admin Dashboard</a></div>
            <div>Admin: <?= htmlspecialchars($_SESSION['username']) ?></div>
            <div><a href="../logout.php">Logout</a></div>
        </div>
    </div>

    <?= $notification ?>

    <div class="Center">
        <div class="wrapper">
            <h2>Manage Interest Rates</h2>
            
            <form method="POST" class="interest-form">
                <div class="rate-group">
                    <label>Savings Account Rate (% APR)</label>
                    <input type="number" name="rates[savings]" step="0.01" min="0" 
                           value="<?= htmlspecialchars($current_rates['savings'] ?? 2.50) ?>" required>
                </div>
                
                <div class="rate-group">
                    <label>Checking Account Rate (% APR)</label>
                    <input type="number" name="rates[checking]" step="0.01" min="0" 
                           value="<?= htmlspecialchars($current_rates['checking'] ?? 0.25) ?>" required>
                </div>
                
                <div class="rate-group">
                    <label>Business Account Rate (% APR)</label>
                    <input type="number" name="rates[business]" step="0.01" min="0" 
                           value="<?= htmlspecialchars($current_rates['business'] ?? 1.50) ?>" required>
                </div>
                
                <button type="submit">Update Interest Rates</button>
            </form>
        </div>
    </div>
</body>
</html> 