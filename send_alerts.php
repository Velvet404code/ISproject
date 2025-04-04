<?php
// send_alerts.php
require 'db_connect.php';

// Fetch owners
$owners = $pdo->query("SELECT u.user_id, u.email, u.name 
                       FROM Users u 
                       JOIN Owner o ON u.user_id = o.user_id")
              ->fetchAll(PDO::FETCH_ASSOC);
// Fetch finders
$finders = $pdo->query("SELECT u.user_id, u.email, u.name 
                        FROM Users u 
                        JOIN Finder f ON u.user_id = f.user_id")
              ->fetchAll(PDO::FETCH_ASSOC);

$subject = "";
$message = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $recipients = isset($_POST['recipients']) ? $_POST['recipients'] : [];
    
    foreach ($recipients as $user_id) {
        // Retrieve recipient details
        $stmt = $pdo->prepare("SELECT email, name FROM Users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $to = $user['email'];
            // Insert notification record. Here, match_id is set to 0 (or you may modify as needed).
            $stmt2 = $pdo->prepare("INSERT INTO Notification (user_id, match_id, message) VALUES (?, ?, ?)");
            $stmt2->execute([$user_id, 0, $message]);
            
            // Send email alert
            $headers = "From: no-reply@youlosewefind.com\r\n";
            mail($to, $subject, $message, $headers);
        }
    }
    $success = "Alerts sent successfully.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Send Alerts</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>Send Alert Notifications</h1>
  <?php if ($success): ?>
    <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
  <?php endif; ?>
  <form method="post" action="send_alerts.php">
    <label for="subject">Subject:</label><br>
    <input type="text" id="subject" name="subject" required value="<?php echo htmlspecialchars($subject); ?>"><br><br>
    
    <label for="message">Message:</label><br>
    <textarea id="message" name="message" rows="5" cols="50" required><?php echo htmlspecialchars($message); ?></textarea><br><br>
    
    <h3>Select Recipients:</h3>
    <fieldset>
      <legend>Owners</legend>
      <?php foreach($owners as $owner): ?>
        <input type="checkbox" name="recipients[]" value="<?php echo $owner['user_id']; ?>"> 
        <?php echo htmlspecialchars($owner['name'] . ' (' . $owner['email'] . ')'); ?><br>
      <?php endforeach; ?>
    </fieldset>
    
    <fieldset>
      <legend>Finders</legend>
      <?php foreach($finders as $finder): ?>
        <input type="checkbox" name="recipients[]" value="<?php echo $finder['user_id']; ?>"> 
        <?php echo htmlspecialchars($finder['name'] . ' (' . $finder['email'] . ')'); ?><br>
      <?php endforeach; ?>
    </fieldset>
    
    <button type="submit">Send Alerts</button>
  </form>
</body>
</html>
