<?php
// Initialize the session and include necessary files
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';


// Check if user is logged in
// check_login();

// Set page title
$page_title = "Illness Cases";
include '../includes/header.php';

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';
$filter_barangay = isset($_GET['barangay']) ? $_GET['barangay'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Build the query
$query = "SELECT i.*, p.full_name, p.age, p.gender, b.barangay_name 
          FROM illnesses i 
          JOIN patients p ON i.patient_id = p.id 
          JOIN barangays b ON p.barangay_id = b.id 
          WHERE 1=1";

$count_query = "SELECT COUNT(*) as total FROM illnesses i 
                JOIN patients p ON i.patient_id = p.id 
                JOIN barangays b ON p.barangay_id = b.id 
                WHERE 1=1";

// Add search and filter conditions
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " AND (p.full_name LIKE '%$search%' OR i.case_id LIKE '%$search%')";
    $count_query .= " AND (p.full_name LIKE '%$search%' OR i.case_id LIKE '%$search%')";
}

if (!empty($filter_type)) {
    $filter_type = $conn->real_escape_string($filter_type);
    $query .= " AND i.illness_type = '$filter_type'";
    $count_query .= " AND i.illness_type = '$filter_type'";
}

if (!empty($filter_barangay)) {
    $filter_barangay = $conn->real_escape_string($filter_barangay);
    $query .= " AND b.id = '$filter_barangay'";
    $count_query .= " AND b.id = '$filter_barangay'";
}

if (!empty($filter_status)) {
    $filter_status = $conn->real_escape_string($filter_status);
    $query .= " AND i.status = '$filter_status'";
    $count_query .= " AND i.status = '$filter_status'";
}

// Add order and limit
$query .= " ORDER BY i.reported_date DESC LIMIT $offset, $records_per_page";

// Execute queries
$result = $conn->query($query);
$count_result = $conn->query($count_query);
$count_row = $count_result->fetch_assoc();
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get illness types for filter
$illness_types_query = "SELECT DISTINCT illness_type FROM illnesses ORDER BY illness_type";
$illness_types_result = $conn->query($illness_types_query);

// Get barangays for filter
$barangays_query = "SELECT id, barangay_name FROM barangays ORDER BY barangay_name";
$barangays_result = $conn->query($barangays_query);
?>

<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Illness Cases</h1>
    <p class="mb-4">View and manage all recorded illness cases in the system.</p>

    <!-- Filters and Search -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
            <a href="add.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus fa-sm"></i> Add New Case
            </a>
        </div>
        <div class="card-body">
            <form method="GET" action="index.php" class="row">
                <div class="col-md-3 mb-3">
                    <label for="search">Search</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Name or Case ID" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2 mb-3">
                    <label for="type">Illness Type</label>
                    <select class="form-control" id="type" name="type">
                        <option value="">All Types</option>
                        <?php while ($type = $illness_types_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($type['illness_type']); ?>" <?php if ($filter_type == $type['illness_type']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($type['illness_type']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="barangay">Barangay</label>
                    <select class="form-control" id="barangay" name="barangay">
                        <option value="">All Barangays</option>
                        <?php while ($barangay = $barangays_result->fetch_assoc()): ?>
                            <option value="<?php echo $barangay['id']; ?>" <?php if ($filter_barangay == $barangay['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($barangay['barangay_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="Active" <?php if ($filter_status == 'Active') echo 'selected'; ?>>Active</option>
                        <option value="Under Treatment" <?php if ($filter_status == 'Under Treatment') echo 'selected'; ?>>Under Treatment</option>
                        <option value="Recovered" <?php if ($filter_status == 'Recovered') echo 'selected'; ?>>Recovered</option>
                        <option value="Chronic" <?php if ($filter_status == 'Chronic') echo 'selected'; ?>>Chronic</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mr-2">Apply Filters</button>
                    <a href="index.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Cases Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Illness Cases</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Case ID</th>
                            <th>Illness Type</th>
                            <th>Patient</th>
                            <th>Barangay</th>
                            <th>Status</th>
                            <th>Reported Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['case_id']); ?></td>
                                    <td>
                                        <?php 
                                        $icon_class = '';
                                        switch ($row['illness_type']) {
                                            case 'Influenza':
                                                $icon_class = 'text-primary';
                                                break;
                                            case 'Respiratory Infection':
                                                $icon_class = 'text-success';
                                                break;
                                            case 'Diarrhea':
                                                $icon_class = 'text-warning';
                                                break;
                                            default:
                                                $icon_class = 'text-secondary';
                                        }
                                        ?>
                                        <i class="fas fa-virus mr-1 <?php echo $icon_class; ?>"></i>
                                        <?php echo htmlspecialchars($row['illness_type']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($row['full_name']); ?>
                                        <small class="d-block text-muted">
                                            <?php echo htmlspecialchars($row['age']); ?> years, 
                                            <?php echo htmlspecialchars($row['gender']); ?>
                                        </small>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['barangay_name']); ?></td>
                                    <td>
                                        <?php 
                                        $status_class = '';
                                        switch ($row['status']) {
                                            case 'Active':
                                                $status_class = 'badge-danger';
                                                break;
                                            case 'Under Treatment':
                                                $status_class = 'badge-warning';
                                                break;
                                            case 'Recovered':
                                                $status_class = 'badge-success';
                                                break;
                                            case 'Chronic':
                                                $status_class = 'badge-secondary';
                                                break;
                                            default:
                                                $status_class = 'badge-info';
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($row['reported_date'])); ?></td>
                                    <td>
                                        <a href="view.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $row['id']; ?>)" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No illness cases found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mt-4">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($filter_type); ?>&barangay=<?php echo urlencode($filter_barangay); ?>&status=<?php echo urlencode($filter_status); ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($filter_type); ?>&barangay=<?php echo urlencode($filter_barangay); ?>&status=<?php echo urlencode($filter_status); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($filter_type); ?>&barangay=<?php echo urlencode($filter_barangay); ?>&status=<?php echo urlencode($filter_status); ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
            
            <div class="mt-3 text-center">
                <p>Showing <?php echo min(($offset + 1), $total_records); ?> to <?php echo min(($offset + $records_per_page), $total_records); ?> of <?php echo $total_records; ?> entries</p>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Are you sure you want to delete this illness case? This action cannot be undone.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <a class="btn btn-danger" id="confirmDeleteBtn" href="#">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    document.getElementById('confirmDeleteBtn').href = 'delete.php?id=' + id;
    $('#deleteModal').modal('show');
}
</script>

<?php
include '../includes/footer.php';
?>