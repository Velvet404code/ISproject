<?php
session_start();
require 'dbconnect.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Function to sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Process role selection update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_type'])) {
    $user_type = sanitizeInput($_POST['user_type']);
    
    try {
        // Remove existing role associations
        $conn->begin_transaction();
        
        $stmt = $conn->prepare("DELETE FROM Owner WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        
        $stmt = $conn->prepare("DELETE FROM Finder WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        // Insert new role
        if ($user_type === 'owner') {
            $stmt = $conn->prepare("INSERT INTO Owner (user_id) VALUES (?)");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
        } elseif ($user_type === 'finder') {
            $stmt = $conn->prepare("INSERT INTO Finder (user_id) VALUES (?)");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
        }
        
        $conn->commit();
        $_SESSION['user_type'] = $user_type;
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error updating role: " . $e->getMessage();
    }
}

// Process profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';

    // Handle file upload
    $profile_picture = $_SESSION['profile_picture'] ?? 'default-profile.png';
    if (isset($_FILES['profile-picture']) && $_FILES['profile-picture']['error'] == UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['profile-picture']['tmp_name']);
        
        if (in_array($fileType, $allowedTypes)) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['profile-picture']['name']);
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['profile-picture']['tmp_name'], $targetFile)) {
                // Delete old profile picture if not default
                if ($profile_picture !== 'default-profile.png' && file_exists($profile_picture)) {
                    unlink($profile_picture);
                }
                $profile_picture = $targetFile;
            }
        }
    }
    
    // Check profile completion (at least 2 of 3 optional fields)
    $updatedCount = 0;
    if (!empty($profile_picture) && $profile_picture != 'default-profile.png') $updatedCount++;
    if (!empty($phone)) $updatedCount++;
    if (!empty($description)) $updatedCount++;
    $profile_completed = ($updatedCount >= 2) ? 1 : 0;
    
    // Update database
    try {
        $stmt = $conn->prepare("UPDATE Users SET name = ?, email = ?, phone = ?, profile_picture = ?, description = ?, profile_completed = ? WHERE user_id = ?");
        $stmt->bind_param("sssssii", $name, $email, $phone, $profile_picture, $description, $profile_completed, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $_SESSION['phone'] = $phone;
            $_SESSION['description'] = $description;
            $_SESSION['profile_picture'] = $profile_picture;
            $_SESSION['profile_completed'] = $profile_completed;
            $_SESSION['success'] = "Profile updated successfully!";
        }
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error'] = "Error updating profile: " . $e->getMessage();
    }
}

