<?php
$page_title = "View Dengue Case";
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireLogin();

// Get case ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "Invalid case ID.";
    $_SESSION['message_type'] = "danger";
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

// Get case details
$sql = "SELECT * FROM dengue_cases WHERE id = $id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['message'] = "Case not found.";
    $_SESSION['message_type'] = "danger";
    header("Location: index.php");
    exit;
}

$case = mysqli_fetch_assoc($result);

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1 class="h3 mb-0 text-gray-800">View Dengue Case</h1>
        <p class="text-muted">Case ID: DEN-<?php echo str_pad($case['id'], 4, '0', STR_PAD_LEFT); ?></p>
    </div>
    <div class="col-md-6 text-md-right">
        <a href="<?php echo $site_url; ?>/dengue/index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
        <a href="<?php echo $site_url; ?>/dengue/edit.php?id=<?php echo $case['id']; ?>" class="btn btn-primary">
            <i class="fas fa-edit mr-1"></i> Edit Case
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Case Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">Patient Name:</div>
                    <div class="col-md-8"><?php echo $case['patient_name']; ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">Age:</div>
                    <div class="col-md-8"><?php echo $case['patient_age']; ?> years old</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">Gender:</div>
                    <div class="col-md-8"><?php echo ucfirst($case['gender']); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">Date Reported:</div>
                    <div class="col-md-8"><?php echo formatDate($case['reported_date']); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">Barangay:</div>
                    <div class="col-md-8"><?php echo $case['barangay']; ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">Address:</div>
                    <div class="col-md-8"><?php echo $case['address']; ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">Case Status:</div>
                    <div class="col-md-8"><?php echo getStatusBadge($case['case_status'], 'dengue'); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">Platelet Count:</div>
                    <div class="col-md-8"><?php echo !empty($case['platelet_count']) ? $case['platelet_count'] : 'Not available'; ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">Date Added:</div>
                    <div class="col-md-8"><?php echo date('F d, Y h:i A', strtotime($case['created_at'])); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">Additional Notes:</div>
                    <div class="col-md-8"><?php echo !empty($case['notes']) ? nl2br($case['notes']) : 'No additional notes'; ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Symptoms</h6>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        High Fever
                        <?php if ($case['fever']): ?>
                            <span class="badge badge-success"><i class="fas fa-check"></i></span>
                        <?php else: ?>
                            <span class="badge badge-secondary"><i class="fas fa-times"></i></span>
                        <?php endif; ?>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Severe Headache
                        <?php if ($case['headache']): ?>
                            <span class="badge badge-success"><i class="fas fa-check"></i></span>
                        <?php else: ?>
                            <span class="badge badge-secondary"><i class="fas fa-times"></i></span>
                        <?php endif; ?>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Eye/Muscle/Joint Pain
                        <?php if ($case['pain']): ?>
                            <span class="badge badge-success"><i class="fas fa-check"></i></span>
                        <?php else: ?>
                            <span class="badge badge-secondary"><i class="fas fa-times"></i></span>
                        <?php endif; ?>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Rash
                        <?php if ($case['rash']): ?>
                            <span class="badge badge-success"><i class="fas fa-check"></i></span>
                        <?php else: ?>
                            <span class="badge badge-secondary"><i class="fas fa-times"></i></span>
                        <?php endif; ?>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Mild Bleeding
                        <?php if ($case['bleeding']): ?>
                            <span class="badge badge-success"><i class="fas fa-check"></i></span>
                        <?php else: ?>
                            <span class="badge badge-secondary"><i class="fas fa-times"></i></span>
                        <?php endif; ?>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Nausea/Vomiting
                        <?php if ($case['nausea']): ?>
                            <span class="badge badge-success"><i class="fas fa-check"></i></span>
                        <?php else: ?>
                            <span class="badge badge-secondary"><i class="fas fa-times"></i></span>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
            </div>
            <div class="card-body">
                <a href="<?php echo $site_url; ?>/dengue/edit.php?id=<?php echo $case['id']; ?>" class="btn btn-primary btn-block mb-2">
                    <i class="fas fa-edit mr-1"></i> Edit Case
                </a>
                <a href="#" onclick="window.print()" class="btn btn-info btn-block mb-2">
                    <i class="fas fa-print mr-1"></i> Print Case Details
                </a>
                <a href="<?php echo $site_url; ?>/dengue/delete.php?id=<?php echo $case['id']; ?>" class="btn btn-danger btn-block delete-confirm">
                    <i class="fas fa-trash mr-1"></i> Delete Case
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>