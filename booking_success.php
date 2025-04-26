<?php
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch user bookings with package name + image
$stmt = $conn->prepare("
    SELECT 
        b.*, 
        p.package_name, 
        p.image
    FROM booking b
    LEFT JOIN package p ON b.package_id = p.package_id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$bookings = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Bookings</title>
    <link rel="stylesheet" href="css/reviewPage.css">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            padding: 0;
        }
        .booking-container {
            max-width: 1100px;
            margin: 4rem auto;
            padding: 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }
        h2, h3 {
            color: #119DA4;
        }
        table {
            width: 100%;
            margin-top: 2rem;
            border-collapse: collapse;
            border-radius: 12px;
            overflow: hidden;
        }
        th, td {
            padding: 1rem;
            text-align: center;
        }
        th {
            background: #119DA4;
            color: white;
            font-size: 1rem;
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        tr:hover {
            background: #eef6f8;
        }
        img.package-img {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .submit-btn {
            margin-top: 2rem;
            background-color: #119DA4;
            color: white;
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s ease;
        }
        .submit-btn:hover {
            background-color: #0c7b82;
        }
        .no-bookings {
            margin-top: 2rem;
            font-size: 1.2rem;
            color: #666;
        }
    </style>
</head>
<body>

<div class="booking-container" style="text-align: center;">
    <h2>🎉 Booking Successful!</h2>
    <p>Thank you for booking with us.</p>

    <h3 style="margin-top: 3rem;">Your Previous Bookings</h3>

    <?php if ($bookings->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Package Name</th>
                    <th>Travel Date</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Booking Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($b = $bookings->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php if (!empty($b['image'])): ?>
                                <img src="uploads/<?= htmlspecialchars($b['image']) ?>" alt="Package Image" class="package-img">
                            <?php else: ?>
                                <img src="uploads/default.jpg" alt="No Image" class="package-img">
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($b['package_name']) ?></td>
                        <td><?= htmlspecialchars($b['travel_date']) ?></td>
                        <td>$<?= number_format($b['total_price'], 2) ?></td>
                        <td><?= htmlspecialchars($b['payment_status']) ?></td>
                        <td><?= date('d M Y', strtotime($b['booking_date'])) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-bookings">You have no previous bookings yet.</p>
    <?php endif; ?>

    <a href="index.php" class="submit-btn">Back to Home</a>
</div>

</body>
</html>
