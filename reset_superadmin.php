<?php
// reset_superadmin.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$superadmin_password = password_hash('superadmin123', PASSWORD_DEFAULT);

$sql = "UPDATE users SET password = '$superadmin_password' WHERE username = 'superadmin'";
if (mysqli_query($conn, $sql)) {
    echo "Super admin password reset successfully. New password: superadmin123";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>