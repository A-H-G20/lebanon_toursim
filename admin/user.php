<?php
session_start();
require 'connection.php';

// Fetch users
$search = $_GET['search'] ?? '';
$searchTerm = '%' . $conn->real_escape_string($search) . '%';

if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE role = 'user' AND (name LIKE ? OR email LIKE ?)");
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
} else {
    $stmt = $conn->prepare("SELECT * FROM users WHERE role = 'user'");
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Management</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<div class="dashboard-container">
<?php include 'navbar.php'; ?>

<main class="main-content">
  <div class="header">
    <div class="header-left">
      <h1>Manage Users</h1>
    </div>
  </div>

  <form method="GET" style="margin-bottom: 1rem; display: flex; justify-content: flex-end;">
    <input type="text" name="search" placeholder="Search user..." 
           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
           style="padding: 8px; border-radius: 5px; border: 1px solid #ccc; width: 250px;">
    <button type="submit" class="btn-primary" style="margin-left: 10px;">Search</button>
  </form>

  <table style="width: 100%; border-collapse: collapse;">
    <thead style="background-color: #f2f2f2;">
      <tr>
        <th style="padding: 10px;">Name</th>
        <th style="padding: 10px;">Email</th>
        <th style="padding: 10px;">Phone</th>
        <th style="padding: 10px;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td style="padding: 10px;"><?= htmlspecialchars($row['name']) ?></td>
            <td style="padding: 10px;"><?= htmlspecialchars($row['email']) ?></td>
            <td style="padding: 10px;"><?= htmlspecialchars($row['phone_number']) ?></td>
            <td style="padding: 10px;">
              <div class="action-buttons">
                <button class="btn-primary" onclick="openWalletModal(<?= $row['id'] ?>)">Add to Wallet</button>
                <button class="btn-danger" onclick="deleteUser(<?= $row['id'] ?>)">Delete</button>
              </div>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="4" style="padding: 20px; text-align:center;">No users found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

</main>
</div>

<!-- Wallet Modal -->
<div id="walletModal" class="wallet-modal">
  <div class="wallet-modal-content">
    <span class="close-btn" onclick="closeWalletModal()">&times;</span>
    <h2>Add Amount to Wallet</h2>
    <form method="POST" action="wallet_add.php">
      <input type="hidden" name="user_id" id="walletUserId">
      <div class="form-group">

        <input type="number" name="amount" required min="1" placeholder="Enter amount to add">
      </div>
      <button type="submit" class="btn-primary" style="margin-top: 1rem;">Add Amount</button>
    </form>
  </div>
</div>

<script>
function deleteUser(id) {
  if (confirm("Are you sure you want to delete this user?")) {
    window.location.href = "delete_user.php?id=" + id;
  }
}

function openWalletModal(userId) {
  document.getElementById('walletModal').style.display = 'block';
  document.getElementById('walletUserId').value = userId;
}

function closeWalletModal() {
  document.getElementById('walletModal').style.display = 'none';
}
</script>
<style>
/* Wallet Modal Overlay */
.wallet-modal {
  display: none;
  position: fixed;
  z-index: 2000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  overflow: auto;
}

/* Wallet Modal Content */
.wallet-modal-content {
  background: white;
  margin: 8% auto;
  padding: 2rem;
  border-radius: 16px;
  width: 90%;
  max-width: 400px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
  position: relative;
  animation: slideIn 0.3s ease-out;
}

/* Close Button */
.close-btn {
  position: absolute;
  right: 1.5rem;
  top: 1rem;
  font-size: 1.8rem;
  font-weight: bold;
  color: #333;
  cursor: pointer;
  transition: color 0.3s;
}

.close-btn:hover {
  color: #e74c3c;
}

/* Form inside Modal */
.wallet-modal-content form {
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}

.wallet-modal-content input[type="number"] {
  width: 100%;
  padding: 0.8rem;
  border: 1px solid #ccc;
  border-radius: 10px;
  font-size: 1rem;
  transition: 0.3s;
}

.wallet-modal-content input[type="number"]:focus {
  border-color: #2c3e50;
  box-shadow: 0 0 0 3px rgba(17, 157, 164, 0.2);
  outline: none;
}

/* Submit Button */
.wallet-modal-content button[type="submit"] {
  background: #2c3e50;
  border: none;
  color: white;
  padding: 0.8rem 1rem;
  border-radius: 10px;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.3s, transform 0.3s;
}

.wallet-modal-content button[type="submit"]:hover {
  background: #2c3e50;
  transform: translateY(-2px);
}

/* Animation */
@keyframes slideIn {
  from {
    transform: translateY(-30px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
</style>


</body>
</html>
