<?php
session_start();
require 'dbconnect.php'; // Database connection

if (!isset($_SESSION['custodian_id'])) {
    header("Location: login.php");
    exit();
}

$custodian_id = $_SESSION['custodian_id'];
$query = $pdo->prepare("SELECT c.name, r.role_name FROM custodians c JOIN roles r ON c.role_id = r.id WHERE c.id = ?");
$query->execute([$custodian_id]);
$user = $query->fetch();

$role = $user['role_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Custodian Dashboard</title>
  <link rel="stylesheet" href="custodian.css">
</head>
<body>
    <header>
        <div class="logo">
          <img src="image.2.webp" alt="You Lose & We Find">  
        </div>
        <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?> (<?php echo $role; ?>)</h1>
        <nav>
          <ul>
            <li><a href="home.html">Home</a></li>
            <li><a href="logout.php">Logout</a></li>
          </ul>
        </nav>
    </header>
    <section id="custodian" class="section">
        <h1>Custodian Dashboard</h1>
        <div class="custodian-content">
            <div class="card">
                <h3>Manage Lost & Found Items</h3>
                <button onclick="location.href='view_items.php'">View Items</button>
            </div>
            <?php if ($role == 'Verifier' || $role == 'Admin') : ?>
            <div class="card">
                <h3>Verify Claims</h3>
                <button onclick="location.href='verify_claims.php'">Verify Claims</button>
            </div>
            <?php endif; ?>
            
            <?php if ($role == 'Notifier' || $role == 'Admin') : ?>
            <div class="card">
                <h3>Send Alerts</h3>
                <button onclick="location.href='send_alerts.php'">Send Alerts</button>
            </div>
            <?php endif; ?>
            
            <div class="card">
                <h3>Display Inventory Report</h3>
                <button onclick="location.href='display_inventory.php'">Display Report</button>
            </div>
        </div>
    </section>
    <footer>
        <h2>Contact Us</h2>     
        <p>Email: <a href="mailto:feedback@youlosewefind.com" class="contact-link">Compose For Feedback</a></p>
        <p>Phone: <a href="tel:+254790661716" class="contact-link">Call For Assistance</a></p>
        <p>&copy; 2025 You Lose & We Find. All rights reserved.</p>
    </footer>
</body>
</html>
