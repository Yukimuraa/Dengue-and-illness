<?php
// Initialize the session and include necessary files
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
// check_login();

// Set page title
$page_title = "Illness Reports";
include '../includes/header.php';

// Get filter parameters
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'summary';
$date_range = isset($_GET['date_range']) ? $_GET['date_range'] : 'month';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$barangay_id = isset($_GET['barangay_id']) ? $_GET['barangay_id'] : '';
$illness_type = isset($_GET['illness_type']) ? $_GET['illness_type'] : '';
$format = isset($_GET['format']) ? $_GET['format'] : 'html';

// Set date range based on selection
if ($date_range == 'week') {
    $start_date = date('Y-m-d', strtotime('-1 week'));
    $end_date = date('Y-m-d');
} elseif ($date_range == 'month') {
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-d');
} elseif ($date_range == 'quarter') {
    $quarter_month = ceil(date('m') / 3) * 3 - 2;
    $start_date = date('Y') . '-' . str_pad($quarter_month, 2, '0', STR_PAD_LEFT) . '-01';
    $end_date = date('Y-m-d');
} elseif ($date_range == 'year') {
    $start_date = date('Y') . '-01-01';
    $end_date = date('Y-m-d');
}

// Get barangays for filter
$barangays_query = "SELECT id, barangay_name FROM barangays ORDER BY barangay_name";
$barangays_result = $conn->query($barangays_query);

// Get illness types for filter
$illness_types_query = "SELECT DISTINCT illness_type FROM illnesses ORDER BY illness_type";
$illness_types_result = $conn->query($illness_types_query);

// Generate report based on type
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate_report'])) {
    // Process report generation
    $report_type = $_POST['report_type'];
    $date_range = $_POST['date_range'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $barangay_id = $_POST['barangay_id'];
    $illness_type = $_POST['illness_type'];
    $format = $_POST['format'];
    
    // Redirect to same page with parameters for report display
    $redirect_url = "reports.php?report_type=$report_type&date_range=$date_range&start_date=$start_date&end_date=$end_date&barangay_id=$barangay_id&illness_type=$illness_type&format=$format&generated=1";
    header("Location: $redirect_url");
    exit;
}

// Check if report should be generated
$generate_report = isset($_GET['generated']) && $_GET['generated'] == '1';

