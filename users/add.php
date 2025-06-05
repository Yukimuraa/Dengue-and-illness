<?php
$page_title = "Add User";
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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clean and validate input
    $username = clean($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = clean($_POST['full_name']);
    $email = clean($_POST['email']);
    $role = clean($_POST['role']);
    $barangay = isset($_POST['barangay']) ? clean($_POST['barangay']) : '';
    
    // Validate required fields
    if (empty($username) || empty($password) || empty($confirm_password) || empty($full_name) || empty($email) || empty($role)) {
        $_SESSION['message'] = "Please fill in all required fields.";
        $_SESSION['message_type'] = "danger";
    } 
    // Check if passwords match
    elseif ($password !== $confirm_password) {
        $_SESSION['message'] = "Passwords do not match.";
        $_SESSION['message_type'] = "danger";
    }
    // Check if username already exists
    else {
        $check_sql = "SELECT * FROM users WHERE username = '$username'";
        $check_result = mysqli_query($conn, $check_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            $_SESSION['message'] = "Username already exists. Please choose a different username.";
            $_SESSION['message_type'] = "danger";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert into database
            $sql = "INSERT INTO users (username, password, full_name, email, role, barangay) 
                    VALUES ('$username', '$hashed_password', '$full_name', '$email', '$role', '$barangay')";
            
            if (mysqli_query($conn, $sql)) {
                $_SESSION['message'] = "User added successfully.";
                $_SESSION['message_type'] = "success";
                header("Location: index.php");
                exit;
            } else {
                $_SESSION['message'] = "Error: " . mysqli_error($conn);
                $_SESSION['message_type'] = "danger";
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1 class="h3 mb-0 text-gray-800">Add New User</h1>
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
                        <label for="username">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="full_name">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="role">Role <span class="text-danger">*</span></label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="">Select Role</option>
                            <?php if (isSuperAdmin()): ?>
                            <option value="super_admin">Super Admin</option>
                            <option value="admin">Admin</option>
                            <?php elseif (isAdmin()): ?>
                            <option value="health_officer">Health Officer</option>
                            <?php endif; ?>
                            <option value="health_worker">Health Worker</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="barangay">Barangay (for Health Officers and Workers)</label>
                <select class="form-control" id="barangay" name="barangay">
                    <option value="">Select Barangay</option>
                    <?php foreach (getBarangays() as $barangay): ?>
                        <option value="<?php echo $barangay; ?>"><?php echo $barangay; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Save User
            </button>
        </form>
    </div>
</div>

<script>
    // Show/hide barangay field based on role selection
    document.getElementById('role').addEventListener('change', function() {
        var barangayField = document.getElementById('barangay').parentNode;
        if (this.value === 'health_officer' || this.value === 'health_worker') {
            barangayField.style.display = 'block';
        } else {
            barangayField.style.display = 'none';
            document.getElementById('barangay').value = '';
        }
    });
</script>

<?php include '../includes/footer.php'; ?>