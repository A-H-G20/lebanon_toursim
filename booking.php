<?php
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$packageId = $_GET['package_id'] ?? 0;

// Fetch package details
$stmt = $conn->prepare("SELECT * FROM package WHERE package_id = ?");
$stmt->bind_param("i", $packageId);
$stmt->execute();
$package = $stmt->get_result()->fetch_assoc();

// Fetch user wallet balance
$userStmt = $conn->prepare("SELECT amount FROM wallet WHERE user_id = ?");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$walletBalance = $user['amount'] ?? 0;

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $travelDate = $_POST['travel_date'] ?? date('Y-m-d');
    $spotCount = $_POST['spot_count'] ?? 1;
    $basePrice = $_POST['base_price'] ?? $package['unit_price'];
    $paymentMethod = $_POST['payment_method'] ?? '';
    $enteredSerial = trim($_POST['serial_code'] ?? '');

    $totalPrice = $basePrice * $spotCount;

    // Check for Serial Discount
    if (!empty($enteredSerial)) {
        $discountCheck = $conn->prepare("SELECT * FROM discount WHERE serial = ?");
        $discountCheck->bind_param("s", $enteredSerial);
        $discountCheck->execute();
        $discountData = $discountCheck->get_result()->fetch_assoc();

        if ($discountData) {
            if ($discountData['status'] === 'accepted') {
                $discountAmount = $discountData['amount'];
                $totalPrice -= $discountAmount;
                if ($totalPrice < 0) $totalPrice = 0;
            } elseif ($discountData['status'] === 'freeze') {
                echo "<script>alert('This discount code is frozen and not usable.'); window.history.back();</script>";
                exit();
            }
        } else {
            echo "<script>alert('Serial number is not correct.'); window.history.back();</script>";
            exit();
        }
    }

    // Wallet or Credit Card Payment
    if ($paymentMethod === 'wallet') {
        if ($walletBalance < $totalPrice) {
            echo "<script>alert('You do not have enough money in your wallet!'); window.history.back();</script>";
            exit();
        } else {
            $newBalance = $walletBalance - $totalPrice;
            $updateWallet = $conn->prepare("UPDATE wallet SET amount = ? WHERE user_id = ?");
            $updateWallet->bind_param("di", $newBalance, $userId);
            $updateWallet->execute();
        }
    }
    elseif ($paymentMethod === 'credit') {
        $cardNumber = $_POST['card_number'] ?? '';
        $expiryDate = $_POST['expiry_date'] ?? '';
        $cvv = $_POST['cvv'] ?? '';

        if (empty($cardNumber) || empty($expiryDate) || empty($cvv)) {
            echo "<script>alert('Please fill in all credit card fields!'); window.history.back();</script>";
            exit();
        }
    }
    else {
        echo "<script>alert('Please select a payment method.'); window.history.back();</script>";
        exit();
    }

    // Insert into booking
    $bookingStmt = $conn->prepare("INSERT INTO booking (booking_date, user_id, package_id, travel_date, payment_status, total_price) VALUES (NOW(), ?, ?, ?, 'Paid', ?)");
    $bookingStmt->bind_param("iisd", $userId, $packageId, $travelDate, $totalPrice);
    $bookingStmt->execute();
    $bookingId = $conn->insert_id;

    // Insert into payment
    $paymentStmt = $conn->prepare("INSERT INTO payment (booking_id, amount, payment_date) VALUES (?, ?, NOW())");
    $paymentStmt->bind_param("id", $bookingId, $totalPrice);
    $paymentStmt->execute();

    // Reduce available spots
    $updateSpots = $conn->prepare("UPDATE package SET available_spots = available_spots - ? WHERE package_id = ?");
    $updateSpots->bind_param("ii", $spotCount, $packageId);
    $updateSpots->execute();

    header("Location: booking_success.php?booking_id=$bookingId");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Tour - Lebanon Tours</title>
    <link rel="stylesheet" href="css/reviewPage.css">
    <style>
        .booking-container { max-width: 700px; margin: 4rem auto; padding: 2rem; background: #f9f9f9; border-radius: 16px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label { font-weight: bold; display: block; margin-bottom: 0.5rem; }
        .form-group input, .form-group select { width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 8px; }
        .submit-btn { background-color: var(--primary, #119DA4); color: white; padding: 0.8rem 1.5rem; border: none; border-radius: 10px; font-size: 1rem; cursor: pointer; transition: background 0.2s ease; width: 100%; }
        .submit-btn:hover { background-color: #0c7b82; }
        .credit-info { display: none; }
    </style>
</head>
<body>

<div class="booking-container">
    <h2>Book: <?= htmlspecialchars($package['package_name']) ?></h2>

    <form method="POST">
        <input type="hidden" name="base_price" id="base_price" value="<?= $package['unit_price'] ?>">

        <div class="form-group">
            <label for="travel_date">Select Travel Date:</label>
            <input type="date" name="travel_date" id="travel_date" required min="<?= date('Y-m-d') ?>">
        </div>

        <div class="form-group">
            <label for="spot_count">Number of Spots:</label>
            <input type="number" name="spot_count" id="spot_count" value="1" min="1" max="<?= $package['available_spots'] ?>" required>
        </div>

        <div class="form-group">
            <label for="payment_method">Payment Method:</label>
            <select name="payment_method" id="payment_method" required>
                <option value="">-- Select Payment Method --</option>
                <option value="wallet">Wallet (Balance: $<?= number_format($walletBalance, 2) ?>)</option>
                <option value="credit">Credit Card</option>
            </select>
        </div>

        <div class="credit-info" id="creditInfo">
            <div class="form-group">
                <label for="card_number">Card Number:</label>
                <input type="text" name="card_number" id="card_number">
            </div>
            <div class="form-group">
                <label for="expiry_date">Expiry Date (MM/YY):</label>
                <input type="text" name="expiry_date" id="expiry_date">
            </div>
            <div class="form-group">
                <label for="cvv">CVV:</label>
                <input type="text" name="cvv" id="cvv">
            </div>
        </div>

        <div class="form-group">
            <label for="serial_code">Discount Serial Code (optional):</label>
            <input type="text" name="serial_code" id="serial_code" placeholder="Enter your discount serial code">
        </div>

        <div class="form-group">
            <label>Total Price (USD):</label>
            <input type="text" id="total_price_display" readonly value="<?= $package['unit_price'] ?>">
        </div>

        <button type="submit" class="submit-btn"><i class="fas fa-ticket-alt"></i> Confirm Booking</button>
    </form>
</div>

<script>
// Show/hide credit card fields
document.getElementById('payment_method').addEventListener('change', function() {
    const creditInfo = document.getElementById('creditInfo');
    creditInfo.style.display = (this.value === 'credit') ? 'block' : 'none';
});

// Update price dynamically
const spotInput = document.getElementById('spot_count');
const basePrice = parseFloat(document.getElementById('base_price').value);
const totalPriceDisplay = document.getElementById('total_price_display');

spotInput.addEventListener('input', function() {
    const spotCount = parseInt(this.value) || 1;
    totalPriceDisplay.value = (basePrice * spotCount).toFixed(2);
});
</script>

</body>
</html>
