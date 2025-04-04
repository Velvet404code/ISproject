<?php
// display_inventory.php
require 'db_connect.php';

// Fetch all lost item reports
$lostItems = $pdo->query("SELECT lost_report_id, item_name, description, date_lost, location, status 
                          FROM LostItemReport")
                 ->fetchAll(PDO::FETCH_ASSOC);

// Fetch all found item reports
$foundItems = $pdo->query("SELECT found_report_id, item_name, description, date_found, location, status 
                           FROM FoundItemReport")
                  ->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inventory Report</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>Inventory Report</h1>
  
  <h2>Lost Items</h2>
  <table border="1" cellspacing="0" cellpadding="5">
    <thead>
      <tr>
        <th>Report ID</th>
        <th>Item Name</th>
        <th>Description</th>
        <th>Date Lost</th>
        <th>Location</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($lostItems as $item): ?>
      <tr>
         <td><?php echo htmlspecialchars($item['lost_report_id']); ?></td>
         <td><?php echo htmlspecialchars($item['item_name']); ?></td>
         <td><?php echo htmlspecialchars($item['description']); ?></td>
         <td><?php echo htmlspecialchars($item['date_lost']); ?></td>
         <td><?php echo htmlspecialchars($item['location']); ?></td>
         <td><?php echo htmlspecialchars($item['status']); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  
  <h2>Found Items</h2>
  <table border="1" cellspacing="0" cellpadding="5">
    <thead>
      <tr>
        <th>Report ID</th>
        <th>Item Name</th>
        <th>Description</th>
        <th>Date Found</th>
        <th>Location</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($foundItems as $item): ?>
      <tr>
         <td><?php echo htmlspecialchars($item['found_report_id']); ?></td>
         <td><?php echo htmlspecialchars($item['item_name']); ?></td>
         <td><?php echo htmlspecialchars($item['description']); ?></td>
         <td><?php echo htmlspecialchars($item['date_found']); ?></td>
         <td><?php echo htmlspecialchars($item['location']); ?></td>
         <td><?php echo htmlspecialchars($item['status']); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
