
<nav class="sidebar" id="sidebar">
  <div class="nav__logo">
    <h2>Tourism</h2>
  </div>
  <div class="nav-item" onclick="window.location.href='index.php'">
  <i class="fas fa-tachometer-alt"></i>
  <span>Dashboard</span>
</div>

  <div class="nav-item" onclick="window.location.href='package.php'">
    <i class="fas fa-box"></i><span>Packages</span>
  </div>
  <div class="nav-item" onclick="window.location.href='booking.php'">
  <i class="fas fa-ticket-alt"></i><span>Booking</span>
</div>

<div class="nav-group" onclick="toggleSubmenu()">
  <i class="fas fa-cogs"></i> <span>Management</span>
</div>

<div id="management-submenu" class="nav-submenu">
  <div class="nav-item" onclick="window.location.href='admin.php'">
    <i class="fas fa-user-shield"></i><span>Admin Management</span>
  </div>
  <div class="nav-item" onclick="window.location.href='user.php'">
    <i class="fas fa-user"></i><span>User Management</span>
  </div>
  <div class="nav-item" onclick="window.location.href='operator.php'">
    <i class="fas fa-user-tie"></i><span>Operator Management</span>
  </div>
  <div class="nav-item" onclick="window.location.href='discount.php'">
    <i class="fas fa-tags"></i><span>Discount Management</span>
  </div>
  <div class="nav-item" onclick="window.location.href='city.php'">
    <i class="fas fa-city"></i><span>City Management</span>
  </div>
  <div class="nav-item" onclick="window.location.href='state.php'">
    <i class="fas fa-map-marked-alt"></i><span>State Management</span>
  </div>
  <div class="nav-item" onclick="window.location.href='package_type.php'">
    <i class="fas fa-box-open"></i><span>Package Type Management</span>
  </div>
</div>

<script>
  function toggleSubmenu() {
    const submenu = document.getElementById('management-submenu');
    submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
  }
</script>




  <div class="nav-item" onclick="window.location.href='../logout.php'">
    <i class="fas fa-door-open"></i><span>Logout</span>
  </div>
</nav>
<style>
  :root {
    --primary: #2a6559;
    --accent: #e67e22;
    --light: #f9f5f0;
    --text: #2c3e50;
    --border: #e5e7eb;
    --shadow: 0 4px 24px rgba(0, 0, 0, 0.08);

    --accent-20: rgba(230, 126, 34, 0.2);
    --text-light: #64748b;
  }

  body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: var(--light);
    color: var(--text);
  }
h2{
  color: black;
}
  .sidebar {
    width: 250px;
    height: 100vh;

    box-shadow: var(--shadow);
    color: var(--light);
    padding: 20px 0;
    position: fixed;
    overflow-y: auto;
  }

  .nav__logo {
    text-align: center;
    padding: 15px 0;
    border-bottom: 1px solid var(--accent-20);
    margin-bottom: 20px;
  }

  .nav__logo h2 {
    font-size: 24px;
    color: black;
    margin: 0;
  }

  .nav-item,
  .nav-group {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    color: black;
  }

  .nav-item i,
  .nav-group i {
    margin-right: 12px;
    font-size: 16px;
    width: 20px;
  }

  .nav-item span,
  .nav-group span {
    font-size: 16px;
  }

  .nav-item:hover {
    background-color: var(--accent-20);
  }

  .nav-group {
    font-weight: bold;
    border-top: 1px solid var(--accent-20);
    border-bottom: 1px solid var(--accent-20);
    margin-top: 15px;
  }

  .nav-submenu {
    display: none;
    flex-direction: column;
    padding-left: 20px;
    margin-top: 5px;
  }

  .nav-submenu .nav-item {
    background-color: rgba(255, 255, 255, 0.05);
    margin: 3px 0;
    padding: 10px 20px;
    border-radius: 4px;
  }


  .nav-submenu .nav-item span {
    font-size: 15px;
  }
</style>
