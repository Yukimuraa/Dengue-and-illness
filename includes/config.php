<?php
// Start session
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'barangay_health_system';
$username = 'root';
$password = ''; // Set your database password here

// Create database connection
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set timezone
date_default_timezone_set('Asia/Manila');

// Site settings
$site_name = "Barangay Health Analytics System";
$site_url = "http://localhost/Dengue_and_illness_system";

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}
?>