<?php
// verify_claims.php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verification_id'], $_POST['action'])) {
    $verification_id = $_POST['verification_id'];
    $action = $_POST['action']; // Expected values: "Approved" or "Rejected"
    $stmt = $pdo->prepare("UPDATE Verification SET status = ?, date_verified = NOW() WHERE verification_id = ?");
    $stmt->execute([$action, $verification_id]);
    header("Location: verify_claims.php");
    exit();
}

$sql = "SELECT v.verification_id, l.lost_report_id, l.item_name, l.description, l.date_lost, l.location, u.name AS owner_name, v.status
        FROM Verification v
        JOIN LostItemReport l ON v.lost_report_id = l.lost_report_id
        JOIN Users u ON l.user_id = u.user_id
        WHERE v.status = 'Pending'";
$stmt = $pdo->query($sql);
$claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verify Claims</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>Pending Lost Item Claims for Verification</h1>
  <table border="1" cellspacing="0" cellpadding="5">
    <thead>
      <tr>
        <th>Verification ID</th>
        <th>Lost Report ID</th>
        <th>Item Name</th>
        <th>Description</th>
        <th>Date Lost</th>
        <th>Location</th>
        <th>Owner Name</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($claims as $claim): ?>
      <tr>
         <td><?php echo htmlspecialchars($claim['verification_id']); ?></td>
         <td><?php echo htmlspecialchars($claim['lost_report_id']); ?></td>
         <td><?php echo htmlspecialchars($claim['item_name']); ?></td>
         <td><?php echo htmlspecialchars($claim['description']); ?></td>
         <td><?php echo htmlspecialchars($claim['date_lost']); ?></td>
         <td><?php echo htmlspecialchars($claim['location']); ?></td>
         <td><?php echo htmlspecialchars($claim['owner_name']); ?></td>
         <td><?php echo htmlspecialchars($claim['status']); ?></td>
         <td>
             <form method="post" action="verify_claims.php">
                 <input type="hidden" name="verification_id" value="<?php echo $claim['verification_id']; ?>">
                 <button type="submit" name="action" value="Approved">Approve</button>
                 <button type="submit" name="action" value="Rejected">Reject</button>
             </form>
         </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
