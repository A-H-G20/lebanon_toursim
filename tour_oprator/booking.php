<?php
session_start();
require 'connection.php';

// Check if operator is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch bookings for operator's packages
$stmt = $conn->prepare("
    SELECT 
        b.*, 
        p.package_name, 
        p.image, 
        u.name AS traveler_name, 
        u.email AS traveler_email, 
        u.phone_number
    FROM booking b
    LEFT JOIN package p ON b.package_id = p.package_id
    LEFT JOIN users u ON b.user_id = u.id
    WHERE p.user_id = ?
    ORDER BY b.booking_date DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$bookings = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Booking Management</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="css/bookingDash.css">
</head>
<body>

<main class="main-content">
<div class="menu-overlay" onclick="closeMobileMenu()"></div>
<button class="menu-toggle" id="menuToggle">
  <span></span>
  <span></span>
  <span></span>
</button>
<?php include 'navbar.php'; ?>

<div class="header">
  <div class="header-left">
    <h1>Booking Management</h1>
    <div class="search-bar">
      <input type="text" id="bookingSearch" placeholder="Search bookings...">
      <i class="fas fa-search"></i>
    </div>
  </div>
 
</div>

<div class="booking-grid" id="bookingContainer">
  <?php if ($bookings->num_rows > 0): ?>
    <?php while ($b = $bookings->fetch_assoc()): ?>
      <div class="booking-card">
        <div class="booking-status"><?= htmlspecialchars($b['payment_status']) ?></div>

        <div class="booking-user">
          <div class="user-avatar">
            <?= strtoupper(substr($b['traveler_name'], 0, 1)) ?><?= strtoupper(substr($b['traveler_name'], strpos($b['traveler_name'], ' ') + 1, 1)) ?>
          </div>
          <div>
            <h3><?= htmlspecialchars($b['traveler_name']) ?></h3>
            <span class="text-light"><?= htmlspecialchars($b['traveler_email']) ?></span>
          </div>
        </div>

        <div class="booking-meta">
          <span><i class="fas fa-box"></i> <?= htmlspecialchars($b['package_name']) ?></span>
          <span class="text-primary">$<?= number_format($b['total_price'], 2) ?></span>
        </div>

        <div class="booking-actions">
          <button class="btn-primary" onclick="viewBookingDetails('<?= htmlspecialchars(json_encode($b)) ?>')">
            <i class="fas fa-eye"></i> Details
          </button>
          <button class="report-btn" onclick="downloadReport(<?= $b['booking_id'] ?>)">
  <i class="fas fa-download"></i> Report
</button>

        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p style="text-align:center; margin-top: 2rem;">No bookings found.</p>
  <?php endif; ?>
</div>

<div class="booking-modal" id="bookingModal">
  <div class="booking-modal-content">
    <button class="btn-primary" onclick="closeBookingModal()" style="position: absolute; right: 1rem; top: 1rem;">
      <i class="fas fa-times"></i>
    </button>
    <h2>Booking Details</h2>

    <div class="detail-grid" id="bookingDetailsContainer">
      <!-- Details dynamically filled here -->
    </div>

    <div class="email-compose">
      <h3>Contact Traveler</h3>
      <button class="btn-primary" id="composeEmailBtn" style="margin-top: 1rem;">
        <i class="fas fa-envelope"></i> Compose Email
      </button>
    </div>
  </div>
</div>
</main>

<script>
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');

menuToggle.addEventListener('click', (e) => {
  e.stopPropagation();
  sidebar.classList.toggle('active');
  menuToggle.classList.toggle('active');
});

document.addEventListener('click', (e) => {
  if (window.innerWidth <= 768) {
    if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
      sidebar.classList.remove('active');
      menuToggle.classList.remove('active');
    }
  }
});

window.addEventListener('resize', () => {
  if (window.innerWidth > 768) {
    sidebar.classList.remove('active');
    menuToggle.classList.remove('active');
  }
});

function viewBookingDetails(data) {
  const booking = JSON.parse(data);
  const container = document.getElementById('bookingDetailsContainer');
  container.innerHTML = `
    <div class="detail-item"><strong>Traveler Name</strong><span>${booking.traveler_name}</span></div>
    <div class="detail-item"><strong>Package Name</strong><span>${booking.package_name}</span></div>
    <div class="detail-item"><strong>Booking Date</strong><span>${booking.booking_date}</span></div>
    <div class="detail-item"><strong>Total Price</strong><span>$${parseFloat(booking.total_price).toFixed(2)}</span></div>
    <div class="detail-item"><strong>Contact Email</strong><span>${booking.traveler_email}</span></div>
    <div class="detail-item"><strong>Phone Number</strong><span>${booking.phone_number}</span></div>
  `;

  document.getElementById('composeEmailBtn').onclick = function() {
    const email = booking.traveler_email;
    const subject = encodeURIComponent('Regarding Your Tour Booking');
    const body = encodeURIComponent(`Dear ${booking.traveler_name},\n\n`);
    window.location.href = `mailto:${email}?subject=${subject}&body=${body}`;
  };

  document.getElementById('bookingModal').style.display = 'block';
}

function closeBookingModal() {
  document.getElementById('bookingModal').style.display = 'none';
}

document.getElementById('bookingSearch').addEventListener('input', function(e) {
  const searchTerm = e.target.value.toLowerCase();
  const bookings = document.querySelectorAll('.booking-card');
  bookings.forEach(card => {
    const text = card.innerText.toLowerCase();
    if (text.includes(searchTerm)) {
      card.style.display = '';
    } else {
      card.style.display = 'none';
    }
  });
});

window.onclick = function(e) {
  const modal = document.getElementById('bookingModal');
  if (e.target === modal) closeBookingModal();
}

function downloadReport(bookingId) {
    window.location.href = 'download_report.php?booking_id=' + bookingId;
}

</script>

</body>
</html>