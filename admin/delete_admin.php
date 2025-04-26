<?php
require 'connection.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'admin'");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: admin.php");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
