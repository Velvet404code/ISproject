<?php
include 'dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Prepare SQL statement (without user_id, since it's auto-increment)
    $sql = "INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssss", $name, $email, $phone, $password);

        if ($stmt->execute()) {
            echo "Registration successful!";
            header("Location: login.php"); // Redirect to login page
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - You Lose, We Find</title>
  <link rel="stylesheet" href="signup.css">
  <!--script src="signup.js" defer></script-->
  <style>
    /* Extra styling for the admin/custodian links */
    .header-extra {
      position: absolute;
      top: 10px;
      right: 10px;
    }
    .header-extra a {
      margin-left: 10px;
      font-size: 0.9em;
      color: #007BFF;
      text-decoration: none;
    }
    .header-extra a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="image.2.webp" alt="You Lose, We Find">
    </div>
    <h1>You Lose, We Find</h1>
    <!-- NEW: Extra links for Admin and Custodian registration -->
    <div class="header-extra">
      <a href="admin_signup.php">Admin Portal</a>
      <a href="custodian_signup.php">Custodian Portal</a>
    </div>
  </header>
  <main>
    <section>
      <h1>Sign Up</h1>
      <form id="signupForm" action="signup.php" method="POST">
        <div class="form-group">
          <label for="name">Full Names:</label>
          <input type="text" id="name" name="name" placeholder="Enter your full names" required />
        </div>
        <!--div class="form-group">
          <label for="username">Username:</label>
          <input type="text" id="username" name="username" placeholder="Enter your username" required />
        </div-->
        <div class="form-group">
          <label for="email">Email:</label>
          <input type="email" id="email" name="email" placeholder="Enter your email" required />
        </div>
        <div class="form-group">
          <label for="phone">Contact:</label>
          <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required />
        </div>
        <div class="form-group">
          <label for="password">Create Password:</label>
          <input type="password" id="password" name="password" placeholder="Enter your password" required />
        </div>
        <div class="form-group">
          <label for="confirm-password">Confirm Password:</label>
          <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm your password" required />
        </div>
        <button type="submit">Sign Up</button>
        <p class="signin-link">Already have an account? <a href="login.php">Sign in here</a></p>
      </form>
    </section>
  </main>
  <footer>
    <h2>Contact Us</h2>     
    <p>Email: <a href="mailto:feedback@youlosewefind.com" class="contact-link">Compose For Feedback</a></p>
    <p>Phone: <a href="tel:+254790661716" class="contact-link">Call For Assistance</a></p>
    <p>&copy; 2025 You Lose & We Find. All rights reserved.</p>
  </footer>
</body>
</html>