// Prepare report data if needed
if ($generate_report) {
    // Base query for all report types
    $base_query = "FROM illnesses i
                  JOIN patients p ON i.patient_id = p.id
                  JOIN barangays b ON p.barangay_id = b.id
                  WHERE i.reported_date BETWEEN ? AND ?";
    
    $params = [$start_date, $end_date];
    $param_types = "ss";
    
    if (!empty($barangay_id)) {
        $base_query .= " AND p.barangay_id = ?";
        $params[] = $barangay_id;
        $param_types .= "s";
    }
    
    if (!empty($illness_type)) {
        $base_query .= " AND i.illness_type = ?";
        $params[] = $illness_type;
        $param_types .= "s";
    }
    
    // Different queries based on report type
    if ($report_type == 'summary') {
        // Summary report
        $query = "SELECT 
                    COUNT(*) as total_cases,
                    COUNT(CASE WHEN i.status = 'Active' THEN 1 END) as active_cases,
                    COUNT(CASE WHEN i.status = 'Under Treatment' THEN 1 END) as under_treatment,
                    COUNT(CASE WHEN i.status = 'Recovered' THEN 1 END) as recovered,
                    COUNT(CASE WHEN i.status = 'Chronic' THEN 1 END) as chronic,
                    COUNT(DISTINCT p.id) as total_patients,
                    COUNT(DISTINCT b.id) as affected_barangays,
                    MIN(i.reported_date) as earliest_date,
                    MAX(i.reported_date) as latest_date
                  $base_query";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        $summary_result = $stmt->get_result()->fetch_assoc();
        
        // Get illness type breakdown
        $illness_breakdown_query = "SELECT 
                                      i.illness_type,
                                      COUNT(*) as count
                                    $base_query
                                    GROUP BY i.illness_type
                                    ORDER BY count DESC";
        
        $illness_breakdown_stmt = $conn->prepare($illness_breakdown_query);
        $illness_breakdown_stmt->bind_param($param_types, ...$params);
        $illness_breakdown_stmt->execute();
        $illness_breakdown_result = $illness_breakdown_stmt->get_result();
        
        // Get barangay breakdown
        $barangay_breakdown_query = "SELECT 
                                      b.barangay_name,
                                      COUNT(*) as count
                                    $base_query
                                    GROUP BY b.barangay_name
                                    ORDER BY count DESC";
        
        $barangay_breakdown_stmt = $conn->prepare($barangay_breakdown_query);
        $barangay_breakdown_stmt->bind_param($param_types, ...$params);
        $barangay_breakdown_stmt->execute();
        $barangay_breakdown_result = $barangay_breakdown_stmt->get_result();
        
    } elseif ($report_type == 'detailed') {
        // Detailed case listing
        $query = "SELECT 
                    i.case_id,
                    i.illness_type,
                    p.full_name as patient_name,
                    p.age,
                    p.gender,
                    b.barangay_name,
                    i.status,
                    i.reported_date,
                    i.symptoms,
                    i.notes
                  $base_query
                  ORDER BY i.reported_date DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        $detailed_result = $stmt->get_result();
        
    } elseif ($report_type == 'trend') {
        // Trend analysis
        $query = "SELECT 
                    DATE_FORMAT(i.reported_date, '%Y-%m') as month,
                    COUNT(*) as count,
                    COUNT(CASE WHEN i.illness_type = 'Influenza' THEN 1 END) as influenza,
                    COUNT(CASE WHEN i.illness_type = 'Respiratory Infection' THEN 1 END) as respiratory,
                    COUNT(CASE WHEN i.illness_type = 'Diarrhea' THEN 1 END) as diarrhea,
                    COUNT(CASE WHEN i.illness_type NOT IN ('Influenza', 'Respiratory Infection', 'Diarrhea') THEN 1 END) as others
                  $base_query
                  GROUP BY DATE_FORMAT(i.reported_date, '%Y-%m')
                  ORDER BY month";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        $trend_result = $stmt->get_result();
        
    } elseif ($report_type == 'demographic') {
        // Demographic analysis
        $age_query = "SELECT 
                        CASE 
                            WHEN p.age BETWEEN 0 AND 5 THEN '0-5'
                            WHEN p.age BETWEEN 6 AND 12 THEN '6-12'
                            WHEN p.age BETWEEN 13 AND 18 THEN '13-18'
                            WHEN p.age BETWEEN 19 AND 30 THEN '19-30'
                            WHEN p.age BETWEEN 31 AND 45 THEN '31-45'
                            WHEN p.age BETWEEN 46 AND 60 THEN '46-60'
                            ELSE '60+' 
                        END as age_group,
                        COUNT(*) as count
                      $base_query
                      GROUP BY age_group
                      ORDER BY FIELD(age_group, '0-5', '6-12', '13-18', '19-30', '31-45', '46-60', '60+')";
        
        $age_stmt = $conn->prepare($age_query);
        $age_stmt->bind_param($param_types, ...$params);
        $age_stmt->execute();
        $age_result = $age_stmt->get_result();
        
        // Gender breakdown
        $gender_query = "SELECT 
                          p.gender,
                          COUNT(*) as count
                        $base_query
                        GROUP BY p.gender";
        
        $gender_stmt = $conn->prepare($gender_query);
        $gender_stmt->bind_param($param_types, ...$params);
        $gender_stmt->execute();
        $gender_result = $gender_stmt->get_result();
    }
    
    // Handle export formats
    if ($format == 'pdf' || $format == 'excel') {
        // In a real implementation, you would generate PDF or Excel here
        // For this example, we'll just show a message
        $export_message = "Export to " . strtoupper($format) . " would be generated here.";
    }
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Illness Reports</h1>
    <p class="mb-4">Generate and download reports for illness cases.</p>

    <!-- Report Generator -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Report Generator</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="report_type">Report Type</label>
                        <select class="form-control" id="report_type" name="report_type">
                            <option value="summary" <?php if ($report_type == 'summary') echo 'selected'; ?>>Summary Report</option>
                            <option value="detailed" <?php if ($report_type == 'detailed') echo 'selected'; ?>>Detailed Case Listing</option>
                            <option value="trend" <?php if ($report_type == 'trend') echo 'selected'; ?>>Trend Analysis</option>
                            <option value="demographic" <?php if ($report_type == 'demographic') echo 'selected'; ?>>Demographic Analysis</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="date_range">Date Range</label>
                        <select class="form-control" id="date_range" name="date_range">
                            <option value="custom" <?php if ($date_range == 'custom') echo 'selected'; ?>>Custom Range</option>
                            <option value="week" <?php if ($date_range == 'week') echo 'selected'; ?>>Last 7 Days</option>
                            <option value="month" <?php if ($date_range == 'month') echo 'selected'; ?>>This Month</option>
                            <option value="quarter" <?php if ($date_range == 'quarter') echo 'selected'; ?>>This Quarter</option>
                            <option value="year" <?php if ($date_range == 'year') echo 'selected'; ?>>This Year</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3 custom-date-range" <?php if ($date_range != 'custom') echo 'style="display:none;"'; ?>>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="start_date">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="end_date">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="barangay_id">Barangay</label>
                        <select class="form-control" id="barangay_id" name="barangay_id">
                            <option value="">All Barangays</option>
                            <?php 
                            // Reset the barangays result pointer
                            $barangays_result->data_seek(0);
                            while ($barangay = $barangays_result->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $barangay['id']; ?>" <?php if ($barangay_id == $barangay['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($barangay['barangay_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="illness_type">Illness Type</label>
                        <select class="form-control" id="illness_type" name="illness_type">
                            <option value="">All Illness Types</option>
                            <?php while ($type = $illness_types_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($type['illness_type']); ?>" <?php if ($illness_type == $type['illness_type']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($type['illness_type']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="format">Output Format</label>
                        <select class="form-control" id="format" name="format">
                            <option value="html" <?php if ($format == 'html') echo 'selected'; ?>>View in Browser</option>
                            <option value="pdf" <?php if ($format == 'pdf') echo 'selected'; ?>>PDF Document</option>
                            <option value="excel" <?php if ($format == 'excel') echo 'selected'; ?>>Excel Spreadsheet</option>
                        </select>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <button type="submit" name="generate_report" class="btn btn-primary">
                        <i class="fas fa-file-alt mr-1"></i> Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($generate_report): ?>
        <?php if (isset($export_message)): ?>
            <div class="alert alert-info">
                <?php echo $export_message; ?>
                <p class="mt-2 mb-0">For demonstration purposes, the report is displayed below in HTML format.</p>
            </div>
        <?php endif; ?>
        
        <!-- Report Display -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <?php 
                    $report_title = '';
                    switch ($report_type) {
                        case 'summary':
                            $report_title = 'Summary Report';
                            break;
                        case 'detailed':
                            $report_title = 'Detailed Case Listing';
                            break;
                        case 'trend':
                            $report_title = 'Trend Analysis';
                            break;
                        case 'demographic':
                            $report_title = 'Demographic Analysis';
                            break;
                    }
                    echo $report_title;
                    ?>
                </h6>
                <div>
                    <button onclick="window.print()" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-print mr-1"></i> Print
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h5>Report Parameters</h5>
                    <p>
                        <strong>Date Range:</strong> <?php echo date('M d, Y', strtotime($start_date)); ?> to <?php echo date('M d, Y', strtotime($end_date)); ?><br>
                        <strong>Barangay:</strong> <?php echo !empty($barangay_id) ? get_barangay_name($conn, $barangay_id) : 'All Barangays'; ?><br>
                        <strong>Illness Type:</strong> <?php echo !empty($illness_type) ? $illness_type : 'All Types'; ?>
                    </p>
                </div>
                
                <?php if ($report_type == 'summary' && isset($summary_result)): ?>
                    <!-- Summary Report -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Case Summary</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Total Cases</th>
                                            <td><?php echo $summary_result['total_cases']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Active Cases</th>
                                            <td><?php echo $summary_result['active_cases']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Under Treatment</th>
                                            <td><?php echo $summary_result['under_treatment']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Recovered</th>
                                            <td><?php echo $summary_result['recovered']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Chronic</th>
                                            <td><?php echo $summary_result['chronic']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Total Patients</th>
                                            <td><?php echo $summary_result['total_patients']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Affected Barangays</th>
                                            <td><?php echo $summary_result['affected_barangays']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Date Range</th>
                                            <td>
                                                <?php 
                                                if (!empty($summary_result['earliest_date']) && !empty($summary_result['latest_date'])) {
                                                    echo date('M d, Y', strtotime($summary_result['earliest_date'])) . ' to ' . 
                                                         date('M d, Y', strtotime($summary_result['latest_date']));
                                                } else {
                                                    echo 'No data';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Illness Type Breakdown</h6>
                                </div>
                                <div class="card-body">
                                    <?php if ($illness_breakdown_result->num_rows > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Illness Type</th>
                                                        <th>Cases</th>
                                                        <th>Percentage</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    while ($row = $illness_breakdown_result->fetch_assoc()): 
                                                        $percentage = ($row['count'] / $summary_result['total_cases']) * 100;
                                                    ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($row['illness_type']); ?></td>
                                                            <td><?php echo $row['count']; ?></td>
                                                            <td><?php echo number_format($percentage, 1); ?>%</td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-center">No data available</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Barangay Breakdown</h6>
                        </div>
                        <div class="card-body">
                            <?php if ($barangay_breakdown_result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Barangay</th>
                                                <th>Cases</th>
                                                <th>Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            while ($row = $barangay_breakdown_result->fetch_assoc()): 
                                                $percentage = ($row['count'] / $summary_result['total_cases']) * 100;
                                            ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['barangay_name']); ?></td>
                                                    <td><?php echo $row['count']; ?></td>
                                                    <td><?php echo number_format($percentage, 1); ?>%</td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-center">No data available</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                <?php elseif ($report_type == 'detailed' && isset($detailed_result)): ?>
                    <!-- Detailed Case Listing -->
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
                                    <th>Symptoms</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($detailed_result->num_rows > 0): ?>
                                    <?php while ($row = $detailed_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['case_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['illness_type']); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($row['patient_name']); ?>
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
                                            <td><?php echo htmlspecialchars($row['symptoms']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                <?php elseif ($report_type == 'trend' && isset($trend_result)): ?>
                    <!-- Trend Analysis -->
                    <div class="chart-area mb-4">
                        <canvas id="trendChart"></canvas>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Total Cases</th>
                                    <th>Influenza</th>
                                    <th>Respiratory</th>
                                    <th>Diarrhea</th>
                                    <th>Others</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $months = [];
                                $total_counts = [];
                                $influenza_counts = [];
                                $respiratory_counts = [];
                                $diarrhea_counts = [];
                                $others_counts = [];
                                
                                if ($trend_result->num_rows > 0): 
                                    while ($row = $trend_result->fetch_assoc()): 
                                        $month_label = date('M Y', strtotime($row['month'] . '-01'));
                                        $months[] = $month_label;
                                        $total_counts[] = (int)$row['count'];
                                        $influenza_counts[] = (int)$row['influenza'];
                                        $respiratory_counts[] = (int)$row['respiratory'];
                                        $diarrhea_counts[] = (int)$row['diarrhea'];
                                        $others_counts[] = (int)$row['others'];
                                ?>
                                    <tr>
                                        <td><?php echo $month_label; ?></td>
                                        <td><?php echo $row['count']; ?></td>
                                        <td><?php echo $row['influenza']; ?></td>
                                        <td><?php echo $row['respiratory']; ?></td>
                                        <td><?php echo $row['diarrhea']; ?></td>
                                        <td><?php echo $row['others']; ?></td>
                                    </tr>
                                <?php 
                                    endwhile; 
                                else: 
                                ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var ctx = document.getElementById('trendChart').getContext('2d');
                        var trendChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: <?php echo json_encode($months); ?>,
                                datasets: [
                                    {
                                        label: 'Total Cases',
                                        data: <?php echo json_encode($total_counts); ?>,
                                        backgroundColor: 'rgba(78, 115, 223, 0.05)',
                                        borderColor: 'rgba(78, 115, 223, 1)',
                                        pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                                        pointBorderColor: '#fff',
                                        pointHoverRadius: 3,
                                        pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                                        pointHoverBorderColor: '#fff',
                                        pointHitRadius: 10,
                                        pointBorderWidth: 2,
                                        tension: 0.3,
                                        fill: true
                                    },
                                    {
                                        label: 'Influenza',
                                        data: <?php echo json_encode($influenza_counts); ?>,
                                        backgroundColor: 'transparent',
                                        borderColor: 'rgba(28, 200, 138, 1)',
                                        pointBackgroundColor: 'rgba(28, 200, 138, 1)',
                                        pointBorderColor: '#fff',
                                        pointHoverRadius: 3,
                                        pointHoverBackgroundColor: 'rgba(28, 200, 138, 1)',
                                        pointHoverBorderColor: '#fff',
                                        pointHitRadius: 10,
                                        pointBorderWidth: 2,
                                        tension: 0.3
                                    },
                                    {
                                        label: 'Respiratory',
                                        data: <?php echo json_encode($respiratory_counts); ?>,
                                        backgroundColor: 'transparent',
                                        borderColor: 'rgba(246, 194, 62, 1)',
                                        pointBackgroundColor: 'rgba(246, 194, 62, 1)',
                                        pointBorderColor: '#fff',
                                        pointHoverRadius: 3,
                                        pointHoverBackgroundColor: 'rgba(246, 194, 62, 1)',
                                        pointHoverBorderColor: '#fff',
                                        pointHitRadius: 10,
                                        pointBorderWidth: 2,
                                        tension: 0.3
                                    },
                                    {
                                        label: 'Diarrhea',
                                        data: <?php echo json_encode($diarrhea_counts); ?>,
                                        backgroundColor: 'transparent',
                                        borderColor: 'rgba(231, 74, 59, 1)',
                                        pointBackgroundColor: 'rgba(231, 74, 59, 1)',
                                        pointBorderColor: '#fff',
                                        pointHoverRadius: 3,
                                        pointHoverBackgroundColor: 'rgba(231, 74, 59, 1)',
                                        pointHoverBorderColor: '#fff',
                                        pointHitRadius: 10,
                                        pointBorderWidth: 2,
                                        tension: 0.3
                                    }
                                ]
                            },
                            options: {
                                maintainAspectRatio: false,
                                layout: {
                                    padding: {
                                        left: 10,
                                        right: 25,
                                        top: 25,
                                        bottom: 0
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: {
                                            display: false,
                                            drawBorder: false
                                        }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        grid: {
                                            color: "rgb(234, 236, 244)",
                                            zeroLineColor: "rgb(234, 236, 244)",
                                            drawBorder: false,
                                            borderDash: [2],
                                            zeroLineBorderDash: [2]
                                        },
                                        ticks: {
                                            precision: 0
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top'
                                    },
                                    tooltip: {
                                        backgroundColor: "rgb(255,255,255)",
                                        bodyColor: "#858796",
                                        titleMarginBottom: 10,
                                        titleColor: '#6e707e',
                                        titleFontSize: 14,
                                        borderColor: '#dddfeb',
                                        borderWidth: 1,
                                        xPadding: 15,
                                        yPadding: 15,
                                        displayColors: false,
                                        intersect: false,
                                        mode: 'index',
                                        caretPadding: 10
                                    }
                                }
                            }
                        });
                    });
                    </script>
                    
                <?php elseif ($report_type == 'demographic' && isset($age_result) && isset($gender_result)): ?>
                    <!-- Demographic Analysis -->
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="card mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Age Group Distribution</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-bar">
                                        <canvas id="ageGroupChart"></canvas>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <div class="table-responsive">
                                            <table class="table table-bordered" width="100%" cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th>Age Group</th>
                                                        <th>Cases</th>
                                                        <th>Percentage</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $age_groups = [];
                                                    $age_counts = [];
                                                    $total_age_count = 0;
                                                    
                                                    while ($row = $age_result->fetch_assoc()) {
                                                        $age_groups[] = $row['age_group'];
                                                        $age_counts[] = (int)$row['count'];
                                                        $total_age_count += (int)$row['count'];
                                                    }
                                                    
                                                    // Reset result pointer
                                                    $age_result->data_seek(0);
                                                    
                                                    if ($age_result->num_rows > 0): 
                                                        while ($row = $age_result->fetch_assoc()): 
                                                            $percentage = ($row['count'] / $total_age_count) * 100;
                                                    ?>
                                                        <tr>
                                                            <td><?php echo $row['age_group']; ?></td>
                                                            <td><?php echo $row['count']; ?></td>
                                                            <td><?php echo number_format($percentage, 1); ?>%</td>
                                                        </tr>
                                                    <?php 
                                                        endwhile; 
                                                    else: 
                                                    ?>
                                                        <tr>
                                                            <td colspan="3" class="text-center">No data available</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-6">
                            <div class="card mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Gender Distribution</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-pie pt-4 pb-2">
                                        <canvas id="genderChart"></canvas>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <div class="table-responsive">
                                            <table class="table table-bordered" width="100%" cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th>Gender</th>
                                                        <th>Cases</th>
                                                        <th>Percentage</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $genders = [];
                                                    $gender_counts = [];
                                                    $total_gender_count = 0;
                                                    
                                                    while ($row = $gender_result->fetch_assoc()) {
                                                        $genders[] = $row['gender'];
                                                        $gender_counts[] = (int)$row['count'];
                                                        $total_gender_count += (int)$row['count'];
                                                    }
                                                    
                                                    // Reset result pointer
                                                    $gender_result->data_seek(0);
                                                    
                                                    if ($gender_result->num_rows > 0): 
                                                        while ($row = $gender_result->fetch_assoc()): 
                                                            $percentage = ($row['count'] / $total_gender_count) * 100;
                                                    ?>
                                                        <tr>
                                                            <td><?php echo $row['gender']; ?></td>
                                                            <td><?php echo $row['count']; ?></td>
                                                            <td><?php echo number_format($percentage, 1); ?>%</td>
                                                        </tr>
                                                    <?php 
                                                        endwhile; 
                                                    else: 
                                                    ?>
                                                        <tr>
                                                            <td colspan="3" class="text-center">No data available</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Age Group Chart
                        var ageCtx = document.getElementById('ageGroupChart').getContext('2d');
                        var ageChart = new Chart(ageCtx, {
                            type: 'bar',
                            data: {
                                labels: <?php echo json_encode($age_groups); ?>,
                                datasets: [{
                                    label: 'Cases',
                                    data: <?php echo json_encode($age_counts); ?>,
                                    backgroundColor: '#4e73df',
                                    hoverBackgroundColor: '#2e59d9',
                                    borderWidth: 0,
                                    borderRadius: 4
                                }]
                            },
                            options: {
                                maintainAspectRatio: false,
                                layout: {
                                    padding: {
                                        left: 10,
                                        right: 25,
                                        top: 25,
                                        bottom: 0
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: {
                                            display: false,
                                            drawBorder: false
                                        }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        grid: {
                                            color: "rgb(234, 236, 244)",
                                            zeroLineColor: "rgb(234, 236, 244)",
                                            drawBorder: false,
                                            borderDash: [2],
                                            zeroLineBorderDash: [2]
                                        },
                                        ticks: {
                                            precision: 0
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        backgroundColor: "rgb(255,255,255)",
                                        bodyColor: "#858796",
                                        titleMarginBottom: 10,
                                        titleColor: '#6e707e',
                                        titleFontSize: 14,
                                        borderColor: '#dddfeb',
                                        borderWidth: 1,
                                        xPadding: 15,
                                        yPadding: 15,
                                        displayColors: false,
                                        caretPadding: 10
                                    }
                                }
                            }
                        });
                        
                        // Gender Chart
                        var genderCtx = document.getElementById('genderChart').getContext('2d');
                        var genderChart = new Chart(genderCtx, {
                            type: 'doughnut',
                            data: {
                                labels: <?php echo json_encode($genders); ?>,
                                datasets: [{
                                    data: <?php echo json_encode($gender_counts); ?>,
                                    backgroundColor: ['#4e73df', '#1cc88a'],
                                    hoverBackgroundColor: ['#2e59d9', '#17a673'],
                                    hoverBorderColor: "rgba(234, 236, 244, 1)"
                                }]
                            },
                            options: {
                                maintainAspectRatio: false,
                                cutout: '60%',
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'bottom'
                                    },
                                    tooltip: {
                                        backgroundColor: "rgb(255,255,255)",
                                        bodyColor: "#858796",
                                        titleMarginBottom: 10,
                                        titleColor: '#6e707e',
                                        titleFontSize: 14,
                                        borderColor: '#dddfeb',
                                        borderWidth: 1,
                                        xPadding: 15,
                                        yPadding: 15,
                                        displayColors: false,
                                        caretPadding: 10
                                    }
                                }
                            }
                        });
                    });
                    </script>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Toggle custom date range based on selection
document.getElementById('date_range').addEventListener('change', function() {
    var customDateRange = document.querySelector('.custom-date-range');
    if (this.value === 'custom') {
        customDateRange.style.display = 'block';
    } else {
        customDateRange.style.display = 'none';
    }
});
</script>

<?php
// Helper function to get barangay name
function get_barangay_name($conn, $barangay_id) {
    $stmt = $conn->prepare("SELECT barangay_name FROM barangays WHERE id = ?");
    $stmt->bind_param("s", $barangay_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['barangay_name'];
    }
    
    return 'Unknown';
}

include '../includes/footer.php';
?>