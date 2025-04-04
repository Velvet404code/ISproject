<?php
session_start();
include 'dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $location = trim($_POST['location']);
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert into Users table
    $sqlUser = "INSERT INTO Users (name, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sqlUser);
    $stmt->bind_param("sss", $name, $email, $hashed_password);
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        $stmt->close();
        
        // Insert into Custodian table (including location)
        $sqlCustodian = "INSERT INTO Custodian (user_id, location) VALUES (?, ?)";
        $stmt = $conn->prepare($sqlCustodian);
        $stmt->bind_param("is", $user_id, $location);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Custodian account created successfully!";
            header("Location: login.php");
            exit();
        } else {
            $_SESSION['error'] = "Error creating custodian record: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Error creating user account: " . $stmt->error;
        $stmt->close();
    }
    
    $conn->close();
    header("Location: custodian_signup.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custodian Registration - You Lose & We Find</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="image.2.webp" alt="You Lose & We Find">
        </div>
        <h1>Custodian Registration</h1>
    </header>
    <main>
        <section>
            <h2>Create Custodian Account</h2>
            <?php
            if (isset($_SESSION['error'])) {
                echo "<div class='error'>" . $_SESSION['error'] . "</div>";
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo "<div class='success'>" . $_SESSION['success'] . "</div>";
                unset($_SESSION['success']);
            }
            ?>
            <form action="custodian_signup.php" method="POST">
                <div class="form-group">
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" name="name" placeholder="Enter your name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Create a password" required>
                </div>
                <div class="form-group">
                    <label for="location">Location:</label>
                    <input type="text" id="location" name="location" placeholder="Enter your work location" required>
                </div>
                <button type="submit">Register as Custodian</button>
            </form>
            <p><a href="login.php">Back to Login</a></p>
        </section>
    </main>
    <footer>
        <p>&copy; 2025 You Lose & We Find. All rights reserved.</p>
    </footer>
</body>
</html>
