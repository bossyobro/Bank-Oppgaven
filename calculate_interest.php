<?php
require "db.php";

function calculateInterest() {
    $conn = getDbConnection();
    $log = [];
    
    try {
        $conn->beginTransaction();
        
        $stmt = $conn->prepare("
            SELECT id, account_number, balance, account_type, interest_rate 
            FROM accounts 
            WHERE balance > 0
        ");
        $stmt->execute();
        $accounts = $stmt->fetchAll();
        
        foreach ($accounts as $account) {
            $dailyRate = $account['interest_rate'] / 365;
            $interestAmount = $account['balance'] * ($dailyRate / 100);
            
            if ($interestAmount > 0) {
                $stmt = $conn->prepare("
                    UPDATE accounts 
                    SET balance = balance + ? 
                    WHERE id = ?
                ");
                $stmt->execute([$interestAmount, $account['id']]);
                
                $stmt = $conn->prepare("
                    INSERT INTO transactions 
                    (account_id, transaction_type, amount, description) 
                    VALUES (?, 'interest', ?, ?)
                ");
                $stmt->execute([
                    $account['id'],
                    $interestAmount,
                    'Daily interest at ' . $account['interest_rate'] . '% APR'
                ]);
                
                $log[] = sprintf(
                    "Added %.2f kr interest to account %s (%.2f%% APR)",
                    $interestAmount,
                    $account['account_number'],
                    $account['interest_rate']
                );
            }
        }
        
        $conn->commit();
        return ['success' => true, 'log' => $log];
        
    } catch (Exception $e) {
        $conn->rollBack();
        return [
            'success' => false, 
            'error' => $e->getMessage(),
            'log' => $log
        ];
    }
}

