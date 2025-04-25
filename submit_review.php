<?php
require 'config.php';

$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$rating = (int) ($_POST['rating'] ?? 0);
$package_id = (int) ($_POST['package_id'] ?? 0);

$imageName = null;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $imageName = time() . '_' . uniqid() . '.' . $ext;
    move_uploaded_file($_FILES['photo']['tmp_name'], 'uploads/' . $imageName);
}

$stmt = $conn->prepare("INSERT INTO review (name, review_description, package_id, rating, image) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssiss", $name, $description, $package_id, $rating, $imageName);
if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}
?>
