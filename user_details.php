<?php
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch user details (name, email, phone, profile picture)
$stmt = $conn->prepare("SELECT name, email, phone_number FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User not found.";
    exit;
}

// Fetch wallet balance separately from wallet table
$walletStmt = $conn->prepare("SELECT amount FROM wallet WHERE user_id = ?");
$walletStmt->bind_param("i", $userId);
$walletStmt->execute();
$walletResult = $walletStmt->get_result();
$wallet = $walletResult->fetch_assoc();

$walletBalance = $wallet ? $wallet['amount'] : 0.00;

// Split full name into first and last name
$nameParts = explode(' ', $user['name']);
$firstName = $nameParts[0];
$lastName = isset($nameParts[1]) ? $nameParts[1] : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $newFirstName = trim($_POST['first_name']);
  $newLastName = trim($_POST['last_name']);
  $newPhone = trim($_POST['phone_number']);
  
  // Merge new first + last name
  $newFullName = $newFirstName . ' ' . $newLastName;

  $updateStmt = $conn->prepare("UPDATE users SET name = ?, phone_number = ? WHERE id = ?");
  $updateStmt->bind_param("ssi", $newFullName, $newPhone, $userId);

  if ($updateStmt->execute()) {
      // Refresh data after update
      header("Location: " . $_SERVER['PHP_SELF']);
      exit();
  } else {
      echo "<script>alert('Failed to update information');</script>";
  }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Account Details - Lebanon Tours</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="css/userDetailPage.css">
</head>
<body>

<div class="account-container">
  <div class="profile-card">
  
    <h2><?= htmlspecialchars($firstName) ?> <?= htmlspecialchars($lastName) ?></h2>
    <p><?= htmlspecialchars($user['email']) ?></p>

    <div class="balance-card">
      <h3>Travel Balance</h3>
      <p style="font-size: 1.8rem; margin: 1rem 0">$<?= number_format($walletBalance, 2) ?></p>

    </div>
  </div>

  <div class="main-content">
    <div class="nav-tabs">
      <button class="tab-btn" onclick="switchTab('bookings')">Account</button>
   
    </div>

    <div class="booking-history tab-content" id="bookingsTab">
    <div class="form-section">
          <h3 class="form-section-title">
            <i class="fas fa-user-circle"></i> Personal Information
          </h3>
          <form class="detail-form" method="POST" action="">
  <div class="form-section">
    <h3 class="form-section-title">
      <i class="fas fa-user-circle"></i> Personal Information
    </h3>
    <div class="form-grid">
      <div class="form-row">
        <label>First Name</label>
        <input type="text" name="first_name" value="<?= htmlspecialchars($firstName) ?>" required />
      </div>
      <div class="form-row">
        <label>Last Name</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($lastName) ?>" required />
      </div>
      <div class="form-row">
        <label>Email Address</label>
        <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly />
      </div>
      <div class="form-row">
        <label>Phone Number</label>
        <input type="tel" name="phone_number" value="<?= isset($user['phone_number']) ? htmlspecialchars($user['phone_number']) : '' ?>" required />
      </div>
    </div>
    <div class="action-buttons" style="margin-top: 1.5rem">
      <button type="submit" class="save-changes-btn">
        <i class="fas fa-save"></i> Save Personal Information
      </button>
    </div>
  </div>
</form>

        
        </div>



  </div>
</div>

<script>
function switchTab(tabId) {
  document.querySelectorAll(".tab-btn, .tab-content").forEach((element) => {
    element.classList.remove("active");
  });
  document.querySelector(`[onclick="switchTab('${tabId}')"]`).classList.add("active");
  document.getElementById(`${tabId}Tab`).classList.add("active");
}

document.addEventListener("DOMContentLoaded", () => {
  switchTab("bookings");
});

function savePersonalInfo() {
  const saveBtn = document.querySelector(".save-changes-btn");

  saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
  saveBtn.disabled = true;

  setTimeout(() => {
    saveBtn.innerHTML = '<i class="fas fa-check"></i> Saved!';
    saveBtn.disabled = false;

    setTimeout(() => {
      saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Personal Information';
    }, 2000);
  }, 1500);
}
</script>

</body>
</html>
