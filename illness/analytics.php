<?php
// Initialize the session and include necessary files
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
check_login();

// Set page title
$page_title = "Illness Analytics";
include '../includes/header.php';

// Get filter parameters
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$barangay_id = isset($_GET['barangay_id']) ? $_GET['barangay_id'] : '';

// Get available years for filter
$years_query = "SELECT DISTINCT YEAR(reported_date) as year FROM illnesses ORDER BY year DESC";
$years_result = $conn->query($years_query);

// Get barangays for filter
$barangays_query = "SELECT id, barangay_name FROM barangays ORDER BY barangay_name";
$barangays_result = $conn->query($barangays_query);

// Monthly illness data
$monthly_query = "SELECT 
                    MONTH(i.reported_date) as month,
                    COUNT(CASE WHEN i.illness_type = 'Influenza' THEN 1 END) as influenza,
                    COUNT(CASE WHEN i.illness_type = 'Respiratory Infection' THEN 1 END) as respiratory,
                    COUNT(CASE WHEN i.illness_type = 'Diarrhea' THEN 1 END) as diarrhea,
                    COUNT(CASE WHEN i.illness_type NOT IN ('Influenza', 'Respiratory Infection', 'Diarrhea') THEN 1 END) as others
                  FROM illnesses i
                  JOIN patients p ON i.patient_id = p.id
                  WHERE YEAR(i.reported_date) = ?";

if (!empty($barangay_id)) {
    $monthly_query .= " AND p.barangay_id = ?";
}

$monthly_query .= " GROUP BY MONTH(i.reported_date)
                    ORDER BY MONTH(i.reported_date)";

$monthly_stmt = $conn->prepare($monthly_query);

if (!empty($barangay_id)) {
    $monthly_stmt->bind_param("is", $year, $barangay_id);
} else {
    $monthly_stmt->bind_param("i", $year);
}

$monthly_stmt->execute();
$monthly_result = $monthly_stmt->get_result();

// Illness type distribution
$illness_types_query = "SELECT 
                          illness_type,
                          COUNT(*) as count
                        FROM illnesses
                        WHERE YEAR(reported_date) = ?";

if (!empty($barangay_id)) {
    $illness_types_query .= " AND patient_id IN (SELECT id FROM patients WHERE barangay_id = ?)";
}

$illness_types_query .= " GROUP BY illness_type
                          ORDER BY count DESC";

$illness_types_stmt = $conn->prepare($illness_types_query);

if (!empty($barangay_id)) {
    $illness_types_stmt->bind_param("is", $year, $barangay_id);
} else {
    $illness_types_stmt->bind_param("i", $year);
}

$illness_types_stmt->execute();
$illness_types_result = $illness_types_stmt->get_result();

// Barangay distribution
$barangay_distribution_query = "SELECT 
                                  b.barangay_name,
                                  COUNT(*) as count
                                FROM illnesses i
                                JOIN patients p ON i.patient_id = p.id
                                JOIN barangays b ON p.barangay_id = b.id
                                WHERE YEAR(i.reported_date) = ?
                                GROUP BY b.barangay_name
                                ORDER BY count DESC
                                LIMIT 10";

$barangay_distribution_stmt = $conn->prepare($barangay_distribution_query);
$barangay_distribution_stmt->bind_param("i", $year);
$barangay_distribution_stmt->execute();
$barangay_distribution_result = $barangay_distribution_stmt->get_result();

// Age group distribution
$age_group_query = "SELECT 
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
                    FROM illnesses i
                    JOIN patients p ON i.patient_id = p.id
                    WHERE YEAR(i.reported_date) = ?";

if (!empty($barangay_id)) {
    $age_group_query .= " AND p.barangay_id = ?";
}

$age_group_query .= " GROUP BY age_group
                      ORDER BY FIELD(age_group, '0-5', '6-12', '13-18', '19-30', '31-45', '46-60', '60+')";

$age_group_stmt = $conn->prepare($age_group_query);

if (!empty($barangay_id)) {
    $age_group_stmt->bind_param("is", $year, $barangay_id);
} else {
    $age_group_stmt->bind_param("i", $year);
}

$age_group_stmt->execute();
$age_group_result = $age_group_stmt->get_result();

// Prepare data for charts
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$monthly_data = array_fill(0, 12, ['influenza' => 0, 'respiratory' => 0, 'diarrhea' => 0, 'others' => 0]);

