<?php
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $city_id = intval($_POST['city_id']);
    $name = trim($_POST['name']);

    $stmt = $conn->prepare("INSERT INTO state (city_id, name) VALUES (?, ?)");
    $stmt->bind_param("is", $city_id, $name);

    if ($stmt->execute()) {
        header('Location: state.php');
        exit();
    } else {
        echo "Error adding state: " . $conn->error;
    }
}
?>
