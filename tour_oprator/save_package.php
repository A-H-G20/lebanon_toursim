<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$userId = $_SESSION['user_id'];

$packageName = $_POST['title'];
$status = $_POST['status']; // <- Not in table, you may ignore or store elsewhere
$packageType = $_POST['type'];
$cityId = $_POST['location'];
$stateId = $_POST['state'];
$startDate = $_POST['startDate'];
$endDate = $_POST['endDate'];
$price = $_POST['price'];
$maxParticipants = $_POST['maxParticipants'];
$description = $_POST['description'];
$availableSpots = $maxParticipants;

// Handle image upload
$imageName = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $imageTmp = $_FILES['image']['tmp_name'];
    $imageName = time() . '_' . basename($_FILES['image']['name']);
    $uploadFolder = '../uploads/';
    $imagePath = $uploadFolder . $imageName;

    if (!is_dir($uploadFolder)) {
        mkdir($uploadFolder, 0777, true);
    }

    move_uploaded_file($imageTmp, $imagePath);
}

// Calculate average duration in days
$from = strtotime($startDate);
$to = strtotime($endDate);
$days = max(1, round(($to - $from) / 86400));
$averageDuration = $days;

// Insert into package
$stmt = $conn->prepare("
    INSERT INTO package (
        user_id, package_name, image, description,
        city_id, state_id, unit_price, available_spots,
        total_spots, package_type, start_date, end_date, average_duration
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "isssiiiiiissd",
    $userId, $packageName, $imageName, $description,
    $cityId, $stateId, $price, $availableSpots,
    $maxParticipants, $packageType, $startDate, $endDate, $averageDuration
);

if ($stmt->execute()) {
    $packageId = $stmt->insert_id;

    foreach ($_POST['dayTitle'] as $index => $title) {
        $desc = $_POST['dayDescription'][$index];
        $start = $_POST['start'][$index];
        $end = $_POST['end'][$index];

        $actStmt = $conn->prepare("
            INSERT INTO activity (name, description, from_time, to_time, package_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $actStmt->bind_param("ssssi", $title, $desc, $start, $end, $packageId);
        $actStmt->execute();
    }

    header("Location: package.php?success=1");
    exit;
} else {
    echo "Error: " . $stmt->error;
}
?>
