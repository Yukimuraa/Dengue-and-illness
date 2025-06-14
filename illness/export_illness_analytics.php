<?php
// Initialize the session and include necessary files
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
check_login();

// Get filter parameters
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$barangay_id = isset($_GET['barangay_id']) ? $_GET['barangay_id'] : '';

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="illness_analytics_' . $year . '.xls"');
header('Cache-Control: max-age=0');

// Output Excel content
echo "Illness Analytics Report\n";
echo "Year: " . $year . "\n";
if (!empty($barangay_id)) {
    $barangay_query = "SELECT barangay_name FROM barangays WHERE id = ?";
    $barangay_stmt = $conn->prepare($barangay_query);
    $barangay_stmt->bind_param("s", $barangay_id);
    $barangay_stmt->execute();
    $barangay_result = $barangay_stmt->get_result();
    if ($barangay_row = $barangay_result->fetch_assoc()) {
        echo "Barangay: " . $barangay_row['barangay_name'] . "\n";
    }
}
echo "\n";

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

// Output Monthly Trend
echo "Monthly Illness Trend\n";
echo "Month\tInfluenza\tRespiratory\tDiarrhea\tOthers\n";

$months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
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

foreach ($monthly_data as $index => $data) {
    echo $months[$index] . "\t";
    echo $data['influenza'] . "\t";
    echo $data['respiratory'] . "\t";
    echo $data['diarrhea'] . "\t";
    echo $data['others'] . "\n";
}

echo "\n";

// Illness Type Distribution
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

echo "Illness Type Distribution\n";
echo "Illness Type\tCount\n";

while ($row = $illness_types_result->fetch_assoc()) {
    echo $row['illness_type'] . "\t" . $row['count'] . "\n";
}

echo "\n";

// Barangay Distribution
$barangay_distribution_query = "SELECT 
                                  b.barangay_name,
                                  COUNT(*) as count
                                FROM illnesses i
                                JOIN patients p ON i.patient_id = p.id
                                JOIN barangays b ON p.barangay_id = b.id
                                WHERE YEAR(i.reported_date) = ?";

if (!empty($barangay_id)) {
    $barangay_distribution_query .= " AND p.barangay_id = ?";
}

$barangay_distribution_query .= " GROUP BY b.barangay_name
                                ORDER BY count DESC";

$barangay_distribution_stmt = $conn->prepare($barangay_distribution_query);

if (!empty($barangay_id)) {
    $barangay_distribution_stmt->bind_param("is", $year, $barangay_id);
} else {
    $barangay_distribution_stmt->bind_param("i", $year);
}

$barangay_distribution_stmt->execute();
$barangay_distribution_result = $barangay_distribution_stmt->get_result();

echo "Barangay Distribution\n";
echo "Barangay\tCount\n";

while ($row = $barangay_distribution_result->fetch_assoc()) {
    echo $row['barangay_name'] . "\t" . $row['count'] . "\n";
}

echo "\n";

// Age Group Distribution
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

echo "Age Group Distribution\n";
echo "Age Group\tCount\n";

while ($row = $age_group_result->fetch_assoc()) {
    echo $row['age_group'] . "\t" . $row['count'] . "\n";
}

exit; 