<?php
require 'connection.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Step 1: Delete related bookings first
    $bookingStmt = $conn->prepare("DELETE FROM booking WHERE user_id = ?");
    $bookingStmt->bind_param("i", $id);
    $bookingStmt->execute();

    // Step 2: Now delete the user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: user.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
