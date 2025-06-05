<?php
$page_title = "View User";
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

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1 class="h3 mb-0 text-gray-800">View User</h1>
        <p class="text-muted">Username: <?php echo $user['username']; ?></p>
    </div>
    <div class="col-md-6 text-md-right">
        <a href="<?php echo $site_url; ?>/users/index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to Users
        </a>
        <a href="<?php echo $site_url; ?>/users/edit.php?id=<?php echo $user['id']; ?>" class="btn btn-primary">
            <i class="fas fa-edit mr-1"></i> Edit User
        </a>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3 font-weight-bold">Username:</div>
            <div class="col-md-9"><?php echo $user['username']; ?></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-3 font-weight-bold">Full Name:</div>
            <div class="col-md-9"><?php echo $user['full_name']; ?></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-3 font-weight-bold">Email:</div>
            <div class="col-md-9"><?php echo $user['email']; ?></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-3 font-weight-bold">Role:</div>
            <div class="col-md-9">
                <?php
                $badge_class = '';
                switch ($user['role']) {
                    case 'super_admin':
                        $badge_class = 'badge-dark';
                        break;
                    case 'admin':
                        $badge_class = 'badge-danger';
                        break;
                    case 'health_officer':
                        $badge_class = 'badge-primary';
                        break;
                    case 'health_worker':
                        $badge_class = 'badge-success';
                        break;
                    default:
                        $badge_class = 'badge-secondary';
                }
                ?>
                <span class="badge <?php echo $badge_class; ?>"><?php echo ucwords(str_replace('_', ' ', $user['role'])); ?></span>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-3 font-weight-bold">Barangay:</div>
            <div class="col-md-9"><?php echo !empty($user['barangay']) ? $user['barangay'] : 'N/A'; ?></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-3 font-weight-bold">Date Created:</div>
            <div class="col-md-9"><?php echo date('F d, Y h:i A', strtotime($user['created_at'])); ?></div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>