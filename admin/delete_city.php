<?php
require 'connection.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM city WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header('Location: city.php');
    } else {
        echo "Error deleting city: " . $conn->error;
    }
}
?>
