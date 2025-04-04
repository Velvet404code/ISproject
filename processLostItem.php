<?php
//QR Code

// --- Database connection setup ---
$servername = "localhost";
$username   = "root";         
$password   = "";             
$dbname     = "lost_and_found";    

// Create a connection using mysqli
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Capture and sanitize form inputs ---
$user_id     = $conn->real_escape_string($_POST['user_id']);       // ideally from session
$item_name   = $conn->real_escape_string($_POST['item_name']);
$description = $conn->real_escape_string($_POST['description']);
$date_lost   = $conn->real_escape_string($_POST['date_lost']);       
$location    = $conn->real_escape_string($_POST['location']);
$status      = $conn->real_escape_string($_POST['status']);          // e.g., "pending"

// --- Insert the lost item into the database ---
$sql = "INSERT INTO LostItemReport (user_id, item_name, description, date_lost, location, status)
        VALUES ('$user_id', '$item_name', '$description', '$date_lost', '$location', '$status')";
if ($conn->query($sql) === TRUE) {
    // Get the auto-generated lost_report_id
    $lost_report_id = $conn->insert_id;
    
    // Create a unique code using the lost_report_id
    $unique_code = "lost_item_" . $lost_report_id;
    
    // --- QR Code Generation ---
    // Define the directory where QR code images will be saved.
    $qrDir = '../qrcodes/';  // relative path: move up one folder from backend
    if (!is_dir($qrDir)) {
        mkdir($qrDir, 0755, true);
    }
    $qrFile = $qrDir . $unique_code . '.png';
    
    // Include the PHP QR Code library.
    // Ensure that the phpqrcode folder is placed at the root of your project (lostfound/phpqrcode)
    require_once('../phpqrcode/qrlib.php');
    
    // Generate and save the QR code image.
    // Parameters:
    //   - Data to encode ($unique_code)
    //   - File path to save the PNG image ($qrFile)
    //   - Error correction level (QR_ECLEVEL_L is low, adjust if needed)
    //   - Size (adjust the scale if necessary)
    QRcode::png($unique_code, $qrFile, QR_ECLEVEL_L, 4);
    
    // --- Update the database record with the QR code image path ---
    $sqlUpdate = "UPDATE LostItemReport SET qr_code = '$qrFile' WHERE lost_report_id = $lost_report_id";
    if ($conn->query($sqlUpdate) === TRUE) {
        echo "Lost item submitted successfully.<br>";
        echo "QR Code generated at: <strong>" . $qrFile . "</strong>";
        echo "<br><img src='../" . $qrFile . "' alt='QR Code'>";
    } else {
        echo "Error updating record with QR code: " . $conn->error;
    }
} else {
    echo "Error inserting record: " . $conn->error;
}

// Close the database connection
$conn->close();
?>
