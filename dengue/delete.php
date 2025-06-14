<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireLogin();

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setFlashMessage('error', 'Invalid dengue case ID');
    redirect('index.php');
}

$id = (int)$_GET['id'];

// Delete the record
$sql = "DELETE FROM dengue_cases WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);

if (mysqli_stmt_execute($stmt)) {
    setFlashMessage('success', 'Dengue case deleted successfully');
} else {
    setFlashMessage('error', 'Error deleting dengue case: ' . mysqli_error($conn));
}

redirect('index.php'); 