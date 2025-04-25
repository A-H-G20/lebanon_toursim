<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Package Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/operatorDash.css" />
  </head>
  <body>
    <div class="dashboard-container">
      <div class="menu-overlay" onclick="closeMobileMenu()"></div>
      <button class="menu-toggle" id="menuToggle">
        <span></span>
        <span></span>
        <span></span>
      </button>

      <nav class="sidebar" id="sidebar">
        <div class="nav__logo">
          <h2>Tourism</h2>
        </div>
        <div class="nav-item" onclick="window.location.href='../OperatorHtml/operatorDash.html'">
          <i class="fas fa-box"></i><span>Packages</span>
        </div>
        <div class="nav-item" onclick="window.location.href='../OperatorHtml/bookinDash.html'">
          <i class="fas fa-users"></i><span>Booking</span>
        </div>
        <div class="nav-item" onclick="window.location.href='../OperatorHtml/notficationDash.html'">
          <i class="fas fa-cog"></i><span>Notification</span>
        </div>
        <div class="nav-item" onclick="window.location.href='../UserHtml/loginPage.html'">
          <i class="fas fa-door-open"></i><span>Logout</span>
        </div>
      </nav>

      <main class="main-content">
        <div class="header">
          <div class="header-left">
            <h1>Manage Packages</h1>
          
          </div>
          <button class="btn-primary" onclick="togglePackageModal()">
            <i class="fas fa-plus"></i> Create New
          </button>
        </div>

        <div class="package-grid" id="packageContainer"></div>
        <?php

session_start();
require 'connection.php';

$userId = $_SESSION['user_id'] ?? 0;

$search = $_GET['search'] ?? '';
$searchTerm = '%' . $conn->real_escape_string($search) . '%';

if (!empty($search)) {
  $stmt = $conn->prepare("SELECT * FROM package WHERE user_id = ? AND package_name LIKE ?");
  $stmt->bind_param("is", $userId, $searchTerm);
} else {
  $stmt = $conn->prepare("SELECT * FROM package WHERE user_id = ?");
  $stmt->bind_param("i", $userId);
}

$stmt->execute();
$result = $stmt->get_result();
?>


<h2 style="margin-top: 2rem;">Your Packages</h2>

<!-- Search Form (placed above the table) -->
<form method="GET" style="margin-bottom: 1rem; display: flex; justify-content: flex-end;">
  <input 
    type="text" 
    name="search" 
    placeholder="Search packages by name..." 
    value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
    style="padding: 8px; border-radius: 5px; border: 1px solid #ccc; width: 250px;"
  />
  <button type="submit" class="btn-primary" style="margin-left: 10px;">Search</button>
</form>


<!-- Package Table -->
<table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
  <thead style="background-color: #f2f2f2;">
    <tr>
      <th style="padding: 10px; border: 1px solid #ddd;">Image</th>
      <th style="padding: 10px; border: 1px solid #ddd;">Title</th>
      <th style="padding: 10px; border: 1px solid #ddd;">Description</th>
      <th style="padding: 10px; border: 1px solid #ddd;">Price</th>
      <th style="padding: 10px; border: 1px solid #ddd;">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php if ($result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td style="padding: 10px; border: 1px solid #ddd;">
            <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" style="width: 80px;">
          </td>
          <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($row['package_name']) ?></td>
          <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($row['description']) ?></td>
          <td style="padding: 10px; border: 1px solid #ddd;">$<?= htmlspecialchars($row['unit_price']) ?></td>
          <td style="padding: 10px; border: 1px solid #ddd;">
            <div class="action-buttons">
              <a href="edit_package_page.php?id=<?= $row['package_id'] ?>" class="btn-primary">Edit</a>
              <button class="btn-danger" onclick="deletePackage(<?= $row['package_id'] ?>)">Delete</button>
            </div>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr>
        <td colspan="5" style="text-align:center; padding: 20px;">No packages found.</td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>

<?Php
include 'connection.php';
$search = $_GET['search'] ?? '';
$searchTerm = '%' . $conn->real_escape_string($search) . '%';

if (!empty($search)) {
  $stmt = $conn->prepare("SELECT * FROM package WHERE user_id = ? AND package_name LIKE ?");
  $stmt->bind_param("is", $userId, $searchTerm);
} else {
  $stmt = $conn->prepare("SELECT * FROM package WHERE user_id = ?");
  $stmt->bind_param("i", $userId);
}
$stmt->execute();
$result = $stmt->get_result();

  
?>

        <div class="package-modal" id="packageModal">
          <div class="modal-content">
            <button class="btn-primary" onclick="togglePackageModal()" style="position: absolute; right: 1rem; top: 1rem">
              <i class="fas fa-times"></i>
            </button>
            <h2 id="modalTitle">Create New Package</h2>
            <form id="packageForm" method="POST" action="save_package.php" enctype="multipart/form-data">

              <input type="hidden" id="packageId" name="id" />
              <div class="form-grid">
                <div class="form-group">
                  <label>Package Title</label>
                  <input type="text" name="title" required />
                </div>
                <div class="form-group">
                  <label>Status</label>
                  <select name="status" required>
                    <option value="active">Active</option>
                    <option value="draft">Draft</option>
                  </select>
                </div>
                <div class="form-group">
  <label>Package Type</label>
  <select name="type" id="typeSelect" required>
    <option value="">Select a package type</option>
    <?php
      require 'connection.php'; // Make sure $conn is defined here

      $query = "SELECT id, name FROM package_type ORDER BY name ASC";
      $result = $conn->query($query);

      if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
          }
      } else {
          echo '<option disabled>No package types available</option>';
      }
    ?>
  </select>
