<?php
require 'connection.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM discount WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header('Location: discount.php');
        exit();
    } else {
        echo "Error deleting discount: " . $conn->error;
    }
} else {
    header('Location: discount.php');
    exit();
}
?>
