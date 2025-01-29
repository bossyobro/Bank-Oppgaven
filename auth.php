<?php


function checkAuth() {
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit;
    }
}

function checkAdmin() {
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit;
    }
    
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT user_type FROM users WHERE username = ?");
    $stmt->execute([$_SESSION['username']]);
    $user_type = $stmt->fetchColumn();
    
    if ($user_type !== 'admin') {
        header("Location: index.php");
        exit;
    }
}