while ($row = $monthly_result->fetch_assoc()) {
    $month_index = (int)$row['month'] - 1;
    $monthly_data[$month_index] = [
        'influenza' => (int)$row['influenza'],
        'respiratory' => (int)$row['respiratory'],
        'diarrhea' => (int)$row['diarrhea'],
        'others' => (int)$row['others']
    ];
}

// Prepare illness type data
$illness_types = [];
$illness_counts = [];

while ($row = $illness_types_result->fetch_assoc()) {
    $illness_types[] = $row['illness_type'];
    $illness_counts[] = (int)$row['count'];
}

// Prepare barangay distribution data
$barangay_names = [];
$barangay_counts = [];

while ($row = $barangay_distribution_result->fetch_assoc()) {
    $barangay_names[] = $row['barangay_name'];
    $barangay_counts[] = (int)$row['count'];
}

// Prepare age group data
$age_groups = [];
$age_counts = [];

while ($row = $age_group_result->fetch_assoc()) {
    $age_groups[] = $row['age_group'];
    $age_counts[] = (int)$row['count'];
}
?>

<!-- Add Chart.js v3 -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Illness Analytics</h1>
    <p class="mb-4">Comprehensive analytics and visualizations for illness cases.</p>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="analytics.php" class="row">
                <div class="col-md-4 mb-3">
                    <label for="year">Year</label>
                    <select class="form-control" id="year" name="year">
                        <?php while ($year_row = $years_result->fetch_assoc()): ?>
                            <option value="<?php echo $year_row['year']; ?>" <?php if ($year == $year_row['year']) echo 'selected'; ?>>
                                <?php echo $year_row['year']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
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
                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Monthly Trend Chart -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Monthly Illness Trend (<?php echo $year; ?>)</h6>
        </div>
        <div class="card-body">
            <div class="chart-area">
                <canvas id="monthlyIllnessChart"></canvas>
            </div>
            <hr>
            <div class="text-center small mt-2">
                <span class="mr-2">
                    <i class="fas fa-circle text-primary"></i> Influenza
                </span>
                <span class="mr-2">
                    <i class="fas fa-circle text-success"></i> Respiratory
                </span>
                <span class="mr-2">
                    <i class="fas fa-circle text-warning"></i> Diarrhea
                </span>
                <span class="mr-2">
                    <i class="fas fa-circle text-secondary"></i> Others
                </span>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Illness Type Distribution -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Illness Type Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="illnessTypeChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <?php foreach ($illness_types as $index => $type): ?>
                            <span class="mr-2">
                                <i class="fas fa-circle" style="color: <?php echo get_chart_color($index); ?>"></i> <?php echo $type; ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Age Group Distribution -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Age Group Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar pt-4 pb-2">
                        <canvas id="ageGroupChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barangay Distribution -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Top Barangays by Illness Cases</h6>
        </div>
        <div class="card-body">
            <div class="chart-bar">
                <canvas id="barangayDistributionChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
// Function to get chart colors
function getChartColors(index) {
    const colors = [
        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#5a5c69',
        '#6610f2', '#6f42c1', '#fd7e14', '#20c9a6', '#858796', '#17a673'
    ];
    return colors[index % colors.length];
}

// Log Chart.js version for debugging
console.log('Chart.js version:', Chart.version);

