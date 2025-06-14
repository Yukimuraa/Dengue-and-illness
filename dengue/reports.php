<?php
$page_title = "Dengue Reports & Analytics";
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireLogin();

// Debug database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Check if table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'dengue_cases'");
if (mysqli_num_rows($table_check) == 0) {
    die("Error: dengue_cases table does not exist in the database");
}

// Check if there's any data in the table
$data_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM dengue_cases");
$row = mysqli_fetch_assoc($data_check);
if ($row['count'] == 0) {
    echo "<div class='alert alert-warning'>No dengue cases have been recorded yet. Please add some cases first.</div>";
}

// Get the earliest and latest dates in the database
$date_range = mysqli_query($conn, "SELECT MIN(reported_date) as earliest, MAX(reported_date) as latest FROM dengue_cases");
$date_info = mysqli_fetch_assoc($date_range);

// Set default date range to current year if no data exists
if (!$date_info['earliest']) {
    $start_date = date('Y-01-01'); // First day of current year
    $end_date = date('Y-m-d'); // Today
} else {
    $start_date = $date_info['earliest'];
    $end_date = $date_info['latest'];
}

// Get date range from request if provided
if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $start_date = clean($_GET['start_date']);
}
if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $end_date = clean($_GET['end_date']);
}

// Get barangay filter
$barangay_filter = isset($_GET['barangay']) ? clean($_GET['barangay']) : '';

// Build WHERE clause
$where = "reported_date BETWEEN ? AND ?";
$params = [$start_date, $end_date];
$types = "ss";

if (!empty($barangay_filter)) {
    $where .= " AND barangay = ?";
    $params[] = $barangay_filter;
    $types .= "s";
}

// Get total cases in date range
$sql = "SELECT COUNT(*) as total FROM dengue_cases WHERE $where";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die("Database error occurred");
}

mysqli_stmt_bind_param($stmt, $types, ...$params);
if (!mysqli_stmt_execute($stmt)) {
    die("Database error occurred");
}

$result = mysqli_stmt_get_result($stmt);
$total_cases = mysqli_fetch_assoc($result)['total'] ?? 0;
mysqli_stmt_close($stmt);

// Get cases by status
$sql = "SELECT case_status, COUNT(*) as count 
        FROM dengue_cases 
        WHERE $where 
        GROUP BY case_status";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die("Database error occurred");
}

mysqli_stmt_bind_param($stmt, $types, ...$params);
if (!mysqli_stmt_execute($stmt)) {
    die("Database error occurred");
}

$result = mysqli_stmt_get_result($stmt);
$status_counts = [];
while ($row = mysqli_fetch_assoc($result)) {
    $status_counts[$row['case_status']] = $row['count'];
}
mysqli_stmt_close($stmt);

// Get cases by barangay
$sql = "SELECT barangay, COUNT(*) as count 
        FROM dengue_cases 
        WHERE $where 
        GROUP BY barangay 
        ORDER BY count DESC";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die("Database error occurred");
}

mysqli_stmt_bind_param($stmt, $types, ...$params);
if (!mysqli_stmt_execute($stmt)) {
    die("Database error occurred");
}

$result = mysqli_stmt_get_result($stmt);
$barangay_counts = [];
while ($row = mysqli_fetch_assoc($result)) {
    $barangay_counts[$row['barangay']] = $row['count'];
}
mysqli_stmt_close($stmt);

// Get monthly trend
$sql = "SELECT DATE_FORMAT(reported_date, '%Y-%m') as month, COUNT(*) as count 
        FROM dengue_cases 
        WHERE $where 
        GROUP BY month 
        ORDER BY month";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die("Database error occurred");
}

mysqli_stmt_bind_param($stmt, $types, ...$params);
if (!mysqli_stmt_execute($stmt)) {
    die("Database error occurred");
}

$result = mysqli_stmt_get_result($stmt);
$monthly_trend = [];
while ($row = mysqli_fetch_assoc($result)) {
    $monthly_trend[$row['month']] = $row['count'];
}
mysqli_stmt_close($stmt);

// Get age distribution
$sql = "SELECT 
            CASE 
                WHEN patient_age < 5 THEN 'Under 5'
                WHEN patient_age BETWEEN 5 AND 14 THEN '5-14'
                WHEN patient_age BETWEEN 15 AND 24 THEN '15-24'
                WHEN patient_age BETWEEN 25 AND 34 THEN '25-34'
                WHEN patient_age BETWEEN 35 AND 44 THEN '35-44'
                WHEN patient_age BETWEEN 45 AND 54 THEN '45-54'
                WHEN patient_age BETWEEN 55 AND 64 THEN '55-64'
                ELSE '65 and above'
            END as age_group,
            COUNT(*) as count
        FROM dengue_cases 
        WHERE $where 
        GROUP BY age_group
        ORDER BY FIELD(age_group, 'Under 5', '5-14', '15-24', '25-34', '35-44', '45-54', '55-64', '65 and above')";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die("Database error occurred");
}

