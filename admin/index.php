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
$packageQuery = $conn->prepare("SELECT COUNT(*) as total_packages FROM package");

$packageQuery->execute();
$packageResult = $packageQuery->get_result()->fetch_assoc();
$totalPackages = $packageResult['total_packages'] ?? 0;

// Fetch total bookings (no condition by operator)
$bookingQuery = $conn->query("SELECT COUNT(*) as total_bookings FROM booking");
$bookingResult = $bookingQuery->fetch_assoc();
$totalBookings = $bookingResult['total_bookings'] ?? 0;

// Fetch number of users (normal users only)
$userQuery = $conn->query("SELECT COUNT(*) as total_users FROM users WHERE role = 'user'");
$userResult = $userQuery->fetch_assoc();
$totalUsers = $userResult['total_users'] ?? 0;

// Fetch Bookings grouped by year (ALL bookings, not by operator)
$yearsStmt = $conn->query("
    SELECT DISTINCT YEAR(travel_date) as travel_year
    FROM booking
    ORDER BY travel_year DESC
");
$years = $yearsStmt->fetch_all(MYSQLI_ASSOC);

// Determine selected year
$currentYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Fetch bookings grouped by month (ALL bookings, not by operator)
$chartStmt = $conn->prepare("
    SELECT MONTH(travel_date) as month, COUNT(*) as count
    FROM booking
    WHERE YEAR(travel_date) = ?
    GROUP BY MONTH(travel_date)
");
$chartStmt->bind_param("i", $currentYear);
$chartStmt->execute();
$bookingChartData = $chartStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch users registered per month
$userChartStmt = $conn->prepare("
    SELECT MONTH(verified_at) as month, COUNT(*) as count
    FROM users
    WHERE role = 'user' AND YEAR(verified_at) = ?
    GROUP BY MONTH(verified_at)
");
$userChartStmt->bind_param("i", $currentYear);
$userChartStmt->execute();
$userChartData = $userChartStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Prepare months data
$bookingMonths = array_fill(1, 12, 0);
foreach ($bookingChartData as $data) {
    $bookingMonths[(int)$data['month']] = $data['count'];
}

$userMonths = array_fill(1, 12, 0);
foreach ($userChartData as $data) {
    $userMonths[(int)$data['month']] = $data['count'];
}

include 'navbar.php';
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
  <div class="dashboard-header"></div>

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

    <div class="card">
      <i class="fas fa-users"></i>
      <h2><?= $totalUsers ?></h2>
      <p>Total Users</p>
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
    <canvas id="bookingChart"></canvas>
  </div>
</div>

<script>
const ctx = document.getElementById('bookingChart').getContext('2d');

let bookingChart = new Chart(ctx, {
    type: 'line', // Global chart type is line
    data: {
        labels: [
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
        ],
        datasets: [
          {
            label: 'Bookings Per Month',
            data: <?= json_encode(array_values($bookingMonths)) ?>,
            borderColor: '#119DA4',
            backgroundColor: 'rgba(17, 157, 164, 0.2)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
          },
          {
            label: 'Users Registered',
            data: <?= json_encode(array_values($userMonths)) ?>,
            borderColor: '#e67e22',
            backgroundColor: 'rgba(230,126,34,0.3)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
          }
        ]
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
