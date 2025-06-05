<?php
$page_title = "User Management";
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

// Handle search and filter
$where = "1=1";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = clean($_GET['search']);
    $where .= " AND (username LIKE '%$search%' OR full_name LIKE '%$search%' OR email LIKE '%$search%')";
}

if (isset($_GET['role']) && !empty($_GET['role'])) {
    $role_filter = clean($_GET['role']);
    $where .= " AND role = '$role_filter'";
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Get total records
$total_query = "SELECT COUNT(*) as total FROM users WHERE $where";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get records for current page
$sql = "SELECT * FROM users WHERE $where ORDER BY id DESC LIMIT $offset, $records_per_page";
$result = mysqli_query($conn, $sql);

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1 class="h3 mb-0 text-gray-800">User Management</h1>
        <p class="text-muted">Manage system users and their access levels</p>
    </div>
    <div class="col-md-6 text-md-right">
        <a href="<?php echo $site_url; ?>/users/add.php" class="btn btn-primary">
            <i class="fas fa-user-plus mr-1"></i> Add New User
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Search & Filters</h6>
    </div>
    <div class="card-body">
        <form method="get" action="">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="search">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Search by username, name or email" 
                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select class="form-control" id="role" name="role">
                            <option value="">All Roles</option>
                            <?php if (isSuperAdmin()): ?>
                            <option value="super_admin" <?php echo (isset($_GET['role']) && $_GET['role'] == 'super_admin') ? 'selected' : ''; ?>>Super Admin</option>
                            <?php endif; ?>
                            <option value="admin" <?php echo (isset($_GET['role']) && $_GET['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <option value="health_officer" <?php echo (isset($_GET['role']) && $_GET['role'] == 'health_officer') ? 'selected' : ''; ?>>Health Officer</option>
                            <option value="health_worker" <?php echo (isset($_GET['role']) && $_GET['role'] == 'health_worker') ? 'selected' : ''; ?>>Health Worker</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-group mb-0 w-100">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search mr-1"></i> Filter
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">System Users</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Barangay</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['username']; ?></td>
                                <td><?php echo $row['full_name']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td>
                                    <?php
                                    $badge_class = '';
                                    switch ($row['role']) {
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
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo ucwords(str_replace('_', ' ', $row['role'])); ?></span>
                                </td>
                                <td><?php echo !empty($row['barangay']) ? $row['barangay'] : 'N/A'; ?></td>
                                <td>
                                    <a href="view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($row['id'] != $_SESSION['user_id'] && (isSuperAdmin() || (isAdmin() && $row['role'] != 'super_admin' && $row['role'] != 'admin'))): ?>
                                    <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger delete-confirm">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No users found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mt-4">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['role']) ? '&role=' . urlencode($_GET['role']) : ''; ?>">Previous</a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['role']) ? '&role=' . urlencode($_GET['role']) : ''; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['role']) ? '&role=' . urlencode($_GET['role']) : ''; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>