<?php
session_start();
require 'connection.php';

// Fetch States
$search = $_GET['search'] ?? '';
$searchTerm = '%' . $conn->real_escape_string($search) . '%';

if (!empty($search)) {
    $stmt = $conn->prepare("SELECT state.*, city.name AS city_name 
                            FROM state 
                            JOIN city ON state.city_id = city.id 
                            WHERE state.name LIKE ? OR city.name LIKE ?");
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
} else {
    $stmt = $conn->prepare("SELECT state.*, city.name AS city_name 
                            FROM state 
                            JOIN city ON state.city_id = city.id");
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>State Management</title>
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
      <h1>Manage States</h1>
    </div>
    <button class="btn-primary" onclick="toggleStateModal()">
      <i class="fas fa-plus"></i> Add New State
    </button>
  </div>

  <form method="GET" style="margin-bottom: 1rem; display: flex; justify-content: flex-end;">
    <input type="text" name="search" placeholder="Search states or cities..." 
           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
           style="padding: 8px; border-radius: 5px; border: 1px solid #ccc; width: 250px;">
    <button type="submit" class="btn-primary" style="margin-left: 10px;">Search</button>
  </form>

  <table style="width: 100%; border-collapse: collapse;">
    <thead style="background-color: #f2f2f2;">
      <tr>
        <th style="padding: 10px;">State Name</th>
        <th style="padding: 10px;">City</th>
        <th style="padding: 10px;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td style="padding: 10px;"><?= htmlspecialchars($row['name']) ?></td>
            <td style="padding: 10px;"><?= htmlspecialchars($row['city_name']) ?></td>
            <td style="padding: 10px;">
              <div class="action-buttons">
                <a href="edit_state.php?id=<?= $row['id'] ?>" class="btn-primary">Edit</a>
                <button class="btn-danger" onclick="deleteState(<?= $row['id'] ?>)">Delete</button>
              </div>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="3" style="padding: 20px; text-align:center;">No states found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- State Modal -->
  <div class="package-modal" id="stateModal">
    <div class="modal-content">
      <button class="btn-primary" onclick="toggleStateModal()" style="position: absolute; right: 1rem; top: 1rem;">
        <i class="fas fa-times"></i>
      </button>
      <h2 id="modalTitle">Create New State</h2>

      <form method="POST" action="add_state.php">
        <div class="form-grid">
          <div class="form-group">
            <label>State Name</label>
            <input type="text" name="name" required>
          </div>

          <div class="form-group">
            <label>City</label>
            <select name="city_id" required>
              <option value="">Select City</option>
              <?php
              $cities = $conn->query("SELECT id, name FROM city ORDER BY name ASC");
              while ($city = $cities->fetch_assoc()):
              ?>
                <option value="<?= $city['id'] ?>"><?= htmlspecialchars($city['name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
          <button type="submit" class="btn-primary">
            <i class="fas fa-save"></i> Save State
          </button>
          <button type="button" class="btn-primary" style="background: var(--text-light);" onclick="toggleStateModal()">Cancel</button>
        </div>
      </form>

    </div>
  </div>

</main>
</div>

<script>
function toggleStateModal() {
  const modal = document.getElementById("stateModal");
  modal.style.display = modal.style.display === "block" ? "none" : "block";
}

function deleteState(id) {
  if (confirm("Are you sure you want to delete this state?")) {
    window.location.href = "delete_state.php?id=" + id;
  }
}
</script>

</body>
</html>
<style>
    /* Admin Form Styling */
#adminForm {
  margin-top: 2rem;
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: var(--shadow);
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
  font-weight: 600;
  color: var(--text);
  margin-bottom: 0.5rem;
}

.form-group input {
  padding: 0.8rem;
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.form-group input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(42, 101, 89, 0.1);
  outline: none;
}

button.btn-primary {
  background: var(--primary);
  color: white;
  padding: 0.8rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.8rem;
}

button.btn-primary:hover {
  background: #1f4a41;
  transform: translateY(-2px);
}

/* Cancel button style */
button.btn-primary[style*="background: var(--text-light);"] {
  background: var(--text-light);
}

button.btn-primary[style*="background: var(--text-light);"]:hover {
  background: #94a3b8;
}

/* Responsive for Mobile */
@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}

</style>