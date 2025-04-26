<?php
session_start();
require 'connection.php';

// Check if operator is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch number of packages uploaded by this operator
$packageQuery = $conn->prepare("SELECT COUNT(*) as total_packages FROM package WHERE user_id = ?");
$packageQuery->bind_param("i", $userId);
$packageQuery->execute();
$packageResult = $packageQuery->get_result()->fetch_assoc();
$totalPackages = $packageResult['total_packages'] ?? 0;

// Fetch number of bookings related to this operator's packages
$bookingQuery = $conn->prepare("
    SELECT COUNT(*) as total_bookings
    FROM booking b
    INNER JOIN package p ON b.package_id = p.package_id
    WHERE p.user_id = ?
");
$bookingQuery->bind_param("i", $userId);
$bookingQuery->execute();
$bookingResult = $bookingQuery->get_result()->fetch_assoc();
$totalBookings = $bookingResult['total_bookings'] ?? 0;

// Fetch Bookings grouped by year
$yearsStmt = $conn->prepare("
    SELECT DISTINCT YEAR(travel_date) as travel_year
    FROM booking b
    INNER JOIN package p ON b.package_id = p.package_id
    WHERE p.user_id = ?
    ORDER BY travel_year DESC
");
$yearsStmt->bind_param("i", $userId);
$yearsStmt->execute();
$years = $yearsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch bookings grouped by month for current year (default)
$currentYear = date('Y');
$chartStmt = $conn->prepare("
    SELECT MONTH(travel_date) as month, COUNT(*) as count
    FROM booking b
    INNER JOIN package p ON b.package_id = p.package_id
    WHERE p.user_id = ? AND YEAR(travel_date) = ?
    GROUP BY MONTH(travel_date)
");
$chartStmt->bind_param("ii", $userId, $currentYear);
$chartStmt->execute();
$chartData = $chartStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Prepare data for Chart.js
$months = array_fill(1, 12, 0); // Initialize months
foreach ($chartData as $data) {
    $months[(int)$data['month']] = $data['count'];
}
include 'navbar.php'; // Include header if needed
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Operator Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="css/operatorDash.css" />
    <link rel="stylesheet" href="css/index.css" />
</head>
<body>
<br><br><br>
<div class="dashboard-container">
  <div class="dashboard-header">
   
  </div>

  <div class="cards-container">
    <div class="card">
      <i class="fas fa-box-open"></i>
      <h2><?= $totalPackages ?></h2>
      <p>Total Packages</p>
    </div>

    <div class="card">
      <i class="fas fa-ticket-alt"></i>
      <h2><?= $totalBookings ?></h2>
      <p>Total Bookings</p>
    </div>
  </div>

  <div class="filter-container">
    <label for="yearFilter">Filter by Travel Year:</label>
    <select id="yearFilter" onchange="filterYear()">
      <?php foreach ($years as $year): ?>
        <option value="<?= $year['travel_year'] ?>" <?= ($year['travel_year'] == $currentYear) ? 'selected' : '' ?>>
          <?= $year['travel_year'] ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="chart-container">
  &nbsp;  <canvas id="bookingChart"></canvas>
  </div>
</div>

<script>
const ctx = document.getElementById('bookingChart').getContext('2d');
let bookingChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
        ],
        datasets: [{
            label: 'Bookings Per Month',
            data: <?= json_encode(array_values($months)) ?>,
            backgroundColor: '#119DA4',
            borderRadius: 10
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                  stepSize: 1
                }
            }
        }
    }
});

function filterYear() {
    const year = document.getElementById('yearFilter').value;
    window.location.href = "index.php?year=" + year;
}
</script>

</body>
</html>