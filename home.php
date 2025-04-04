<?php
session_start();
include 'dbconnect.php';

// If the user is logged in, you can optionally fetch more details from the DB.
// For this example, we assume profile data is stored in session.
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home - You Lose, We Find</title>
  <link rel="stylesheet" href="home.css">
</head>
<body>
  <header>
    <div class="logo">
      <img src="image.2.webp" alt="You Lose & We Find">  
    </div>
    <h1>You Lose & We Find</h1>
    <nav>
      <ul>
        <?php if ($isLoggedIn): ?>
          <li><a href="userdashboard.php">Dashboard</a></li>
          <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
          <li><a href="login.php">Login/Sign Up</a></li>
        <?php endif; ?>
        <li><a href="items.php">Search and Match Items</a></li>
      </ul>
    </nav>
  </header>
  <section class="hero">
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <?php if ($isLoggedIn): ?>
        <h1 class="standout">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
        <p>Your profile is ready. View or update your information in your Dashboard.</p>
        <a href="userdashboard.php" class="cta-button">Go To Dashboard</a>
      <?php else: ?>
        <h1 class="standout">Welcome to You Lose &amp; We Find</h1>
        <p>Your trusted platform for efficient item recovery</p>
        <a href="login.php" class="cta-button">Proceed To Report</a>
      <?php endif; ?>
    </div>
  </section>
  <main>
    <?php if ($isLoggedIn): ?>
      <!-- Display a simple profile summary -->
      <section class="profile-summary fade-in">
        <h2>Your Profile</h2>
        <div class="profile-card">
          <img src="<?php echo isset($_SESSION['profile_picture']) ? htmlspecialchars($_SESSION['profile_picture']) : 'default-profile.png'; ?>" alt="Profile Picture" class="profile-thumb">
          <div class="profile-info">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
            <?php if (!empty($_SESSION['description'])): ?>
              <p><strong>About You:</strong> <?php echo htmlspecialchars($_SESSION['description']); ?></p>
            <?php endif; ?>
          </div>
        </div>
      </section>
    <?php endif; ?>
    
    <section class="features fade-in">
      <h2>How It Works</h2>
      <div class="feature-cards">
        <div class="card">
          <img src="image.4.jpg" alt="Report">
          <h3>Report</h3>
          <p>Quickly report your lost or found item.</p>
        </div>
        <div class="card">
          <img src="image.11.webp" alt="Match">
          <h3>Match</h3>
          <p>Our system matches the item's details seamlessly.</p>
        </div>
        <div class="card">
          <img src="image.6.webp" alt="Recover">
          <h3>Recover</h3>
          <p>We connect with the rightful owner to recover the item.</p>
        </div>
      </div>
    </section>
    <section class="recent-items fade-in">
      <h2>Recent Items</h2>
      <ul id="items-list">
        <li class="lost">Lost: Black Wallet <a href="items.php" class="details">View Details</a></li>
        <li class="found">Found: Set of Keys <a href="items.php" class="details">View Details</a></li>
        <li class="lost">Lost: Blue Bag <a href="items.php" class="details">View Details</a></li>
        <li class="found">Found: Silver Watch <a href="items.php" class="details">View Details</a></li>
      </ul>
    </section>
    <section class="testimonials fade-in">
      <h2>Testimonials</h2>
      <p>Read what our users have to say about our platform</p>
      <div class="testimonial-cards">
        <div class="card">
          <h3>Gabriella Githinji</h3>
          <p>"I lost my phone and within a few hours, I had it back. Thank you!"</p>
        </div>
        <div class="card">
          <h3>Maxximus Mutiso</h3>
          <p>"I found a laptop and reported it. The owner was so grateful!"</p>
        </div>
      </div>
    </section>
  </main>
  <footer>
    <h2>Contact Us</h2>     
    <p>Email: <a href="mailto:feedback@youlosewefind.com" class="contact-link">Compose For Feedback</a></p>
    <p>Phone: <a href="tel:+254790661716" class="contact-link">Call For Assistance</a></p>
    <p>&copy; 2025 You Lose & We Find. All rights reserved.</p>
  </footer>
  <script src="home.js"></script>
</body>
</html>
