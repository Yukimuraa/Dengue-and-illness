<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($username) || empty($password)) {
        $_SESSION['message'] = "Please enter both username and password.";
        $_SESSION['message_type'] = "danger";
    } else {
        // Check if user exists
        $sql = "SELECT * FROM users WHERE username = '$username'";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit;
            } else {
                $_SESSION['message'] = "Invalid password.";
                $_SESSION['message_type'] = "danger";
            }
        } else {
            $_SESSION['message'] = "User not found.";
            $_SESSION['message_type'] = "danger";
        }
    }
}

$page_title = "Login";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo $site_name; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $site_url; ?>/assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-body">
                    <div class="login-logo">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <h2 class="login-title"><?php echo $site_name; ?></h2>
                    <p class="text-center text-muted mb-4">Sign in to your account</p>
                    
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show">
                            <?php echo $_SESSION['message']; ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php 
                            unset($_SESSION['message']);
                            unset($_SESSION['message_type']);
                        ?>
                    <?php endif; ?>
                    
                    <form method="post" action="">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                </div>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                </div>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                                <div class="input-group-append">
                                    <span class="input-group-text toggle-password" style="cursor: pointer; background: black; border-left: none;">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="remember-me">
                                <label class="custom-control-label" for="remember-me">Remember me</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-sign-in-alt mr-2"></i> Login
                        </button>
                    </form>
                </div>
            </div>
            <div class="text-center mt-4">
                <p class="text-muted">
                    <i class="fas fa-hospital mr-1"></i> Talisay City Health Office
                </p>
                <p class="text-muted small">
                    Dengue Information Tracking System with Data Analytics (DITS) <br>
                    & Barangay Illness Analytics System
                </p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Toggle password visibility
        $('.toggle-password').click(function() {
            const passwordField = $('#password');
            const passwordFieldType = passwordField.attr('type');
            const eyeIcon = $(this).find('i');
            
            if (passwordFieldType === 'password') {
                passwordField.attr('type', 'text');
                eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordField.attr('type', 'password');
                eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
        
        // Add animation to alert messages
        $('.alert').addClass('animated fadeInDown');
        
        // Focus on username field when page loads
        $('#username').focus();
    });
    </script>
</body>
</html>