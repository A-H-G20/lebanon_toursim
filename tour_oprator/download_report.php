<?php
require 'connection.php';

if (!isset($_GET['booking_id'])) {
    die('Missing booking id');
}

$bookingId = intval($_GET['booking_id']);

// Fetch the booking info
$stmt = $conn->prepare("
    SELECT 
        b.*, 
        p.package_name, 
        u.name AS traveler_name, 
        u.email AS traveler_email, 
        u.phone_number
    FROM booking b
    LEFT JOIN package p ON b.package_id = p.package_id
    LEFT JOIN users u ON b.user_id = u.id
    WHERE b.booking_id = ?
");
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    die('Booking not found');
}

// Set headers for Excel download
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=booking_report_" . $bookingId . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Output table
echo "<table border='1'>";
echo "<tr><th colspan='2'>Booking Report</th></tr>";
echo "<tr><td><strong>Traveler Name</strong></td><td>{$booking['traveler_name']}</td></tr>";
echo "<tr><td><strong>Email</strong></td><td>{$booking['traveler_email']}</td></tr>";
echo "<tr><td><strong>Phone Number</strong></td><td>{$booking['phone_number']}</td></tr>";
echo "<tr><td><strong>Package Name</strong></td><td>{$booking['package_name']}</td></tr>";
echo "<tr><td><strong>Booking Date</strong></td><td>{$booking['booking_date']}</td></tr>";
echo "<tr><td><strong>Travel Date</strong></td><td>{$booking['travel_date']}</td></tr>";
echo "<tr><td><strong>Total Price</strong></td><td>\${$booking['total_price']}</td></tr>";
echo "<tr><td><strong>Status</strong></td><td>{$booking['payment_status']}</td></tr>";
echo "</table>";
?>
