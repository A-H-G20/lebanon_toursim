<?php
// add_city.php
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);

    $stmt = $conn->prepare("INSERT INTO city (name) VALUES (?)");
    $stmt->bind_param("s", $name);

    if ($stmt->execute()) {
        header('Location: city.php');
        exit();
    } else {
        echo "Error adding city: " . $conn->error;
    }
}
?>
