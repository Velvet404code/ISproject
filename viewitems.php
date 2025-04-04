<?php
// view_items.php
require 'dbconnect.php';  // Ensure this file sets up your $pdo connection

// Query to fetch all found item reports along with finder information
$sql = "SELECT f.found_report_id, f.item_name, f.description, f.date_found, f.location, u.name AS finder_name, u.email 
        FROM FoundItemReport f
        JOIN Users u ON f.user_id = u.user_id";
$stmt = $pdo->query($sql);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Found Items</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>Found Items Reported by Finders</h1>
  <table border="1" cellspacing="0" cellpadding="5">
    <thead>
      <tr>
        <th>ID</th>
        <th>Item Name</th>
        <th>Description</th>
        <th>Date Found</th>
        <th>Location</th>
        <th>Finder Name</th>
        <th>Finder Email</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($items as $item): ?>
      <tr>
         <td><?php echo htmlspecialchars($item['found_report_id']); ?></td>
         <td><?php echo htmlspecialchars($item['item_name']); ?></td>
         <td><?php echo htmlspecialchars($item['description']); ?></td>
         <td><?php echo htmlspecialchars($item['date_found']); ?></td>
         <td><?php echo htmlspecialchars($item['location']); ?></td>
         <td><?php echo htmlspecialchars($item['finder_name']); ?></td>
         <td><?php echo htmlspecialchars($item['email']); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
