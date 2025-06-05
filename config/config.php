<?php
// Database configuration
$db_host = "localhost";
$db_user = "root";  // Default XAMPP username
$db_pass = "";      // Default XAMPP password
$db_name = "barangay_health_system";  // Your database name

// In config/config.php
// Change this:
$site_name = "Dengue and Illness Tracking System";
$site_url = "http://localhost/Dengue_and_illness_system"; // Remove trailing slash

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8");

// Define base URL
$base_url = "http://localhost/Dengue_and_illness_system/";

// Define site name
$site_name = "Dengue and Illness Tracking System";

// Define timezone
date_default_timezone_set('Asia/Manila');
?>