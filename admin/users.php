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

// Handle user updates
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'delete':
                    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$_POST['user_id']]);
                    $notification = '<div class="success">User deleted successfully.</div>';
                    break;
                    
                case 'update':
                    $stmt = $conn->prepare("
                        UPDATE users 
                        SET name = ?, email = ?, phone = ?, address = ?, user_type = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $_POST['name'],
                        $_POST['email'],
                        $_POST['phone'],
                        $_POST['address'],
                        $_POST['user_type'],
                        $_POST['user_id']
                    ]);
                    $notification = '<div class="success">User updated successfully.</div>';
                    break;
            }
        } catch (Exception $e) {
            $notification = '<div class="error">Operation failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Get all users
$stmt = $conn->prepare("
    SELECT u.*, COUNT(a.id) as account_count, SUM(a.balance) as total_balance
    FROM users u
    LEFT JOIN accounts a ON u.id = a.user_id
    GROUP BY u.id
    ORDER BY u.username
");
$stmt->execute();
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/styles.css">
    <title>Manage Users</title>
</head>
<body>
    <div class="nav">
        <div class="navItem">
            <div><a href="dashboard.php">Dashboard</a></div>
            <div>Admin: <?= htmlspecialchars($_SESSION['username']) ?></div>
            <div><a href="../logout.php">Logout</a></div>
        </div>
    </div>

    <?= $notification ?>

    <div class="admin-container">
        <h2>Manage Users</h2>
        
        <div class="users-list">
            <?php foreach ($users as $user): ?>
                <div class="user-card">
                    <h3><?= htmlspecialchars($user['username']) ?></h3>
                    <form method="POST" class="user-form">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        
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
                            <label>User Type</label>
                            <select name="user_type" required>
                                <option value="personal" <?= $user['user_type'] == 'personal' ? 'selected' : '' ?>>Personal</option>
                                <option value="business" <?= $user['user_type'] == 'business' ? 'selected' : '' ?>>Business</option>
                                <option value="admin" <?= $user['user_type'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>
                        
                        <div class="user-stats">
                            <p>Accounts: <?= $user['account_count'] ?></p>
                            <p>Total Balance: kr <?= number_format($user['total_balance'] ?? 0, 2) ?></p>
                        </div>
                        
                        <div class="button-group">
                            <button type="submit" name="action" value="update">Update</button>
                            <button type="submit" name="action" value="delete" 
                                    onclick="return confirm('Are you sure you want to delete this user?')" 
                                    class="delete-button">Delete</button>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html> 