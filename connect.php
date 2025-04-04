<?php
$host = 'localhost';
$user = 'root';
$pass = ''; 
$dbname = 'lost_and_found';

// Create connection
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected to MySQL successfully!";

// Check if database exists
$db_selected = mysqli_select_db($conn, $dbname);
if (!$db_selected) {
    die("Database does not exist: " . mysqli_error($conn));
} else {
    echo "Database exists!";
}

$conn->close();
?>
