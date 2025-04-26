<?php
session_start();
require 'connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// Approve / Deactivate Operator
if (isset($_GET['toggle_id'])) {
    $id = intval($_GET['toggle_id']);

    // Fetch current status
    $stmt = $conn->prepare("SELECT tour_operator.status, users.email, users.name FROM tour_operator 
                            INNER JOIN users ON users.id = tour_operator.user_id
                            WHERE users.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $operator = $result->fetch_assoc();

    if ($operator) {
        if ($operator['status'] == 'pending') {
            // Approve
            $conn->query("UPDATE tour_operator SET status = 'approved' WHERE user_id = $id");
            $conn->query("UPDATE users SET verified = 1 WHERE id = $id");

            // Send approval email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'ahmadghosen20@gmail.com';
                $mail->Password = 'bbievwnemblpxuqt';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('ahmadghosen20@gmail.com', 'Lebanon Tours Admin');
                $mail->addAddress($operator['email'], $operator['name']);
                $mail->isHTML(true);
                $mail->Subject = 'Your Account Has Been Approved!';
                $mail->Body = "
                    <div style='font-family: Arial; text-align: center; padding:20px;'>
                        <h2 style='color:#119DA4;'>Congratulations, {$operator['name']}!</h2>
                        <p>Your account as a Tour Operator on Lebanon Tours has been approved.</p>
                        <p>You can now log in and start managing your packages.</p>
                        <br>
                        <p>Best regards,<br>Lebanon Tours Team</p>
                    </div>
                ";
                $mail->send();
            } catch (Exception $e) {
                // Ignore email error silently
            }
        } else {
            // Deactivate
            $conn->query("UPDATE tour_operator SET status = 'pending' WHERE user_id = $id");
            $conn->query("UPDATE users SET verified = 0 WHERE id = $id");
        }

        header("Location: operator.php");
        exit();
    }
}

// Fetch Operators
$search = $_GET['search'] ?? '';
$searchTerm = '%' . $conn->real_escape_string($search) . '%';

if (!empty($search)) {
    $stmt = $conn->prepare("
        SELECT users.*, tour_operator.status FROM users 
        INNER JOIN tour_operator ON users.id = tour_operator.user_id
        WHERE users.role = 'operator' AND (users.name LIKE ? OR users.email LIKE ?)
    ");
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
} else {
    $stmt = $conn->prepare("
        SELECT users.*, tour_operator.status FROM users 
        INNER JOIN tour_operator ON users.id = tour_operator.user_id
        WHERE users.role = 'operator'
    ");
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Operator Management</title>
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
      <h1>Manage Operators</h1>
    </div>
  </div>

  <form method="GET" style="margin-bottom: 1rem; display: flex; justify-content: flex-end;">
    <input type="text" name="search" placeholder="Search operator..." 
           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
           style="padding: 8px; border-radius: 5px; border: 1px solid #ccc; width: 250px;">
    <button type="submit" class="btn-primary" style="margin-left: 10px;">Search</button>
  </form>

  <table style="width: 100%; border-collapse: collapse;">
    <thead style="background-color: #f2f2f2;">
      <tr>
        <th style="padding: 10px;">Name</th>
        <th style="padding: 10px;">Email</th>
        <th style="padding: 10px;">Phone</th>
        <th style="padding: 10px;">Status</th>
        <th style="padding: 10px;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td style="padding: 10px;"><?= htmlspecialchars($row['name']) ?></td>
            <td style="padding: 10px;"><?= htmlspecialchars($row['email']) ?></td>
            <td style="padding: 10px;"><?= htmlspecialchars($row['phone_number']) ?></td>
            <td style="padding: 10px;"><?= htmlspecialchars(ucfirst($row['status'])) ?></td>
            <td style="padding: 10px;">
              <div class="action-buttons">
                <a href="operator.php?toggle_id=<?= $row['id'] ?>" 
                   class="btn-primary" 
                   onclick="return confirm('Are you sure you want to <?= $row['status'] == 'pending' ? 'approve' : 'deactivate' ?> this operator?')">
                  <?= $row['status'] == 'pending' ? 'Approve' : 'Deactivate' ?>
                </a>
                <button class="btn-danger" onclick="deleteOperator(<?= $row['id'] ?>)">Delete</button>
              </div>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" style="padding: 20px; text-align:center;">No operator found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

</main>
</div>

<script>
function deleteOperator(id) {
  if (confirm("Are you sure you want to delete this operator?")) {
    window.location.href = "delete_operator.php?id=" + id;
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