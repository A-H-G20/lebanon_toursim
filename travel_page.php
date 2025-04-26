<?php
session_start();
require 'config.php';

// Fetch all packages
$packagesStmt = $conn->query("
    SELECT p.*, 
           c.name AS city_name, 
           s.name AS state_name, 
           pt.name AS package_type_name
    FROM package p
    LEFT JOIN city c ON p.city_id = c.id
    LEFT JOIN state s ON p.state_id = s.id
    LEFT JOIN package_type pt ON p.package_type = pt.id
");
$packages = $packagesStmt->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Lebanon Travel Experiences | Discover Ancient Wonders</title>
    <link rel="stylesheet" href="css/travelList.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
  </head>
  <body>
    <div class="filter-overlay">
      <aside class="filter-sidebar">
        <button class="close-filters">&times;</button>
        <div class="filter-group">
          <h3 class="filter-title">
            <i class="fas fa-map-marker-alt filter-icon"></i>
            Locations
          </h3>
          <div class="location-filter">
            <div class="location-card">
            
              <span class="location-name">Beirut</span>
            </div>
            <div class="location-card">
            
              <span class="location-name">Byblos</span>
            </div>
          </div>
        </div>

        <div class="filter-group">
          <h3 class="filter-title">
            <i class="fas fa-tag filter-icon"></i>
            Tour Type
          </h3>
          <div class="type-filter">
            <div class="type-pill">
              <i class="fas fa-university"></i>
              Cultural
            </div>
            <div class="type-pill">
              <i class="fas fa-hiking"></i>
              Adventure
            </div>
          </div>
        </div>

        <div class="filter-group">
          <h3 class="filter-title">
            <i class="fas fa-star filter-icon"></i>
            Rating
          </h3>
          <div class="rating-filter">
            <div class="rating-item">
              <div class="stars">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
              </div>
              <span class="rating-text">4.5 & up</span>
            </div>
          </div>
        </div>

        <div class="filter-group">
          <h3 class="filter-title">
            <i class="fas fa-wallet filter-icon"></i>
            Price Range
          </h3>
          <div class="price-filter">
            <input
              type="range"
              class="price-slider"
              min="0"
              max="1000"
              step="50"
            />
            <div class="price-labels">
              <span>$100</span>
              <span>$1000</span>
            </div>
          </div>
        </div>

        <div class="filter-group">
          <h3 class="filter-title">
            <i class="fas fa-clock filter-icon"></i>
            Duration
          </h3>
          <div class="duration-filter">
            <div class="duration-btn">1-3 Days</div>
            <div class="duration-btn">4-7 Days</div>
            <div class="duration-btn">8-14 Days</div>
            <div class="duration-btn">15+ Days</div>
          </div>
        </div>

        <button class="card-cta" style="width: 100%; margin-top: 1rem">
          Apply Filters
        </button>
      </aside>
    </div>
    <button class="mobile-filter-btn">
      <i class="fas fa-sliders-h"></i>
    </button>
    <header class="discover-header">
      <h1>Lebanon's Hidden Treasures</h1>
      <p>
        Explore 200+ curated experiences across ancient cities, mountain
        retreats, and Mediterranean coasts
      </p>
      <div class="search-header">
  <form method="GET" class="search-container">
    <input
      type="text"
      name="search"
      class="search-input"
      placeholder="Search experiences..."
      value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
    />
    <button type="submit" class="search-button">
      <i class="fas fa-search"></i>
      Search
    </button>
  </form>
</div>

    </header>

    <main class="listing-container">
    <?php
require 'config.php';

// Fetch cities and types FIRST
$citiesStmt = $conn->query("SELECT id, name FROM city");
$cities = $citiesStmt ? $citiesStmt->fetch_all(MYSQLI_ASSOC) : [];

$typesStmt = $conn->query("SELECT id, name FROM package_type");
$types = $typesStmt ? $typesStmt->fetch_all(MYSQLI_ASSOC) : [];

// Handle filter values
$whereClauses = [];
$params = [];
$bindTypes = "";

// Location filter
if (!empty($_GET['city'])) {
    $whereClauses[] = "p.city_id = ?";
    $params[] = $_GET['city'];
}

// Tour Type filter
if (!empty($_GET['type'])) {
    $whereClauses[] = "p.package_type = ?";
    $params[] = $_GET['type'];
}

// Price filter
if (!empty($_GET['max_price'])) {
    $whereClauses[] = "p.unit_price <= ?";
    $params[] = $_GET['max_price'];
}

// Duration filter
if (!empty($_GET['duration'])) {
    switch ($_GET['duration']) {
        case '1-3':
            $whereClauses[] = "p.average_duration BETWEEN 24 AND 72";
            break;
        case '4-7':
            $whereClauses[] = "p.average_duration BETWEEN 96 AND 168";
            break;
        case '8-14':
            $whereClauses[] = "p.average_duration BETWEEN 192 AND 336";
            break;
        case '15+':
            $whereClauses[] = "p.average_duration > 360";
            break;
    }
}
if (!empty($_GET['search'])) {
  $whereClauses[] = "p.package_name LIKE ?";
  $params[] = '%' . $_GET['search'] . '%';
}
// Build SQL Query
$sql = "
    SELECT p.*, c.name AS city_name, s.name AS state_name, pt.name AS package_type_name
    FROM package p
    LEFT JOIN city c ON p.city_id = c.id
    LEFT JOIN state s ON p.state_id = s.id
    LEFT JOIN package_type pt ON p.package_type = pt.id
";

if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(' AND ', $whereClauses);
}

$sql .= " ORDER BY p.start_date DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $bindTypes = str_repeat("i", count($params));
    $stmt->bind_param($bindTypes, ...$params);
}

