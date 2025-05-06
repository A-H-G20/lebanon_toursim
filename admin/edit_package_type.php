<?php
require 'connection.php';

// Check if ID exists
if (!isset($_GET['id'])) {
    header('Location: package_type.php');
    exit();
}

$id = intval($_GET['id']);

// Fetch the city
$stmt = $conn->prepare("SELECT * FROM package_type WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$city = $result->fetch_assoc();

if (!$city) {
    echo "package_type not found.";
    exit();
}

// Update package_type
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);

    $update = $conn->prepare("UPDATE package_type SET name = ? WHERE id = ?");
    $update->bind_param("si", $name, $id);

    if ($update->execute()) {
        header('Location: package_type.php');
        exit();
    } else {
        echo "Error updating package_type: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit package_type</title>
  <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<div class="dashboard-container">
<main class="main-content">
  <h1>Edit package_type</h1>

  <form method="POST" style="max-width: 600px;">
    <div class="form-grid">
      <div class="form-group">
        <label>package_type Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($city['name']) ?>" required>
      </div>
    </div>

    <div style="margin-top: 2rem;">
      <button type="submit" class="btn-primary">Save Changes</button>
      <a href="package_type.php" class="btn-primary" style="background: var(--text-light);">Cancel</a>
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
  padding: 2rem;
  min-height: 100vh;
}

.main-content {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: var(--shadow);
  max-width: 800px;
  margin: auto;
}

h1 {
  margin-bottom: 2rem;
  color: var(--primary);
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
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

.form-group input,
.form-group select {
  padding: 0.8rem;
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 1rem;
  transition: 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
  border-color: var(--primary);
  outline: none;
  box-shadow: 0 0 0 3px rgba(42, 101, 89, 0.1);
}

.btn-primary {
  background: var(--primary);
  color: white;
  padding: 0.8rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
  transition: 0.3s ease;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.8rem;
}

.btn-primary:hover {
  background: #1f4a41;
  transform: translateY(-2px);
}

.btn-primary[style*="background: var(--text-light);"] {
  background: var(--text-light);
}

.btn-primary[style*="background: var(--text-light);"]:hover {
  background: #94a3b8;
}

/* Responsive Design */
@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}

</style>