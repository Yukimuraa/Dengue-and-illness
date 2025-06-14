<?php
$page_title = "Dengue Cases";
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireLogin();

// Handle search and filter
$where = "1=1";
$params = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = clean($_GET['search']);
    $where .= " AND (patient_name LIKE '%$search%' OR barangay LIKE '%$search%')";
}

if (isset($_GET['barangay']) && !empty($_GET['barangay'])) {
    $barangay_filter = clean($_GET['barangay']);
    $where .= " AND barangay = '$barangay_filter'";
}

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status_filter = clean($_GET['status']);
    $where .= " AND case_status = '$status_filter'";
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Get total records
$total_query = "SELECT COUNT(*) as total FROM dengue_cases WHERE $where";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get records for current page
$sql = "SELECT * FROM dengue_cases WHERE $where ORDER BY reported_date DESC LIMIT $offset, $records_per_page";
$result = mysqli_query($conn, $sql);

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1 class="h3 mb-0 text-gray-800">Dengue Cases</h1>
        <p class="text-muted">Manage and track dengue cases in Talisay City</p>
    </div>
    <div class="col-md-6 text-md-right">
        <a href="<?php echo $site_url; ?>/dengue/add.php" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Add New Case
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
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="search">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Search by name or barangay" 
                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="barangay">Barangay</label>
                        <select class="form-control" id="barangay" name="barangay">
                            <option value="">All Barangays</option>
                            <?php foreach (getBarangays() as $barangay): ?>
                                <option value="<?php echo $barangay; ?>" <?php echo (isset($_GET['barangay']) && $_GET['barangay'] == $barangay) ? 'selected' : ''; ?>>
                                    <?php echo $barangay; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Statuses</option>
                            <?php foreach (getDengueStatusOptions() as $value => $label): ?>
                                <option value="<?php echo $value; ?>" <?php echo (isset($_GET['status']) && $_GET['status'] == $value) ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
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

<!-- Cases Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Dengue Cases List</h6>
        <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown">
                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                <a class="dropdown-item" href="<?php echo $site_url; ?>/reports/dengue_report.php">
                    <i class="fas fa-file-pdf mr-1"></i> Generate Report
                </a>
                <a class="dropdown-item" href="#" onclick="window.print()">
                    <i class="fas fa-print mr-1"></i> Print List
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="<?php echo $site_url; ?>/dengue/index.php">
                    <i class="fas fa-sync-alt mr-1"></i> Reset Filters
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Barangay</th>
                        <th>Status</th>
                        <th>Reported Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>DEN-<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo $row['patient_name']; ?>, <?php echo $row['patient_age']; ?></td>
                                <td><?php echo $row['barangay']; ?></td>
                                <td><?php echo getStatusBadge($row['case_status'], 'dengue'); ?></td>
                                <td><?php echo formatDate($row['reported_date']); ?></td>
                                <td>
                                    <a href="view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger delete-confirm">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No dengue cases found</td>
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
                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['barangay']) ? '&barangay=' . urlencode($_GET['barangay']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?>">Previous</a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['barangay']) ? '&barangay=' . urlencode($_GET['barangay']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['barangay']) ? '&barangay=' . urlencode($_GET['barangay']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add confirmation for delete buttons
    const deleteButtons = document.querySelectorAll('.delete-confirm');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this dengue case? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
});
</script>