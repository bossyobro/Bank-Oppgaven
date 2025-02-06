<?php
session_start();
require "db.php";
require "auth.php";
require "includes/validators.php";  // Add validator functions

checkAuth();

$notification = null;
$conn = getDbConnection();
$username = $_SESSION['username'];

// Get account ID from URL and verify ownership
$account_id = isset($_GET['account']) ? (int)$_GET['account'] : 0;
$stmt = $conn->prepare("
    SELECT a.*, u.username 
    FROM accounts a 
    JOIN users u ON a.user_id = u.id 
    WHERE a.id = ? AND u.username = ?
");
$stmt->execute([$account_id, $username]);
$account = $stmt->fetch();

if (!$account) {
    header("Location: accounts.php");
    exit;
}

// Handle transaction submission
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $transaction_type = $_POST['transaction_type'];
    $amount = (float)$_POST['amount'];
    $to_account = isset($_POST['to_account']) ? $_POST['to_account'] : null;
    
    try {
        if ($amount <= 0) {
            throw new Exception("Please enter a valid amount.");
        }

        if ($transaction_type === 'transfer') {
            if (!validateAccountNumber($to_account)) {
                throw new Exception("Invalid account number format.");
            }
        }

        $conn->beginTransaction();
        
        switch ($transaction_type) {
            case 'deposit':
                // Add money to account
                $stmt = $conn->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
                $stmt->execute([$amount, $account_id]);
                break;
                
            case 'withdrawal':
                // Check sufficient balance
                if ($amount > $account['balance']) {
                    throw new Exception("Insufficient funds");
                }
                // Subtract money from account
                $stmt = $conn->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?");
                $stmt->execute([$amount, $account_id]);
                break;
                
            case 'transfer':
                // Verify target account exists
                $stmt = $conn->prepare("SELECT id FROM accounts WHERE account_number = ?");
                $stmt->execute([$to_account]);
                $target_account = $stmt->fetch();
                
                if (!$target_account) {
                    throw new Exception("Target account not found");
                }
                
                if ($amount > $account['balance']) {
                    throw new Exception("Insufficient funds");
                }
                
                // Subtract from source account
                $stmt = $conn->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?");
                $stmt->execute([$amount, $account_id]);
                
                // Add to target account
                $stmt = $conn->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
                $stmt->execute([$amount, $target_account['id']]);
                break;
        }
        
        // Log the activity
        logActivity($conn, $account['user_id'], $transaction_type, json_encode([
            'amount' => $amount,
            'from_account' => $account['account_number'],
            'to_account' => $to_account,
        ]));
        
        // Log the transaction
        $stmt = $conn->prepare("
            INSERT INTO transactions (account_id, transaction_type, amount, to_account) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$account_id, $transaction_type, $amount, $to_account]);
        
        $conn->commit();
        $notification = '<div class="success">Transaction completed successfully!</div>';
        
        // Refresh account data
        $stmt = $conn->prepare("SELECT * FROM accounts WHERE id = ?");
        $stmt->execute([$account_id]);
        $account = $stmt->fetch();
        
    } catch (Exception $e) {
        $conn->rollBack();
        $notification = '<div class="error">' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Get transaction history
$stmt = $conn->prepare("
    SELECT t.*, a2.account_number as to_account_number 
    FROM transactions t 
    LEFT JOIN accounts a2 ON t.to_account = a2.account_number 
    WHERE t.account_id = ? 
    ORDER BY t.transaction_date DESC
");
$stmt->execute([$account_id]);
$transactions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./static/styles.css">
    <title>Account Transactions</title>
</head>
<body>
    <div class="nav">
        <div class="navItem">
            <div><a href="accounts.php">My Accounts</a></div>
            <div>Balance: kr <?= number_format($account['balance'], 2) ?></div>
            <div><a href="logout.php">Logout</a></div>
        </div>
    </div>

    <?= $notification ?>

    <div class="Center">
        <div class="wrapper">
            <h2><?= ucfirst($account['account_type']) ?> Account</h2>
            <p>Account Number: <?= htmlspecialchars($account['account_number']) ?></p>
            
            <form method="POST" class="transaction-form">
                <h3>New Transaction</h3>
                <select name="transaction_type" id="transaction_type" required>
                    <option value="deposit">Deposit</option>
                    <option value="withdrawal">Withdrawal</option>
                    <option value="transfer">Transfer</option>
                </select>
                
                <input type="number" name="amount" step="0.01" min="0.01" 
                       placeholder="Amount" required>
                
                <div id="transfer_fields" style="display: none;">
                    <input type="text" name="to_account" 
                           placeholder="Recipient Account Number">
                </div>
                
                <button type="submit">Submit Transaction</button>
            </form>

            <h3>Transaction History</h3>
            <div class="transaction-list">
                <?php foreach ($transactions as $trans): ?>
                    <div class="transaction-item">
                        <div class="transaction-type">
                            <?= ucfirst($trans['transaction_type']) ?>
                        </div>
                        <div class="transaction-amount">
                            kr <?= number_format($trans['amount'], 2) ?>
                        </div>
                        <?php if ($trans['to_account']): ?>
                            <div class="transaction-details">
                                To: <?= htmlspecialchars($trans['to_account']) ?>
                            </div>
                        <?php endif; ?>
                        <div class="transaction-date">
                            <?= $trans['transaction_date'] ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('transaction_type').addEventListener('change', function() {
            const transferFields = document.getElementById('transfer_fields');
            transferFields.style.display = this.value === 'transfer' ? 'block' : 'none';
        });
    </script>
</body>
</html> 