<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Store the current page in session for redirect after login/signup
    $_SESSION['redirect_after_login'] = 'items.php';
    
    // Redirect to login or signup
    header("Location: signup.php");
    exit();
}

// Database connection
include 'dbconnect.php';

// Handle search query
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT * FROM LostItemReport";
$params = [];

// Modify query if search term exists
if (!empty($searchTerm)) {
    $sql .= " WHERE LOWER(item_name) LIKE LOWER(?) OR LOWER(description) LIKE LOWER(?) OR LOWER(location) LIKE LOWER(?)";
    $params = ["%$searchTerm%", "%$searchTerm%", "%$searchTerm%"];
}

// Prepare statement
$stmt = $conn->prepare($sql);

if ($stmt) {
    // Bind parameters dynamically
    if (!empty($params)) {
        $stmt->bind_param("sss", ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $lostItems = $result->fetch_all(MYSQLI_ASSOC);
    
    $stmt->close();
} else {
    die("Error preparing statement: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items - You Lose, We Find</title>
    <link rel="stylesheet" href="items.css">
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
                <li><a href="userdashboard.php">User Dashboard</a></li>
                <li><a href="items.php">Search and Match Items</a></li>
            </ul>
        </nav>
    </header>

    <section id="search" class="section">
        <h1>Search & Match Items</h1>
        <p>Enter keywords, category, or date to search for your lost item. Our system will help match your search with found items.</p>
        <form method="GET" action="items.php">
            <input type="text" name="search" placeholder="Search for items..." value="<?= htmlspecialchars($searchTerm) ?>">
            <button type="submit">Search</button>
        </form>
    </section>

    <section id="lostItems" class="section">
        <h2>Lost Items</h2>
        <div id="lostItemsContainer">
            <?php if (empty($lostItems)): ?>
                <p>No lost items found.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($lostItems as $item): ?>
                        <li>
                            <strong><?= htmlspecialchars($item['item_name']) ?></strong><br>
                            Description: <?= htmlspecialchars($item['description']) ?><br>
                            Location: <?= htmlspecialchars($item['location']) ?><br>
                            Date Lost: <?= htmlspecialchars($item['date_lost']) ?><br>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
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
