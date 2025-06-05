<?php
$page_title = "Dashboard";
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/dashboard_functions.php';
requireLogin();

// Get statistics for dashboard
$total_dengue = getTotalDengueCases();
$total_illness = getTotalIllnessCases();
$dengue_by_barangay = getDengueCasesByBarangay();
$illness_by_type = getIllnessCasesByType();
$monthly_dengue = getMonthlyDengueCases();

// Prepare data for charts
$barangay_labels = [];
$barangay_values = [];
foreach ($dengue_by_barangay as $item) {
    $barangay_labels[] = $item['barangay'];
    $barangay_values[] = $item['count'];
}

$illness_labels = [];
$illness_values = [];
foreach ($illness_by_type as $item) {
    $illness_labels[] = ucfirst($item['illness_type']);
    $illness_values[] = $item['count'];
}

// Get recent cases
$sql_recent_dengue = "SELECT * FROM dengue_cases ORDER BY reported_date DESC LIMIT 5";
$result_recent_dengue = mysqli_query($conn, $sql_recent_dengue);

$sql_recent_illness = "SELECT * FROM illness_cases ORDER BY reported_date DESC LIMIT 5";
$result_recent_illness = mysqli_query($conn, $sql_recent_illness);

include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1 class="h3 mb-0 text-gray-800 dashboard-header">Dashboard</h1>
        <p class="text-muted">Overview of health statistics in Talisay City</p>
    </div>
    <div class="col-md-6 text-md-right">
        <p class="mb-0">
            <i class="fas fa-calendar-day mr-1"></i>
            <?php echo date('F d, Y'); ?>
        </p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2 stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Total Dengue Cases</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_dengue; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-mosquito fa-2x text-gray-300 stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2 stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Active Outbreaks</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">3</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300 stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2 stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Illness Cases</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_illness; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-virus fa-2x text-gray-300 stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2 stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Health Workers Active</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">42</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-md fa-2x text-gray-300 stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Monthly Dengue Cases (<?php echo date('Y'); ?>)</h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="monthlyDengueChart" data-values='<?php echo json_encode(array_values($monthly_dengue)); ?>'></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Illness Distribution</h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="illnessTypeChart" 
                            data-labels='<?php echo json_encode($illness_labels); ?>' 
                            data-values='<?php echo json_encode($illness_values); ?>'></canvas>
                </div>
            </div>
        </div>
    </div>
</div>  

<!-- Barangay Distribution Chart -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Dengue Cases by Barangay</h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="barangayDistributionChart" 
                            data-labels='<?php echo json_encode($barangay_labels); ?>' 
                            data-values='<?php echo json_encode($barangay_values); ?>'></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Cases Row -->
<div class="row">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Dengue Cases</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Barangay</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result_recent_dengue)): ?>
                            <tr>
                                <td><?php echo $row['patient_name']; ?>, <?php echo $row['patient_age']; ?></td>
                                <td><?php echo $row['barangay']; ?></td>
                                <td><?php echo getStatusBadge($row['case_status'], 'dengue'); ?></td>
                                <td><?php echo formatDate($row['reported_date']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if (mysqli_num_rows($result_recent_dengue) == 0): ?>
                            <tr>
                                <td colspan="4" class="text-center">No recent dengue cases</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="<?php echo $site_url; ?>/dengue/index.php" class="btn btn-sm btn-primary">
                        View All Dengue Cases
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Illness Cases</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Illness</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result_recent_illness)): ?>
                            <tr>
                                <td><?php echo $row['patient_name']; ?>, <?php echo $row['patient_age']; ?></td>
                                <td><?php echo ucfirst($row['illness_type']); ?></td>
                                <td><?php echo getStatusBadge($row['case_status'], 'illness'); ?></td>
                                <td><?php echo formatDate($row['reported_date']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if (mysqli_num_rows($result_recent_illness) == 0): ?>
                            <tr>
                                <td colspan="4" class="text-center">No recent illness cases</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="<?php echo $site_url; ?>/illness/index.php" class="btn btn-sm btn-primary">
                        View All Illness Cases
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>