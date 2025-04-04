<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'dbconnect.php';

// -------------------------------
// Fetch Pending Lost Item Verifications
// -------------------------------
$pendingQuery = "
    SELECT v.verification_id, l.item_name, v.status 
    FROM Verification v 
    JOIN LostItemReport l ON v.lost_report_id = l.lost_report_id 
    WHERE v.status = 'Pending'
";
$pendingResult = $conn->query($pendingQuery);

// -------------------------------
// Audit Log with Search/Filter
// -------------------------------
$searchTerm = "";
if (isset($_GET['search'])) {
    $searchTerm = $conn->real_escape_string($_GET['search']);
    $logQuery = "SELECT * FROM Audit_Log WHERE action LIKE '%$searchTerm%' ORDER BY timestamp DESC";
} else {
    $logQuery = "SELECT * FROM Audit_Log ORDER BY timestamp DESC";
}
$logResult = $conn->query($logQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - You Lose & We Find</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="image.2.webp" alt="You Lose & We Find">  
        </div>
        <h1>You Lose & We Find</h1>
        <nav>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="adminpanel.php">Admin Panel</a></li>
            </ul>
        </nav>
    </header>
    <section id="admin" class="section">
        <h1>Admin Dashboard</h1>
        <div class="admin-content">
            <!-- Pending Lost Item Verifications -->
            <div class="card">
                <h3>Pending Lost Item Verifications</h3>
                <table>
                    <tr>
                        <th>Item Name</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <?php while ($row = $pendingResult->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['item_name']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td>
                            <a href="process_verification.php?id=<?= $row['verification_id'] ?>&action=approve">Approve</a> | 
                            <a href="process_verification.php?id=<?= $row['verification_id'] ?>&action=reject">Reject</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
            <!-- Audit Log with Search/Filtering -->
            <div class="card">
                <h3>Audit Log</h3>
                <form method="GET" action="adminpanel.php">
                    <input type="text" name="search" placeholder="Search logs..." value="<?= htmlspecialchars($searchTerm) ?>">
                    <button type="submit">Search</button>
                </form>
                <table>
                    <tr>
                        <th>Action</th>
                        <th>Timestamp</th>
                    </tr>
                    <?php while ($logRow = $logResult->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($logRow['action']) ?></td>
                        <td><?= htmlspecialchars($logRow['timestamp']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
            <!-- Additional admin features such as managing users, sending notifications, etc., can be added as new cards -->
        </div>
        <a href="logout.php">Logout</a>
    </section>
    <footer>
        <h2>Contact Us</h2>     
        <p>Email: <a href="mailto:feedback@youlosewefind.com" class="contact-link">Compose For Feedback</a></p>
        <p>Phone: <a href="tel:+254790661716" class="contact-link">Call For Assistance</a></p>
        <p>&copy; 2025 You Lose & We Find. All rights reserved.</p>
    </footer>
    <script src="admin.js"></script>
</body>
</html>