// Process found item report
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_found_item'])) {
    $item_type = sanitizeInput($_POST['item-type']);
    $item_name = sanitizeInput($_POST['found-item-name']);
    $description = sanitizeInput($_POST['found-description']);
    $date_found = sanitizeInput($_POST['found-date']);
    $location = sanitizeInput($_POST['found-location']);
    $status = "pending";
    $picture_path = "";
    
    // Handle file upload
    if (isset($_FILES['found-picture']) && $_FILES['found-picture']['error'] == UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['found-picture']['tmp_name']);
        
        if (in_array($fileType, $allowedTypes)) {
            $uploadDir = 'uploads/items/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['found-picture']['name']);
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['found-picture']['tmp_name'], $targetFile)) {
                $picture_path = $targetFile;
            }
        }
    }
    
    try {
        $conn->begin_transaction();
        
        $stmt = $conn->prepare("INSERT INTO FoundItemReport (user_id, item_type, item_name, description, date_found, location, picture_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $user_id, $item_type, $item_name, $description, $date_found, $location, $picture_path, $status);
        
        if ($stmt->execute()) {
            $found_report_id = $conn->insert_id;
            $stmt->close();
            
            // Generate QR code or barcode
            $qr_code_path = "";
            if ($item_type !== "ID Card") {
                $unique_code = "found_item_" . $found_report_id;
                $qrDir = 'qrcodes/';
                if (!is_dir($qrDir)) {
                    mkdir($qrDir, 0755, true);
                }
                $qrFile = $qrDir . $unique_code . '.png';
                
                require_once('phpqrcode/qrlib.php');
                QRcode::png($unique_code, $qrFile, QR_ECLEVEL_L, 4);
                $qr_code_path = $qrFile;
            } else {
                require_once 'php-barcode-generator/src/BarcodeGenerator.php';
                require_once 'php-barcode-generator/src/BarcodeGeneratorPNG.php';
                
                $id_number = preg_match('/(\d+)/', $description, $matches) ? $matches[1] : $item_name;
                $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                $barcodeData = $generator->getBarcode($id_number, $generator::TYPE_CODE_128);
                
                $barcodeDir = 'barcodes/';
                if (!is_dir($barcodeDir)) {
                    mkdir($barcodeDir, 0755, true);
                }
                
                $barcodeFile = $barcodeDir . $id_number . '_' . time() . '.png';
                file_put_contents($barcodeFile, $barcodeData);
                $qr_code_path = $barcodeFile;
            }
            
            // Update with QR/barcode path
            $stmt = $conn->prepare("UPDATE FoundItemReport SET qr_code_path = ? WHERE found_report_id = ?");
            $stmt->bind_param("si", $qr_code_path, $found_report_id);
            $stmt->execute();
            $stmt->close();
            
            // Run matching algorithm
            require_once('backend/matching.php');
            $logger = new Logger('match_found_item_log.txt');
            $nameRule = new ItemNameMatchRule();
            $locationRule = new LocationMatchRule();
            $combinedRule = new CombinedMatchRule([$nameRule, $locationRule]);
            $matchingEngine = new MatchingEngine([$nameRule, $locationRule, $combinedRule], $logger);
            
            // Get pending lost items
            $lostItems = [];
            $result = $conn->query("SELECT * FROM LostItemReport WHERE status = 'pending'");
            while ($row = $result->fetch_assoc()) {
                $lostItems[] = $row;
            }
            
            // Get the found item
            $stmt = $conn->prepare("SELECT * FROM FoundItemReport WHERE found_report_id = ?");
            $stmt->bind_param("i", $found_report_id);
            $stmt->execute();
            $foundItem = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            // Run matching
            $matchingEngine->processMatch($foundItem, $lostItems);
            
            $conn->commit();
            $_SESSION['success'] = "Found item reported successfully!";
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error reporting found item: " . $e->getMessage();
    }
    
    header("Location: userdashboard.php");
    exit();
}

// Process lost item report
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_lost_item'])) {
    $item_name = sanitizeInput($_POST['lost-item-name']);
    $description = sanitizeInput($_POST['lost-description']);
    $date_lost = sanitizeInput($_POST['lost-date']);
    $location = sanitizeInput($_POST['lost-location']);
    $status = "pending";
    $picture_path = "";
    
    // Handle file upload
    if (isset($_FILES['lost-picture']) && $_FILES['lost-picture']['error'] == UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['lost-picture']['tmp_name']);
        
        if (in_array($fileType, $allowedTypes)) {
            $uploadDir = 'uploads/items/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['lost-picture']['name']);
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['lost-picture']['tmp_name'], $targetFile)) {
                $picture_path = $targetFile;
            }
        }
    }
    
    try {
        $conn->begin_transaction();
        
        $stmt = $conn->prepare("INSERT INTO LostItemReport (user_id, item_name, description, date_lost, location, picture_path, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $user_id, $item_name, $description, $date_lost, $location, $picture_path, $status);
        
        if ($stmt->execute()) {
            $lost_report_id = $conn->insert_id;
            $stmt->close();
            
            // Generate QR code
            $unique_code = "lost_item_" . $lost_report_id;
            $qrDir = 'qrcodes/';
            if (!is_dir($qrDir)) {
                mkdir($qrDir, 0755, true);
            }
            $qrFile = $qrDir . $unique_code . '.png';
            
            require_once('phpqrcode/qrlib.php');
            QRcode::png($unique_code, $qrFile, QR_ECLEVEL_L, 4);
            
            // Update with QR code path
            $stmt = $conn->prepare("UPDATE LostItemReport SET qr_code = ? WHERE lost_report_id = ?");
            $stmt->bind_param("si", $qrFile, $lost_report_id);
            $stmt->execute();
            $stmt->close();
            
            $conn->commit();
            $_SESSION['success'] = "Lost item reported successfully!";
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error reporting lost item: " . $e->getMessage();
    }
    
    header("Location: userdashboard.php");
    exit();
}

// Get current user data
$user_type = $_SESSION['user_type'] ?? '';
$profileCompleted = isset($_SESSION['profile_completed']) && $_SESSION['profile_completed'] == 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - You Lose, We Find</title>
    <link rel="stylesheet" href="userdashboard.css">
    <style>
        /* Inline styles for spacing and layout */
        .section { margin: 20px 0; }
        .form-group { margin-bottom: 15px; }
        .radio-group { margin-bottom: 10px; }
        .profile-picture-container { margin-bottom: 15px; }
        .rounded-profile-picture {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            display: block;
            margin-bottom: 10px;
        }
        .toggle-link { cursor: pointer; color: blue; text-decoration: underline; }
        .role-info { margin-bottom: 10px; }
        
        /* NEW CODE: Add styles for success and error messages */
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        /* END NEW CODE */
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="image.2.webp" alt="You Lose, We Find">
        </div>
        <h1>You Lose & We Find</h1>
        <nav>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="items.php">Search and Match Items</a></li>
                <li>
                    <label for="dropdown" style="display: none;">Choose an option:</label>
                    <select id="dropdown" name="options" onchange="location.href = this.value;" class="styled-dropdown">
                        <option value="#user-profile">User Profile</option>
                        <?php if ($user_type === 'owner'): ?>
                            <option value="#report-lost-item">Owner's Form</option>
                        <?php elseif ($user_type === 'finder'): ?>
                            <option value="#report-found-item">Finder's Form</option>
                        <?php else: ?>
                            <option value="#report-lost-item">Owner's Form</option>
                            <option value="#report-found-item">Finder's Form</option>
                        <?php endif; ?>
                    </select>
                </li>
                <li><a href="logout.php" class="logout-button">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section id="dashboard" class="section">
            <h1>User Dashboard</h1>
            <p>Welcome, <span id="userNameDisplay"><?php echo htmlspecialchars($_SESSION['name']); ?></span>!</p>
            
            <!-- NEW CODE: Display success and error messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <!-- END NEW CODE -->
            
            <?php if (!$user_type): ?>
                <!-- Role selection form if no role is set -->
                <h2>Please select your role:</h2>
                <form method="POST" action="">
                    <div class="radio-group">
                        <input type="radio" id="finder" name="user_type" value="finder" required>
                        <label for="finder">Finder</label>
                    </div>
                    <div class="radio-group">
                        <input type="radio" id="owner" name="user_type" value="owner" required>
                        <label for="owner">Owner</label>
                    </div>
                    <button type="submit">Submit</button>
                </form>
            <?php else: ?>

                <div class="role-info">
                    <?php if ($user_type === 'finder'): ?>
                        <p>You are logged in as a <strong>Finder</strong> (<span class="toggle-link" id="toggleRole">Lost something?</span>)</p>
                    <?php elseif ($user_type === 'owner'): ?>
                        <p>You are logged in as an <strong>Owner</strong> (<span class="toggle-link" id="toggleRole">Need to find something?</span>)</p>
                    <?php endif; ?>
                </div>

                <p>Use the forms below to report lost or found items. Your reports help us match lost items with their rightful owners.</p>
                <!-- Hidden role update form -->
                <div id="roleUpdateForm" style="display:none; margin-bottom:20px;">
                    <form method="POST" action="">
                        <div class="radio-group">
                            <input type="radio" id="finder_role" name="user_type" value="finder" required <?php echo ($user_type === 'finder' ? 'checked' : ''); ?>>
                            <label for="finder_role">Finder</label>
                        </div>
                        <div class="radio-group">
                            <input type="radio" id="owner_role" name="user_type" value="owner" required <?php echo ($user_type === 'owner' ? 'checked' : ''); ?>>
                            <label for="owner_role">Owner</label>
                        </div>
                        <button type="submit">Update Role</button>
                    </form>
                </div>
            <?php endif; ?>
        </section>
        
        <section id="user-profile" class="section">
            <h2>User Profile</h2>
            <?php 
            // UPDATED: Instead of checking for phone or description, we now check if the profile is marked as complete.
            if ($profileCompleted): 
            ?>
                <div id="profileView">
                    <div class="profile-picture-container">
                        <img src="<?php echo htmlspecialchars($_SESSION['profile_picture']); ?>" alt="Profile Picture" class="rounded-profile-picture">
                    </div>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($_SESSION['phone']); ?></p>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($_SESSION['description']); ?></p>
                    <button id="editProfileBtn">Edit Profile</button>
                </div>
                <div id="profileEdit" style="display:none;">
                    <form id="userProfileForm" method="POST" action="" enctype="multipart/form-data">
                        <div class="profile-picture-container">
                            <label for="profile-picture" class="profile-picture-label">
                                <img src="<?php echo htmlspecialchars($_SESSION['profile_picture']); ?>" alt="Profile Picture" id="profile-picture-preview" class="rounded-profile-picture">
                                <input type="file" id="profile-picture" name="profile-picture" accept="image/*" style="display: none;">
                            </label>
                        </div>
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number:</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_SESSION['phone']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="description">Short Description (optional):</label>
                            <textarea id="description" name="description" placeholder="Tell us something about yourself..."><?php echo htmlspecialchars($_SESSION['description']); ?></textarea>
                        </div>
                        <button type="submit" name="update_profile">Update Profile</button>
                    </form>
                    <button id="cancelEditBtn">Cancel</button>
                </div>
            <?php else: ?>
                <!-- UPDATED: If profile is not completed, show the update form immediately -->
                <form id="userProfileForm" method="POST" action="" enctype="multipart/form-data">
                    <div class="profile-picture-container">
                        <label for="profile-picture" class="profile-picture-label">
                            <img src="<?php echo isset($_SESSION['profile_picture']) ? htmlspecialchars($_SESSION['profile_picture']) : 'default-profile.png'; ?>" alt="Profile Picture" id="profile-picture-preview" class="rounded-profile-picture">
                            <input type="file" id="profile-picture" name="profile-picture" accept="image/*" style="display: none;">
                        </label>
                    </div>
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number:</label>
                        <input type="tel" id="phone" name="phone">
                    </div>
                    <div class="form-group">
                        <label for="description">Short Description (optional):</label>
                        <textarea id="description" name="description" placeholder="Tell us something about yourself..."></textarea>
                    </div>
                    <button type="submit" name="update_profile">Update Profile</button>
                </form>
            <?php endif; ?>
        </section>
        
        <?php if ($user_type === 'owner'): ?>
            <section id="report-lost-item" class="section">
                <h2>Report Lost Item</h2>
                
                <!-- UPDATED: Modified form to submit to server with proper enctype -->
                <form id="lostItemForm" method="POST" action="" enctype="multipart/form-data">
                    <!-- Lost Item Form Fields -->
                    <div class="form-group">
                        <label for="lost-item-name">Item Name:</label>
                        <input type="text" id="lost-item-name" name="lost-item-name" required>
                    </div>
                    <div class="form-group">
                        <label for="lost-description">Description:</label>
                        <textarea id="lost-description" name="lost-description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="lost-picture">Attach Picture:</label>
                        <input type="file" id="lost-picture" name="lost-picture" accept="image/*" required>
                    </div>
                    <div class="form-group">
                        <label for="lost-date">Date Lost:</label>
                        <input type="date" id="lost-date" name="lost-date" required>
                    </div>
                    <div class="form-group">
                        <label for="lost-location">Location:</label>
                        <input type="text" id="lost-location" name="lost-location" required>
                    </div>
                    <button type="submit" name="submit_lost_item">Submit</button>
                </form>
            </section>
        <?php elseif ($user_type === 'finder'): ?>
            <section id="report-found-item" class="section">
                <h2>Report Found Item</h2>
                
                <!-- UPDATED: Modified form to submit to server with proper enctype -->
                <form id="foundItemForm" method="POST" action="" enctype="multipart/form-data">
                    <!-- Found Item Form Fields -->
                    <div class="form-group">
                        <label for="item-type">Select Item Type:</label><br>
                        <input type="radio" id="id-card" name="item-type" value="ID Card" required>
                        <label for="id-card">ID Card</label>
                    </div>
                    <div class="form-group">
                        <input type="radio" id="phone" name="item-type" value="Phone">
                        <label for="phone">Phone</label>
                    </div>
                    <div class="form-group">
                        <input type="radio" id="wallet" name="item-type" value="Wallet">
                        <label for="wallet">Wallet</label>
                    </div>
                    <div class="form-group">
                        <input type="radio" id="other" name="item-type" value="Other">
                        <label for="other">Other</label>
                    </div>
                    <div class="form-group">
                        <label for="found-item-name">Item Name:</label>
                        <input type="text" id="found-item-name" name="found-item-name" required>
                    </div>
                    <div class="form-group">
                        <label for="found-description">Description:</label>
                        <textarea id="found-description" name="found-description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="found-picture">Attach Picture:</label>
                        <input type="file" id="found-picture" name="found-picture" accept="image/*" required>
                    </div>
                    <div class="form-group">
                        <label for="found-date">Date Found:</label>
                        <input type="date" id="found-date" name="found-date" required>
                    </div>
                    <div class="form-group">
                        <label for="found-location">Location:</label>
                        <input type="text" id="found-location" name="found-location" required>
                    </div>
                    <button type="submit" name="submit_found_item">Submit</button>
                </form>
            </section>
        <?php endif; ?>
    </main>

    <footer>
        <h2>Contact Us</h2>
        <p>Email: <a href="mailto:feedback@youlosewefind.com" class="contact-link">Compose For Feedback</a></p>
        <p>Phone: <a href="tel:+254790661716" class="contact-link">Call For Assistance</a></p>
        <p>&copy; 2025 You Lose & We Find. All rights reserved.</p>
    </footer>

    <script>
        // Toggle profile edit form
        const editBtn = document.getElementById('editProfileBtn');
        const cancelBtn = document.getElementById('cancelEditBtn');
        if(editBtn){
            editBtn.addEventListener('click', function(){
                document.getElementById('profileView').style.display = 'none';
                document.getElementById('profileEdit').style.display = 'block';
            });
        }
        if(cancelBtn){
            cancelBtn.addEventListener('click', function(){
                document.getElementById('profileEdit').style.display = 'none';
                document.getElementById('profileView').style.display = 'block';
            });
        }
        
        // Toggle role update form when clicking on the toggle link
        const toggleRoleLink = document.getElementById('toggleRole');
        if(toggleRoleLink){
            toggleRoleLink.addEventListener('click', function(){
                const roleForm = document.getElementById('roleUpdateForm');
                if(roleForm.style.display === 'none' || roleForm.style.display === ''){
                    roleForm.style.display = 'block';
                } else {
                    roleForm.style.display = 'none';
                }
            });
        }
        
        // Preview uploaded profile picture
        const profilePictureInput = document.getElementById('profile-picture');
        const profilePicturePreview = document.getElementById('profile-picture-preview');
        if(profilePictureInput && profilePicturePreview){
            profilePictureInput.addEventListener('change', function(){
                if(this.files && this.files[0]){
                    const reader = new FileReader();
                    reader.onload = function(e){
                        profilePicturePreview.src = e.target.result;
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    </script>
</body>
</html>
