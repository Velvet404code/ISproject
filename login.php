<?php
session_start();
include 'dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Using email as the login credential
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Prepare SQL to retrieve user record
    $sql = "SELECT user_id, password, name FROM Users WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $hashed_password, $name);
            $stmt->fetch();

            // Verify password
            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['email'] = $email;
                $_SESSION['name'] = $name;

                // Default role for regular users
                $_SESSION['role'] = 'user';
                $user_type = ''; // for owner/finder determination

                // Check Admin table
                $sqlAdmin = "SELECT admin_id FROM Admin WHERE user_id = ?";
                $stmtAdmin = $conn->prepare($sqlAdmin);
                $stmtAdmin->bind_param("i", $user_id);
                $stmtAdmin->execute();
                $stmtAdmin->store_result();
                if ($stmtAdmin->num_rows > 0) {
                    $_SESSION['role'] = 'admin';
                    header("Location: adminpanel.php");
                    exit();
                }
                $stmtAdmin->close();

                // Check Custodian table
                $sqlCustodian = "SELECT custodian_id FROM Custodian WHERE user_id = ?";
                $stmtCustodian = $conn->prepare($sqlCustodian);
                if (!$stmtCustodian) {
                    // Error preparing statement – log this or handle accordingly
                    die("Prepare failed (Custodian): " . $conn->error);
                }
                $sqlCustodian = "SELECT custodian_id FROM Custodian WHERE user_id = ?";
                $stmtCustodian = $conn->prepare($sqlCustodian);
                $stmtCustodian->bind_param("i", $user_id);
                $stmtCustodian->execute();
                $stmtCustodian->store_result();
                if ($stmtCustodian->num_rows > 0) {
                    $_SESSION['role'] = 'custodian';
                    header("Location: custodianpanel.php");
                    exit();
                }
                $stmtCustodian->close();

                // Check if user already exists in Owner table
                $sqlOwner = "SELECT owner_id FROM Owner WHERE user_id = ?";
                $stmtOwner = $conn->prepare($sqlOwner);
                $stmtOwner->bind_param("i", $user_id);
                $stmtOwner->execute();
                $stmtOwner->store_result();
                if ($stmtOwner->num_rows > 0) {
                    $user_type = 'owner';
                }
                $stmtOwner->close();

                // Check if user already exists in Finder table
                $sqlFinder = "SELECT finder_id FROM Finder WHERE user_id = ?";
                $stmtFinder = $conn->prepare($sqlFinder);
                $stmtFinder->bind_param("i", $user_id);
                $stmtFinder->execute();
                $stmtFinder->store_result();
                if ($stmtFinder->num_rows > 0) {
                    $user_type = 'finder';
                }
                $stmtFinder->close();

                if ($user_type != '') {
                    $_SESSION['user_type'] = $user_type;
                }

                $_SESSION['success'] = "✅ Login successful! Redirecting...";
                header("Location: userdashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Incorrect password or Email!";
            }
        } else {
            $_SESSION['error'] = "❌ User not found!";
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "❌ Database error: " . $conn->error;
    }

    $conn->close();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - You Lose & We Find</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="image.2.webp" alt="You Lose & We Find">
        </div>
        <h1>You Lose & We Find</h1>
    </header>
    <main>
        <section>
            <h1>Login</h1>

            <!-- Display messages -->
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

            <form id="loginForm" action="login.php" method="POST">
                <div class="form-group">
                    <!-- Changed label from Username to Email -->
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required />
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required />
                </div>
                <button type="submit">Login</button>
                <p class="signup-link">Don't have an account? <a href="signup.php">Sign up here</a></p>
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
