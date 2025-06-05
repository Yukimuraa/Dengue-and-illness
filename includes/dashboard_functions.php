<?php
$page_title = "Dashboard";
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/dashboard_functions.php';
requireLogin();

// Get data
$total_dengue = getTotalDengueCases();
$total_illness = getTotalIllnessCases();
$dengue_by_barangay = getDengueCasesByBarangay();
$illness_by_type = getIllnessCasesByType();
$monthly_dengue = getMonthlyDengueCases();

$barangay_labels = array_column($dengue_by_barangay, 'barangay');
$barangay_values = array_column($dengue_by_barangay, 'count');
$illness_labels = array_map('ucfirst', array_column($illness_by_type, 'illness_type'));
$illness_values = array_column($illness_by_type, 'count');

$sql_recent_dengue = "SELECT * FROM dengue_cases ORDER BY reported_date DESC LIMIT 5";
$result_recent_dengue = mysqli_query($conn, $sql_recent_dengue);
$sql_recent_illness = "SELECT * FROM illness_cases ORDER BY reported_date DESC LIMIT 5";
$result_recent_illness = mysqli_query($conn, $sql_recent_illness);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dengue Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    

    <style>
        body {
            background-color: #f8f9fc;
        }
        .dashboard-header {
            font-weight: 700;
            color: #2c3e50;
        }
        .stats-card {
            transition: transform 0.2s ease-in-out;
            border-radius: 1rem;
        }
        .stats-card:hover {
            transform: scale(1.03);
        }
        .stats-icon {
            filter: drop-shadow(0 0 5px rgba(0,0,0,0.1));
        }
        .card {
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .card:hover {
            box-shadow: 0 6px 24px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        .table thead th {
            background-color: #f8f9fc;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        @media (max-width: 768px) {
            .chart-container {
                height: 250px;
            }
        }
    </style>
</head>
<body class="container mt-4">

    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0 dashboard-header">Dashboard</h1>
            <p class="text-muted">Overview of health statistics in Talisay City</p>
        </div>
        <div class="col-md-6 text-md-right">
            <p class="mb-0"><i class="fas fa-calendar-day mr-1"></i> <?= date('F d, Y'); ?></p>
        </div>
    </div>

    <div class="row">
        <!-- Stat Cards -->
        <?php
        $stats = [
            ['label' => 'Total Dengue Cases', 'value' => $total_dengue, 'icon' => 'fa-mosquito', 'color' => 'danger'],
            ['label' => 'Active Outbreaks', 'value' => 3, 'icon' => 'fa-exclamation-triangle', 'color' => 'warning'],
            ['label' => 'Total Illness Cases', 'value' => $total_illness, 'icon' => 'fa-virus', 'color' => 'info'],
            ['label' => 'Health Workers Active', 'value' => 42, 'icon' => 'fa-user-md', 'color' => 'success'],
        ];
        foreach ($stats as $stat): ?>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-<?= $stat['color']; ?> shadow h-100 py-2 stats-card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-<?= $stat['color']; ?> text-uppercase mb-1">
                                    <?= $stat['label']; ?>
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stat['value']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas <?= $stat['icon']; ?> fa-2x text-gray-300 stats-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">Monthly Dengue Cases (<?= date('Y'); ?>)</div>
                <div class="card-body chart-container">
                    <canvas id="monthlyDengueChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">Illness Distribution</div>
                <div class="card-body chart-container">
                    <canvas id="illnessTypeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Barangay Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">Dengue Cases by Barangay</div>
                <div class="card-body chart-container">
                    <canvas id="barangayDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Cases -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">Recent Dengue Cases</div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr><th>Patient</th><th>Barangay</th><th>Status</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result_recent_dengue)): ?>
                                <tr>
                                    <td><?= $row['patient_name']; ?>, <?= $row['patient_age']; ?></td>
                                    <td><?= $row['barangay']; ?></td>
                                    <td><?= getStatusBadge($row['case_status'], 'dengue'); ?></td>
                                    <td><?= formatDate($row['reported_date']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if (mysqli_num_rows($result_recent_dengue) == 0): ?>
                                <tr><td colspan="4" class="text-center">No recent dengue cases</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <div class="text-center">
                        <a href="<?= $site_url; ?>/dengue/index.php" class="btn btn-sm btn-primary">View All Dengue Cases</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Illness Cases -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">Recent Illness Cases</div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr><th>Patient</th><th>Illness</th><th>Status</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result_recent_illness)): ?>
                                <tr>
                                    <td><?= $row['patient_name']; ?>, <?= $row['patient_age']; ?></td>
                                    <td><?= ucfirst($row['illness_type']); ?></td>
                                    <td><?= getStatusBadge($row['case_status'], 'illness'); ?></td>
                                    <td><?= formatDate($row['reported_date']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if (mysqli_num_rows($result_recent_illness) == 0): ?>
                                <tr><td colspan="4" class="text-center">No recent illness cases</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <div class="text-center">
                        <a href="<?= $site_url; ?>/illness/index.php" class="btn btn-sm btn-primary">View All Illness Cases</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const monthlyDengue = <?= json_encode(array_values($monthly_dengue)); ?>;
        const barangayLabels = <?= json_encode($barangay_labels); ?>;
        const barangayValues = <?= json_encode($barangay_values); ?>;
        const illnessLabels = <?= json_encode($illness_labels); ?>;
        const illnessValues = <?= json_encode($illness_values); ?>;

        new Chart(document.getElementById('monthlyDengueChart'), {
            type: 'line',
            data: {
                labels: ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
                datasets: [{
                    label: "Cases",
                    data: monthlyDengue,
                    borderColor: "#4e73df",
                    backgroundColor: "rgba(78,115,223,0.1)",
                    fill: true,
                    tension: 0.3
                }]
            }
        });

        new Chart(document.getElementById('illnessTypeChart'), {
            type: 'doughnut',
            data: {
                labels: illnessLabels,
                datasets: [{
                    data: illnessValues,
                    backgroundColor: ['#f6c23e', '#e74a3b', '#36b9cc', '#1cc88a', '#858796']
                }]
            }
        });

        new Chart(document.getElementById('barangayDistributionChart'), {
            type: 'bar',
            data: {
                labels: barangayLabels,
                datasets: [{
                    label: "Cases",
                    data: barangayValues,
                    backgroundColor: "#e74a3b"
                }]
            }
        });
    </script>
</body>
</html>
