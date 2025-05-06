<?php

require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);

    $stmt = $conn->prepare("INSERT INTO package_type (name) VALUES (?)");
    $stmt->bind_param("s", $name);

    if ($stmt->execute()) {
        header('Location: package_type.php');
        exit();
    } else {
        echo "Error adding package_type: " . $conn->error;
    }
}
?>