</div>

<div style="border: 1px solid #ccc; border-radius: 8px; padding: 16px; width: 300px; font-family: Arial, sans-serif;">
  <label for="imageUpload" style="font-weight: bold; display: block; margin-bottom: 8px;">Upload Image</label>
  <input type="file" name="image" id="imageUpload" accept="image/*" required style="width: 100%; padding: 6px; border-radius: 4px; border: 1px solid #aaa;">
</div>

                <div class="form-group">
  <label>Location</label>
  <select name="location" required>
    <option value="">Select a location</option>
    <?php
      require 'connection.php'; // make sure $conn is defined inside this file

      $sql = "SELECT id, name FROM city ORDER BY name ASC";
      $result = $conn->query($sql);

      if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['name']) . '</option>';
          }
      } else {
          echo '<option disabled>No locations found</option>';
      }
    ?>
  </select>
</div>

                
<div class="form-group">
  <label>State</label>
  <select name="state" id="stateSelect" required>
    <option value="">Select a state</option>
    <?php
      require 'connection.php';
      $sql = "SELECT id, name FROM state ORDER BY name ASC";
      $result = $conn->query($sql);

      if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
          }
      } else {
          echo '<option disabled>No states found</option>';
      }
    ?>
  </select>
</div>

                <div class="form-group">
                  <label>Start Date</label>
                  <input type="date" name="startDate" required />
                </div>
                <div class="form-group">
                  <label>End Date</label>
                  <input type="date" name="endDate" required />
                </div>
                <div class="form-group">
                  <label>Price per Person</label>
                  <input type="number" name="price" required />
                </div>
                <div class="form-group">
                  <label>Max Participants</label>
                  <input type="number" name="maxParticipants" required />
                </div>
                <div class="form-group">
                  <label>Package Description</label>
                  <textarea name="description" rows="4"></textarea>
                </div>
              </div>
              <div class="form-group">
                <label>Daily Itinerary</label>
                <div id="itineraryContainer">
                  <div class="itinerary-day">
                    <input type="text" name="dayTitle[]" placeholder="Day 1 Title" required />
                    <textarea name="dayDescription[]" rows="2" placeholder="Day 1 Description" required></textarea>
                    <input type="date" name="start[]" placeholder="start time" required />
                    <input type="date" name="end[]" placeholder="end time" required />
                  </div>
                </div>
                <button type="button" class="btn-primary add-day-btn" onclick="addItineraryDay()">
                  <i class="fas fa-plus"></i> Add Day
                </button>
              </div>
              <div style="display: flex; gap: 1rem; margin-top: 2rem">
                <button type="submit" class="btn-primary">
                  <i class="fas fa-save"></i> Save Package
                </button>
                <button type="button" class="btn-primary" style="background: var(--text-light)" onclick="togglePackageModal()">
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      </main>
    </div>
    <script>
     

      function addItineraryDay() {
        const container = document.getElementById("itineraryContainer");
        const dayNumber = container.children.length + 1;
        const dayDiv = document.createElement("div");
        dayDiv.className = "itinerary-day";
        dayDiv.innerHTML = `
          <input type="text" name="dayTitle[]" placeholder="Day ${dayNumber} Title" required>
          <textarea name="dayDescription[]" rows="2" placeholder="Day ${dayNumber} Description" required></textarea>
          <input type="text" name="start[]" placeholder="start time" required>
          <input type="text" name="end[]" placeholder="end time" required>
        `;
        container.appendChild(dayDiv);
      }
      function togglePackageModal() {
  const modal = document.getElementById("packageModal");

  if (modal.style.display === "block") {
    modal.style.display = "none";
    resetForm();
  } else {
    modal.style.display = "block";
    document.querySelector(".modal-content").scrollTop = 0;
  }
}

    </script>
  </body>
</html>
<script>
function editPackage(id) {
  alert("Edit functionality for package ID: " + id);
  // Later you can fetch data via AJAX and fill the modal
}

function deletePackage(id) {
  if (confirm("Are you sure you want to delete this package?")) {
    window.location.href = "delete_package.php?id=" + id;
  }
}
</script>
<style>
  .action-buttons {
  display: flex;
  gap: 10px;
}

.action-buttons .btn-primary,
.action-buttons .btn-danger {
  padding: 8px 14px;
  font-size: 14px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  text-decoration: none;
  transition: background-color 0.3s ease;
}

.action-buttons .btn-primary {
  background-color: #119DA4;
  color: white;
}

.action-buttons .btn-primary:hover {
  background-color: #0c7e83;
}

.action-buttons .btn-danger {
  background-color: #e74c3c;
  color: white;
}

.action-buttons .btn-danger:hover {
  background-color: #c0392b;
}

</style>