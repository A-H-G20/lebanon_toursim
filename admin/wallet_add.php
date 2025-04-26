<?php
session_start();
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = intval($_POST['user_id']);
    $amount = floatval($_POST['amount']);

    // Check if user already has a wallet
    $stmt = $conn->prepare("SELECT * FROM wallet WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $wallet = $stmt->get_result()->fetch_assoc();

    if ($wallet) {
        // Update existing wallet
        $newAmount = $wallet['amount'] + $amount;
        $update = $conn->prepare("UPDATE wallet SET amount = ? WHERE user_id = ?");
        $update->bind_param("di", $newAmount, $userId);
        $update->execute();
    } else {
        // Create new wallet record
        $insert = $conn->prepare("INSERT INTO wallet (user_id, amount) VALUES (?, ?)");
        $insert->bind_param("id", $userId, $amount);
        $insert->execute();
    }

    header("Location: user.php");
    exit();
}
?>
