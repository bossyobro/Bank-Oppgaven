<?php
session_start();
require "db.php";
require "includes/validators.php";

if (isset($_SESSION['username'])) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$_SESSION['username']]);
    $user_id = $stmt->fetchColumn();
    
    logActivity($conn, $user_id, 'logout', null);
}

session_destroy();
header("Location: login.php");
exit;
