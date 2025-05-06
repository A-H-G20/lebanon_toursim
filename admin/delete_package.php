<?php
require 'connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  die("Unauthorized");
}

$id = $_GET['id'];

// Delete the package without checking user ownership
$stmt = $conn->prepare("DELETE FROM package WHERE package_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: operatorDash.html");
exit;
?>