// Set up Chart.js
document.addEventListener('DOMContentLoaded', function() {
    try {
        // Monthly Illness Trend Chart
        var monthlyCtx = document.getElementById('monthlyIllnessChart').getContext('2d');
        var monthlyChart = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [
                    {
                        label: 'Influenza',
                        data: <?php echo json_encode(array_column($monthly_data, 'influenza')); ?>,
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
                        label: 'Respiratory',
                        data: <?php echo json_encode(array_column($monthly_data, 'respiratory')); ?>,
                        backgroundColor: 'rgba(28, 200, 138, 0.05)',
                        borderColor: 'rgba(28, 200, 138, 1)',
                        pointBackgroundColor: 'rgba(28, 200, 138, 1)',
                        pointBorderColor: '#fff',
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: 'rgba(28, 200, 138, 1)',
                        pointHoverBorderColor: '#fff',
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Diarrhea',
                        data: <?php echo json_encode(array_column($monthly_data, 'diarrhea')); ?>,
                        backgroundColor: 'rgba(246, 194, 62, 0.05)',
                        borderColor: 'rgba(246, 194, 62, 1)',
                        pointBackgroundColor: 'rgba(246, 194, 62, 1)',
                        pointBorderColor: '#fff',
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: 'rgba(246, 194, 62, 1)',
                        pointHoverBorderColor: '#fff',
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Others',
                        data: <?php echo json_encode(array_column($monthly_data, 'others')); ?>,
                        backgroundColor: 'rgba(133, 135, 150, 0.05)',
                        borderColor: 'rgba(133, 135, 150, 1)',
                        pointBackgroundColor: 'rgba(133, 135, 150, 1)',
                        pointBorderColor: '#fff',
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: 'rgba(133, 135, 150, 1)',
                        pointHoverBorderColor: '#fff',
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        tension: 0.3,
                        fill: true
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
                            drawBorder: false,
                            borderDash: [2]
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
                        titleFont: {
                            size: 14
                        },
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        padding: {
                            x: 15,
                            y: 15
                        },
                        displayColors: false,
                        intersect: false,
                        mode: 'index',
                        caretPadding: 10
                    }
                }
            }
        });

        // Illness Type Distribution Chart
        var illnessTypeCtx = document.getElementById('illnessTypeChart').getContext('2d');
        var illnessTypeChart = new Chart(illnessTypeCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($illness_types); ?>,
                datasets: [{
                    data: <?php echo json_encode($illness_counts); ?>,
                    backgroundColor: [
                        <?php 
                        foreach (range(0, max(0, count($illness_types) - 1)) as $i) {
                            echo "getChartColors($i),";
                        }
                        ?>
                    ],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617', '#3a3b45'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)"
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyColor: "#858796",
                        titleMarginBottom: 10,
                        titleColor: '#6e707e',
                        titleFont: {
                            size: 14
                        },
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        padding: {
                            x: 15,
                            y: 15
                        },
                        displayColors: false,
                        caretPadding: 10
                    }
                }
            }
        });

        // Age Group Distribution Chart
        var ageGroupCtx = document.getElementById('ageGroupChart').getContext('2d');
        var ageGroupChart = new Chart(ageGroupCtx, {
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
                            drawBorder: false,
                            borderDash: [2]
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
                        titleFont: {
                            size: 14
                        },
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        padding: {
                            x: 15,
                            y: 15
                        },
                        displayColors: false,
                        caretPadding: 10
                    }
                }
            }
        });

        // Barangay Distribution Chart
        var barangayDistributionCtx = document.getElementById('barangayDistributionChart').getContext('2d');
        var barangayDistributionChart = new Chart(barangayDistributionCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($barangay_names); ?>,
                datasets: [{
                    label: 'Cases',
                    data: <?php echo json_encode($barangay_counts); ?>,
                    backgroundColor: '#36b9cc',
                    hoverBackgroundColor: '#2c9faf',
                    borderWidth: 0,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
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
                        beginAtZero: true,
                        grid: {
                            color: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2]
                        },
                        ticks: {
                            precision: 0
                        }
                    },
                    y: {
                        grid: {
                            display: false,
                            drawBorder: false
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
                        titleFont: {
                            size: 14
                        },
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        padding: {
                            x: 15,
                            y: 15
                        },
                        displayColors: false,
                        caretPadding: 10
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error initializing charts:', error);
        // Display error message on the page
        document.querySelectorAll('.chart-area, .chart-pie, .chart-bar').forEach(function(chartContainer) {
            chartContainer.innerHTML = '<div class="alert alert-danger">Error loading chart: ' + error.message + '</div>';
        });
    }
});
</script>

<?php
// Helper function to get chart colors
function get_chart_color($index) {
    $colors = [
        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#5a5c69',
        '#6610f2', '#6f42c1', '#fd7e14', '#20c9a6', '#858796', '#17a673'
    ];
    return $colors[$index % count($colors)];
}

// Debug information
echo "<script>console.log('Barangay Names:', " . json_encode($barangay_names) . ");</script>";
echo "<script>console.log('Barangay Counts:', " . json_encode($barangay_counts) . ");</script>";
echo "<script>console.log('Illness Types:', " . json_encode($illness_types) . ");</script>";
echo "<script>console.log('Illness Counts:', " . json_encode($illness_counts) . ");</script>";

include '../includes/footer.php';
?>