mysqli_stmt_bind_param($stmt, $types, ...$params);
if (!mysqli_stmt_execute($stmt)) {
    die("Database error occurred");
}

$result = mysqli_stmt_get_result($stmt);
$age_distribution = [];
while ($row = mysqli_fetch_assoc($result)) {
    $age_distribution[$row['age_group']] = $row['count'];
}
mysqli_stmt_close($stmt);

// Get gender distribution
$sql = "SELECT gender, COUNT(*) as count 
        FROM dengue_cases 
        WHERE $where 
        GROUP BY gender";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die("Database error occurred");
}

mysqli_stmt_bind_param($stmt, $types, ...$params);
if (!mysqli_stmt_execute($stmt)) {
    die("Database error occurred");
}

$result = mysqli_stmt_get_result($stmt);
$gender_distribution = [];
while ($row = mysqli_fetch_assoc($result)) {
    $gender_distribution[$row['gender']] = $row['count'];
}
mysqli_stmt_close($stmt);

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1 class="h3 mb-0 text-gray-800">Dengue Reports & Analytics</h1>
        <p class="text-muted">Comprehensive analysis of dengue cases</p>
    </div>
    <div class="col-md-6 text-md-right">
        <button onclick="window.print()" class="btn btn-secondary">
            <i class="fas fa-print mr-1"></i> Print Report
        </button>
        <a href="export_report.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&barangay=<?php echo $barangay_filter; ?>" 
           class="btn btn-success">
            <i class="fas fa-file-excel mr-1"></i> Export to Excel
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Report Filters</h6>
    </div>
    <div class="card-body">
        <form method="get" action="" class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                           value="<?php echo $start_date; ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" 
                           value="<?php echo $end_date; ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="barangay">Barangay</label>
                    <select class="form-control" id="barangay" name="barangay">
                        <option value="">All Barangays</option>
                        <?php foreach (getBarangays() as $barangay): ?>
                            <option value="<?php echo $barangay; ?>" <?php echo $barangay_filter === $barangay ? 'selected' : ''; ?>>
                                <?php echo $barangay; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Cases</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_cases; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Confirmed Cases</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $status_counts['confirmed'] ?? 0; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Suspected Cases</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $status_counts['suspected'] ?? 0; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Recovered Cases</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $status_counts['recovered'] ?? 0; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-heart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row">
    <!-- Monthly Trend -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Monthly Trend</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Distribution -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Case Status Distribution</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4">
                    <canvas id="statusDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Barangay Distribution -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Cases by Barangay</h6>
            </div>
            <div class="card-body">
                <div class="chart-bar">
                    <canvas id="barangayDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Age Distribution -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Age Distribution</h6>
            </div>
            <div class="card-body">
                <div class="chart-bar">
                    <canvas id="ageDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Monthly Trend Chart
const monthlyTrendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
new Chart(monthlyTrendCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_keys($monthly_trend)); ?>,
        datasets: [{
            label: 'Number of Cases',
            data: <?php echo json_encode(array_values($monthly_trend)); ?>,
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Status Distribution Chart
const statusDistributionCtx = document.getElementById('statusDistributionChart').getContext('2d');
new Chart(statusDistributionCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_keys($status_counts)); ?>,
        datasets: [{
            data: <?php echo json_encode(array_values($status_counts)); ?>,
            backgroundColor: [
                'rgb(255, 99, 132)',
                'rgb(54, 162, 235)',
                'rgb(255, 205, 86)',
                'rgb(75, 192, 192)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Barangay Distribution Chart
const barangayDistributionCtx = document.getElementById('barangayDistributionChart').getContext('2d');
new Chart(barangayDistributionCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_keys($barangay_counts)); ?>,
        datasets: [{
            label: 'Number of Cases',
            data: <?php echo json_encode(array_values($barangay_counts)); ?>,
            backgroundColor: 'rgb(75, 192, 192)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Age Distribution Chart
const ageDistributionCtx = document.getElementById('ageDistributionChart').getContext('2d');
new Chart(ageDistributionCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_keys($age_distribution)); ?>,
        datasets: [{
            label: 'Number of Cases',
            data: <?php echo json_encode(array_values($age_distribution)); ?>,
            backgroundColor: 'rgb(153, 102, 255)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>
