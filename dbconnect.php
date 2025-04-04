<?php
$host = 'localhost';
$user = 'root'; // Default user in XAMPP
$pass = ''; // Leave blank unless a password is set
$dbname = 'lost_and_found';

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

