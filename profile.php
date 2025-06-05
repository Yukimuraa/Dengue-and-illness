<?php
$page_title = "My Profile";
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clean and validate input
    $full_name = clean($_POST['full_name']);
    $email = clean($_POST['email']);
    
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
                email = '$email'
                $password_update
                WHERE id = $user_id";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['message'] = "Profile updated successfully.";
            $_SESSION['message_type'] = "success";
            $_SESSION['full_name'] = $full_name;
            
            // Refresh user data
            $user = getUserById($user_id);
        } else {
            $_SESSION['message'] = "Error: " . mysqli_error($conn);
            $_SESSION['message_type'] = "danger";
        }
    }
}

include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1 class="h3 mb-0 text-gray-800">My Profile</h1>
        <p class="text-muted">Manage your account information</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Profile Information</h6>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" value="<?php echo $user['username']; ?>" readonly>
                        <small class="form-text text-muted">Username cannot be changed.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo $user['full_name']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role</label>
                        <input type="text" class="form-control" id="role" value="<?php echo ucwords(str_replace('_', ' ', $user['role'])); ?>" readonly>
                    </div>
                    
                    <?php if (!empty($user['barangay'])): ?>
                    <div class="form-group">
                        <label for="barangay">Barangay</label>
                        <input type="text" class="form-control" id="barangay" value="<?php echo $user['barangay']; ?>" readonly>
                    </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <h5 class="mb-3">Change Password</h5>
                    
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <small class="form-text text-muted">Leave blank to keep current password.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Update Profile
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Account Information</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="fas fa-user-circle fa-5x text-gray-300 mb-3"></i>
                    <h5><?php echo $user['full_name']; ?></h5>
                    <p class="badge badge-primary"><?php echo ucwords(str_replace('_', ' ', $user['role'])); ?></p>
                </div>
                
                <div class="mb-3">
                    <strong>Username:</strong> <?php echo $user['username']; ?>
                </div>
                
                <div class="mb-3">
                    <strong>Email:</strong> <?php echo $user['email']; ?>
                </div>
                
                <?php if (!empty($user['barangay'])): ?>
                <div class="mb-3">
                    <strong>Barangay:</strong> <?php echo $user['barangay']; ?>
                </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <strong>Account Created:</strong> <?php echo date('F d, Y', strtotime($user['created_at'])); ?>
                </div>
                
                <div class="text-center mt-4">
                    <a href="<?php echo $site_url; ?>/logout.php" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>