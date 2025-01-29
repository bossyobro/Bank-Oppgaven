<?php
session_start();
require "db.php";
require "auth.php";

checkAuth();

$notification = null;
$conn = getDbConnection();
$username = $_SESSION['username'];

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    try {
        // Verify current password
        if (!password_verify($_POST['current_password'], $user['password'])) {
            throw new Exception("Current password is incorrect");
        }
        
        $updates = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'address' => $_POST['address']
        ];
        
        // If new password is provided, update it
        if (!empty($_POST['new_password'])) {
            if (strlen($_POST['new_password']) < 8) {
                throw new Exception("New password must be at least 8 characters long");
            }
            $updates['password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        }
        
        // Build update query
        $sql = "UPDATE users SET " . implode(" = ?, ", array_keys($updates)) . " = ? WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([...array_values($updates), $username]);
        
        $notification = '<div class="success">Profile updated successfully!</div>';
        
        // Refresh user data
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
    } catch (Exception $e) {
        $notification = '<div class="error">' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Get user's accounts
$stmt = $conn->prepare("
    SELECT a.*, 
           (SELECT COUNT(*) FROM transactions WHERE account_id = a.id) as transaction_count
    FROM accounts a 
    WHERE user_id = ?
");
$stmt->execute([$user['id']]);
$accounts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./static/styles.css">
    <title>My Profile</title>
</head>
<body>
    <div class="nav">
        <div class="navItem">
            <div><a href="accounts.php">My Accounts</a></div>
            <div>User: <?= htmlspecialchars($username) ?></div>
            <div><a href="logout.php">Logout</a></div>
        </div>
    </div>

    <?= $notification ?>

    <div class="Center">
        <div class="wrapper">
            <h2>My Profile</h2>
            
            <form method="POST" class="profile-form">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" value="<?= htmlspecialchars($user['address']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Current Password (required for any changes)</label>
                    <input type="password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label>New Password (leave blank to keep current)</label>
                    <input type="password" name="new_password" minlength="8">
                </div>
                
                <button type="submit">Update Profile</button>
            </form>
            
            <div class="account-summary">
                <h3>Account Summary</h3>
                <?php foreach ($accounts as $account): ?>
                    <div class="account-item">
                        <div class="account-info">
                            <strong><?= ucfirst($account['account_type']) ?> Account</strong>
                            <div><?= htmlspecialchars($account['account_number']) ?></div>
                        </div>
                        <div class="account-balance">
                            <div>Balance: kr <?= number_format($account['balance'], 2) ?></div>
                            <div class="transaction-count">
                                <?= $account['transaction_count'] ?> transactions
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html> 