<?php
$page_title = "Edit User";
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

// Check permissions - only super admin can edit other super admins
if ($user['role'] == 'super_admin' && !isSuperAdmin()) {
    $_SESSION['message'] = "You don't have permission to edit super admin users.";
    $_SESSION['message_type'] = "danger";
    header("Location: index.php");
    exit;
}

// Check permissions - admins can only edit health officers and workers
if (isAdmin() && !isSuperAdmin() && $user['role'] == 'admin') {
    $_SESSION['message'] = "You don't have permission to edit admin users.";
    $_SESSION['message_type'] = "danger";
    header("Location: index.php");
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clean and validate input
    $full_name = clean($_POST['full_name']);
    $email = clean($_POST['email']);
    $role = clean($_POST['role']);
    $barangay = isset($_POST['barangay']) ? clean($_POST['barangay']) : '';
    
    // Check if password should be updated
    $password_update = "";
    if (!empty($_POST['password']) && !empty($_POST['confirm_password'])) {
        if ($_POST['password'] === $_POST['confirm_password']) {
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $password_update = ", password = '$hashed_password'";
        } else {
            $_SESSION['message'] = "Passwords do not match.";
            $_SESSION['message_type'] = "danger";
        }
    }
    
    // Update user
    if (!isset($_SESSION['message'])) {
        $sql = "UPDATE users SET 
                full_name = '$full_name', 
                email = '$email', 
                role = '$role', 
                barangay = '$barangay'
                $password_update
                WHERE id = $id";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['message'] = "User updated successfully.";
            $_SESSION['message_type'] = "success";
            
            // Update session if user is updating their own account
            if ($id == $_SESSION['user_id']) {
                $_SESSION['full_name'] = $full_name;
                $_SESSION['user_role'] = $role;
            }
            
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['message'] = "Error: " . mysqli_error($conn);
            $_SESSION['message_type'] = "danger";
        }
    }
}

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1 class="h3 mb-0 text-gray-800">Edit User</h1>
        <p class="text-muted">Username: <?php echo $user['username']; ?></p>
    </div>
    <div class="col-md-6 text-md-right">
        <a href="<?php echo $site_url; ?>/users/index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to Users
        </a>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
    </div>
    <div class="card-body">
        <form method="post" action="">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" value="<?php echo $user['username']; ?>" readonly>
                        <small class="form-text text-muted">Username cannot be changed.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="full_name">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo $user['full_name']; ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <small class="form-text text-muted">Leave blank to keep current password.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="role">Role <span class="text-danger">*</span></label>
                        <select class="form-control" id="role" name="role" required>
                            <?php if (isSuperAdmin()): ?>
                            <option value="super_admin" <?php echo ($user['role'] == 'super_admin') ? 'selected' : ''; ?>>Super Admin</option>
                            <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <?php endif; ?>
                            <option value="health_officer" <?php echo ($user['role'] == 'health_officer') ? 'selected' : ''; ?>>Health Officer</option>
                            <option value="health_worker" <?php echo ($user['role'] == 'health_worker') ? 'selected' : ''; ?>>Health Worker</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-group" id="barangay-group" <?php echo ($user['role'] == 'super_admin' || $user['role'] == 'admin') ? 'style="display:none;"' : ''; ?>>
                <label for="barangay">Barangay (for Health Officers and Workers)</label>
                <select class="form-control" id="barangay" name="barangay">
                    <option value="">Select Barangay</option>
                    <?php foreach (getBarangays() as $barangay): ?>
                        <option value="<?php echo $barangay; ?>" <?php echo ($user['barangay'] == $barangay) ? 'selected' : ''; ?>><?php echo $barangay; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Update User
            </button>
        </form>
    </div>
</div>

<script>
    // Show/hide barangay field based on role selection
    document.getElementById('role').addEventListener('change', function() {
        var barangayGroup = document.getElementById('barangay-group');
        if (this.value === 'health_officer' || this.value === 'health_worker') {
            barangayGroup.style.display = 'block';
        } else {
            barangayGroup.style.display = 'none';
            document.getElementById('barangay').value = '';
        }
    });
</script>

<?php include '../includes/footer.php'; ?>