$stmt->execute();
$packages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<aside class="filter-sidebar">
<form method="GET">

  <div class="filter-group">
    <h3 class="filter-title">
      <i class="fas fa-map-marker-alt filter-icon"></i>
      Locations
    </h3>
    <div class="location-filter">
      <?php foreach ($cities as $city): ?>
        <div class="location-card">
          <label>
            <input type="radio" name="city" value="<?= $city['id'] ?>" <?= (isset($_GET['city']) && $_GET['city'] == $city['id']) ? 'checked' : '' ?>>
            <span class="location-name"><?= htmlspecialchars($city['name']) ?></span>
          </label>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="filter-group">
    <h3 class="filter-title">
      <i class="fas fa-tag filter-icon"></i>
      Tour Type
    </h3>
    <div class="type-filter">
      <?php foreach ($types as $type): ?>
        <div class="type-pill">
          <label>
            <input type="radio" name="type" value="<?= $type['id'] ?>" <?= (isset($_GET['type']) && $_GET['type'] == $type['id']) ? 'checked' : '' ?>>
            <i class="fas <?= $type['name'] === 'Cultural' ? 'fa-university' : 'fa-hiking' ?>"></i>
            <?= htmlspecialchars($type['name']) ?>
          </label>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="filter-group">
    <h3 class="filter-title">
      <i class="fas fa-wallet filter-icon"></i>
      Price Range
    </h3>
    <div class="price-filter">
      <input type="range" class="price-slider" min="0" max="1000" step="50" name="max_price" id="priceRange" value="<?= isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : 1000 ?>">
      <div class="price-labels">
        <span>$0</span>
        <span>$1000</span>
      </div>
    </div>
  </div>

  <div class="filter-group">
    <h3 class="filter-title">
      <i class="fas fa-clock filter-icon"></i>
      Duration
    </h3>
    <div class="duration-filter">
      <label><input type="radio" name="duration" value="1-3" <?= (isset($_GET['duration']) && $_GET['duration'] == '1-3') ? 'checked' : '' ?>> 1-3 Days</label>
      <label><input type="radio" name="duration" value="4-7" <?= (isset($_GET['duration']) && $_GET['duration'] == '4-7') ? 'checked' : '' ?>> 4-7 Days</label>
      <label><input type="radio" name="duration" value="8-14" <?= (isset($_GET['duration']) && $_GET['duration'] == '8-14') ? 'checked' : '' ?>> 8-14 Days</label>
      <label><input type="radio" name="duration" value="15+" <?= (isset($_GET['duration']) && $_GET['duration'] == '15+') ? 'checked' : '' ?>> 15+ Days</label>
    </div>
  </div>

  <button type="submit" class="card-cta" style="width: 100%; margin-top: 1rem;">
    Apply Filters
  </button>

</form>
</aside>


      <section class="experience-grid">
    <?php foreach ($packages as $package): ?>
    <article class="experience-card">
        <div
            class="card-image"
            style="background-image: url('uploads/<?= htmlspecialchars($package['image']) ?>');"
        >
            <span class="card-badge">
              <i class="fas <?= ($package['package_type_name'] == 'Cultural' ? 'fa-university' : 'fa-hiking') ?>"></i>
              <?= htmlspecialchars($package['package_type_name']) ?>
            </span>
        </div>
        <div class="card-content">
            <h3 class="card-title"><?= htmlspecialchars($package['package_name']) ?></h3>
            <div class="card-location">
              <i class="fas fa-map-marker-alt"></i>
              <?= htmlspecialchars($package['city_name']) ?>, <?= htmlspecialchars($package['state_name']) ?>
            </div>
            <div class="card-details">
              <div class="detail-item">
                <i class="fas fa-clock detail-icon"></i>
                <?= htmlspecialchars($package['average_duration']) ?> Hours
              </div>
              <div class="detail-item">
                <i class="fas fa-calendar detail-icon"></i>
                <?= date('d M', strtotime($package['start_date'])) ?> → <?= date('d M', strtotime($package['end_date'])) ?>
              </div>
              <div class="detail-item">
                <i class="fas fa-users detail-icon"></i>
                <?= htmlspecialchars($package['available_spots']) ?> Spots
              </div>
              <div class="detail-item">
                <i class="fas fa-suitcase-rolling detail-icon"></i>
                <?= htmlspecialchars($package['total_spots']) ?> Total
              </div>
            </div>
            <div class="card-price">
              $<?= number_format($package['unit_price'], 2) ?><span>/person</span>
            </div>
            <a href="details.php?id=<?= $package['package_id'] ?>" class="card-cta" style="text-decoration: none;" >
              <i class="fas fa-suitcase"></i>
             View Details
            </a>
        </div>
    </article>
    <?php endforeach; ?>
</section>
    </main>

    <script>
      const filterSidebar = document.querySelector(".filter-sidebar");
      const mobileFilterBtn = document.querySelector(".mobile-filter-btn");
      const filterOverlay = document.querySelector(".filter-overlay");
      const closeFilters = document.querySelector(".close-filters");

      function toggleFilters() {
        filterOverlay.classList.toggle("active");
        filterSidebar.classList.toggle("active");
      }

      mobileFilterBtn.addEventListener("click", toggleFilters);

      closeFilters.addEventListener("click", function (e) {
        e.stopPropagation(); // Prevent event from bubbling to overlay
        toggleFilters();
      });

      filterOverlay.addEventListener("click", function (e) {
        if (e.target === filterOverlay) {
          toggleFilters();
        }
      });

      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && filterOverlay.classList.contains("active")) {
          toggleFilters();
        }
      });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mark.js/8.11.1/mark.min.js"></script>
  </body>
</html>
