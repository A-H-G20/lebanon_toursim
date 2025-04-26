<?php
session_start();
require 'connection.php';

// Check if ID is passed
if (!isset($_GET['id'])) {
    header('Location: admin.php');
    exit();
}

$admin_id = intval($_GET['id']);

// Fetch admin data
$stmt = $conn->prepare("SELECT id, name, email, phone_number FROM users WHERE id = ? AND role = 'admin'");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin) {
    echo "Admin not found.";
    exit();
}

// Update admin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone_number']);
    $password = trim($_POST['password']);

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone_number = ?, password = ? WHERE id = ? AND role = 'admin'");
        $update_stmt->bind_param("ssssi", $name, $email, $phone, $hashedPassword, $admin_id);
    } else {
        $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone_number = ? WHERE id = ? AND role = 'admin'");
        $update_stmt->bind_param("sssi", $name, $email, $phone, $admin_id);
    }

    if ($update_stmt->execute()) {
        header("Location: admin.php");
        exit();
    } else {
        echo "Error updating admin: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/admin.css"> <!-- Link to your admin panel CSS -->
</head>
<body>

<div class="dashboard-container">
  <?php include 'navbar.php'; ?>

  <main class="main-content">
    <div class="header">
      <h1>Edit Admin</h1>
    </div>

    <form action="" method="POST" class="form-grid" style="max-width: 600px;">
      <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($admin['name']) ?>" required>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
      </div>

      <div class="form-group">
        <label>Phone Number</label>
        <input type="text" name="phone_number" value="<?= htmlspecialchars($admin['phone_number']) ?>" required>
      </div>

      <div class="form-group">
        <label>Password (Leave empty if you don't want to change)</label>
        <input type="password" name="password" placeholder="New password (optional)">
      </div>

      <div style="margin-top: 2rem;">
        <button type="submit" class="btn-primary">
          <i class="fas fa-save"></i> Save Changes
        </button>
        <a href="admin.php" class="btn-primary" style="background: var(--text-light); margin-left: 1rem;">Cancel</a>
      </div>
    </form>
  </main>
</div>

</body>
</html>

<style>
    :root {
  --primary: #2a6559;
  --accent: #e67e22;
  --light: #f9f5f0;
  --text: #2c3e50;
  --border: #e5e7eb;
  --shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
  --text-light: #64748b;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Plus Jakarta Sans", sans-serif;
}

body {
  background: #f8fafc;
}

.dashboard-container {
  display: grid;
  grid-template-columns: 240px 1fr;
  min-height: 100vh;
}

.sidebar {
  background: white;
  padding: 2rem;
  border-right: 1px solid var(--border);
  position: sticky;
  top: 0;
  height: 100vh;
}

.nav-header {
  font-family: "Playfair Display", serif;
  font-size: 1.5rem;
  margin-bottom: 2rem;
  color: var(--primary);
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  margin: 0.5rem 0;
  border-radius: 8px;
  color: var(--text);
  transition: all 0.3s ease;
}

.nav-item:hover {
  background: var(--light);
  color: var(--primary);
}

.main-content {
  padding: 2rem;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.header h1 {
  color: var(--primary);
}

.btn-primary {
  background: var(--primary);
  color: white;
  padding: 0.8rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.8rem;
}

.btn-primary:hover {
  background: #1f4a41;
  transform: translateY(-2px);
}

/* Form */
.form-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
  margin-top: 2rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: var(--text);
}

.form-group input {
  padding: 0.8rem;
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 1rem;
}

.form-group input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(42, 101, 89, 0.1);
}

/* Table */
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 2rem;
}

thead {
  background: #f2f2f2;
}

th, td {
  padding: 1rem;
  border: 1px solid var(--border);
  text-align: center;
}

.action-buttons {
  display: flex;
  gap: 10px;
  justify-content: center;
}

.action-buttons .btn-primary {
  background: #119DA4;
}

.action-buttons .btn-primary:hover {
  background: #0c7e83;
}

.action-buttons .btn-danger {
  background: #e74c3c;
}

.action-buttons .btn-danger:hover {
  background: #c0392b;
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .dashboard-container {
    grid-template-columns: 1fr;
  }

  .sidebar {
    display: none;
  }

  .main-content {
    padding: 1rem;
  }
}

</style>