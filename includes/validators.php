<?php

function validateAccountNumber($accountNumber) {
    if (!preg_match('/^\d{4}\.\d{2}\.\d{5}$/', $accountNumber)) {
        return false;
    }
    
    list($bankCode, $typeCode, $accountCode) = explode('.', $accountNumber);
    
    if ($bankCode !== '1234') {
        return false;
    }
    
    $validTypes = ['01', '02', '03']; 
    if (!in_array($typeCode, $validTypes)) {
        return false;
    }
    
    return true;
}

function logActivity($conn, $userId, $activity, $details = null) {
    $stmt = $conn->prepare("
        INSERT INTO logs (user_id, activity, details, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $userId,
        $activity,
        $details,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);
} 