<?php
// add_test_users.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Only run this script once
$check_sql = "SELECT COUNT(*) as count FROM users WHERE username = 'admin'";
$check_result = mysqli_query($conn, $check_sql);
$row = mysqli_fetch_assoc($check_result);

if ($row['count'] > 0) {
    echo "Test users already exist. Script will not run again.";
    exit;
}

// Add Admin user
$admin_username = 'admin';
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$admin_fullname = 'System Administrator';
$admin_email = 'admin@talisay.gov.ph';
$admin_role = 'admin';

$sql = "INSERT INTO users (username, password, full_name, email, role) 
        VALUES ('$admin_username', '$admin_password', '$admin_fullname', '$admin_email', '$admin_role')";
mysqli_query($conn, $sql);

// Add Health Officer
$officer_username = 'officer';
$officer_password = password_hash('officer123', PASSWORD_DEFAULT);
$officer_fullname = 'Health Officer';
$officer_email = 'officer@talisay.gov.ph';
$officer_role = 'health_officer';
$officer_barangay = 'San Fernando';

$sql = "INSERT INTO users (username, password, full_name, email, role, barangay) 
        VALUES ('$officer_username', '$officer_password', '$officer_fullname', '$officer_email', '$officer_role', '$officer_barangay')";
mysqli_query($conn, $sql);

// Add Health Worker
$worker_username = 'worker';
$worker_password = password_hash('worker123', PASSWORD_DEFAULT);
$worker_fullname = 'Health Worker';
$worker_email = 'worker@talisay.gov.ph';
$worker_role = 'health_worker';
$worker_barangay = 'Matab-ang';

$sql = "INSERT INTO users (username, password, full_name, email, role, barangay) 
        VALUES ('$worker_username', '$worker_password', '$worker_fullname', '$worker_email', '$worker_role', '$worker_barangay')";
mysqli_query($conn, $sql);

echo "Test users created successfully:<br>";
echo "1. Admin: username = admin, password = admin123<br>";
echo "2. Health Officer: username = officer, password = officer123<br>";
echo "3. Health Worker: username = worker, password = worker123<br>";
?>