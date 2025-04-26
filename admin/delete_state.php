<?php
require 'connection.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM state WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header('Location: state.php');
    } else {
        echo "Error deleting state: " . $conn->error;
    }
}
?>
