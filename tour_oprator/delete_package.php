<?php
require 'connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  die("Unauthorized");
}

$id = $_GET['id'];
$userId = $_SESSION['user_id'];

// Delete only if the package belongs to the logged-in user
$stmt = $conn->prepare("DELETE FROM package WHERE package_id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $userId);
$stmt->execute();

header("Location: operatorDash.html");
exit;
?>
