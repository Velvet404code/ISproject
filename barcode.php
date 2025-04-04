<?php
// backend/process_lost_id.php

// Include Composer autoload (adjust the path if necessary)
require_once '../php-barcode-generator-main/src/BarcodeGenerator.php';
require_once '../php-barcode-generator-main/src/BarcodeGeneratorPNG.php';


use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGenerator;

// Database connection settings
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "lost_and_found";  

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve POST data
$studentStaffID = trim($_POST['student_staff_id']);
$description    = trim($_POST['description']);
$dateFound      = $_POST['date_found'];

// Validate required fields
if (empty($studentStaffID) || empty($dateFound)) {
    die("Student/Staff ID and Date Found are required.");
}

// Generate barcode image using Picqer Barcode Generator
$generator   = new BarcodeGeneratorPNG();
$barcodeData = $generator->getBarcode($studentStaffID, $generator::TYPE_CODE_128);

// Ensure the barcodes folder exists (adjust path as needed)
$barcodeFolder = '../barcodes/';
if (!is_dir($barcodeFolder)) {
    mkdir($barcodeFolder, 0777, true);
}      

// Create a unique filename for the barcode image
$barcodeFileName = $studentStaffID . '_' . time() . '.png';
$barcodeFilePath = $barcodeFolder . $barcodeFileName;

// Save the barcode image
if (file_put_contents($barcodeFilePath, $barcodeData) === false) {
    die("Error saving the barcode image.");
}

// Insert data into the lost_ids table
$stmt = $conn->prepare("INSERT INTO lost_ids (student_staff_id, description, date_found, barcode_path) VALUES (?, ?, ?, ?)");
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("ssss", $studentStaffID, $description, $dateFound, $barcodeFileName);

if ($stmt->execute()) {
    echo "Lost ID reported successfully.<br>";
    echo "Generated Barcode:<br>";
    echo "<img src='../barcodes/" . $barcodeFileName . "' alt='Barcode'>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
