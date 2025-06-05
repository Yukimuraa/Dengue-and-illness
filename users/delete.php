<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireLogin();

// Check if user has permission to access this page
if (!isAdmin() && !isSuperAdmin()) {
    $_SESSION['message'] = "You don't have permission to access this page.";
    $_SESSION['message_type'] = "danger";
    header("Location: " . $site_url . "/dashboard.php");
    exit;
}

// Get user ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "Invalid user ID.";
    $_SESSION['message_type'] = "danger";
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

// Prevent deleting your own account
if ($id == $_SESSION['user_id']) {
    $_SESSION['message'] = "You cannot delete your own account.";
    $_SESSION['message_type'] = "danger";
    header("Location: index.php");
    exit;
}

// Get user details
$sql = "SELECT * FROM users WHERE id = $id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['message'] = "User not found.";
    $_SESSION['message_type'] = "danger";
    header("Location: index.php");
    exit;
}

$user = mysqli_fetch_assoc($result);

// Check permissions - only super admin can delete other super admins or admins
if (($user['role'] == 'super_admin' || $user['role'] == 'admin') && !isSuperAdmin()) {
    $_SESSION['message'] = "You don't have permission to delete this user.";
    $_SESSION['message_type'] = "danger";
    header("Location: index.php");
    exit;
}

// Delete user
$sql = "DELETE FROM users WHERE id = $id";
if (mysqli_query($conn, $sql)) {
    $_SESSION['message'] = "User deleted successfully.";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "Error deleting user: " . mysqli_error($conn);
    $_SESSION['message_type'] = "danger";
}

header("Location: index.php");
exit;
?>