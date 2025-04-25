<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['user_id'])) {
  die("Unauthorized access");
}

$userId = $_SESSION['user_id'];
$packageId = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM package WHERE package_id = ? AND user_id = ?");
$stmt->bind_param("ii", $packageId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  die("Package not found.");
}

$package = $result->fetch_assoc();

// Fetch itinerary
$activities = [];
$actStmt = $conn->prepare("SELECT * FROM activity WHERE package_id = ?");
$actStmt->bind_param("i", $packageId);
$actStmt->execute();
$actResult = $actStmt->get_result();

while ($row = $actResult->fetch_assoc()) {
  $activities[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Package</title>
  <link rel="stylesheet" href="css/edit_package.css" />
</head>
<body>
  <div class="dashboard-container">
    <main class="main-content">
      <h2>Edit Package</h2>
      <form method="POST" action="save_package.php" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $package['package_id'] ?>" />

        <div class="form-group">
          <label>Package Title</label>
          <input type="text" name="title" value="<?= htmlspecialchars($package['package_name']) ?>" required />
        </div>

        <div class="form-group">
          <label>Status</label>
          <select name="status" required>
            <option value="active" <?= $package['status'] === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="draft" <?= $package['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
          </select>
        </div>

        <div class="form-group">
          <label>Package Type</label>
          <select name="type" required>
            <option value="">Select a package type</option>
            <?php
              $res = $conn->query("SELECT id, name FROM package_type");
              while ($type = $res->fetch_assoc()) {
                $selected = ($package['package_type'] == $type['id']) ? 'selected' : '';
                echo "<option value='{$type['id']}' $selected>" . htmlspecialchars($type['name']) . "</option>";
              }
            ?>
          </select>
        </div>

        <div class="form-group">
          <label>Upload Image</label>
          <input type="file" name="image" />
          <p>Current: <img src="../uploads/<?= htmlspecialchars($package['image']) ?>" width="80" /></p>
        </div>

        <div class="form-group">
          <label>Location (City)</label>
          <select name="location" required>
            <option value="">Select a city</option>
            <?php
              $res = $conn->query("SELECT id, name FROM city");
              while ($city = $res->fetch_assoc()) {
                $selected = ($package['city_id'] == $city['id']) ? 'selected' : '';
                echo "<option value='{$city['id']}' $selected>" . htmlspecialchars($city['name']) . "</option>";
              }
            ?>
          </select>
        </div>

        <div class="form-group">
          <label>State</label>
          <select name="state" required>
            <option value="">Select a state</option>
            <?php
              $res = $conn->query("SELECT id, name FROM state");
              while ($state = $res->fetch_assoc()) {
                $selected = ($package['state_id'] == $state['id']) ? 'selected' : '';
                echo "<option value='{$state['id']}' $selected>" . htmlspecialchars($state['name']) . "</option>";
              }
            ?>
          </select>
        </div>

        <div class="form-group">
          <label>Start Date</label>
          <input type="date" name="startDate" value="<?= $package['start_date'] ?>" required />
        </div>

        <div class="form-group">
          <label>End Date</label>
          <input type="date" name="endDate" value="<?= $package['end_date'] ?>" required />
        </div>

        <div class="form-group">
          <label>Price per Person</label>
          <input type="number" name="price" value="<?= $package['unit_price'] ?>" required />
        </div>

        <div class="form-group">
          <label>Max Participants</label>
          <input type="number" name="maxParticipants" value="<?= $package['total_spots'] ?>" required />
        </div>

        <div class="form-group">
          <label>Package Description</label>
          <textarea name="description" rows="4"><?= htmlspecialchars($package['description']) ?></textarea>
        </div>

        <div class="form-group">
          <label>Daily Itinerary</label>
          <div id="itineraryContainer">
            <?php foreach ($activities as $i => $act): ?>
              <div class="itinerary-day">
                <input type="text" name="dayTitle[]" placeholder="Title" value="<?= htmlspecialchars($act['name']) ?>" required />
                <textarea name="dayDescription[]" rows="2" placeholder="Description" required><?= htmlspecialchars($act['description']) ?></textarea>
                <input type="text" name="start[]" placeholder="Start time" value="<?= htmlspecialchars($act['from_time']) ?>" required />
                <input type="text" name="end[]" placeholder="End time" value="<?= htmlspecialchars($act['to_time']) ?>" required />
              </div>
            <?php endforeach; ?>
          </div>
          <button type="button" onclick="addItineraryDay()">Add Day</button>
        </div>

        <div style="margin-top: 20px;">
          <button type="submit">Update Package</button>
          <a href="package.php" style="margin-left: 20px;">Cancel</a>
        </div>
      </form>
    </main>
  </div>

  <script>
    function addItineraryDay() {
      const container = document.getElementById("itineraryContainer");
      const dayNumber = container.children.length + 1;
      const div = document.createElement("div");
      div.className = "itinerary-day";
      div.innerHTML = `
        <input type="text" name="dayTitle[]" placeholder="Title" required />
        <textarea name="dayDescription[]" rows="2" placeholder="Description" required></textarea>
        <input type="text" name="start[]" placeholder="Start time" required />
        <input type="text" name="end[]" placeholder="End time" required />
      `;
      container.appendChild(div);
    }
  </script>
</body>
</html>
