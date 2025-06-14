<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireLogin();

// Get date range from request
$start_date = isset($_GET['start_date']) ? clean($_GET['start_date']) : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? clean($_GET['end_date']) : date('Y-m-d');
$barangay_filter = isset($_GET['barangay']) ? clean($_GET['barangay']) : '';

// Build WHERE clause
$where = "reported_date BETWEEN '$start_date' AND '$end_date'";
if (!empty($barangay_filter)) {
    $where .= " AND barangay = '$barangay_filter'";
}

// Get all cases in date range
$sql = "SELECT * FROM dengue_cases WHERE $where ORDER BY reported_date DESC";
$result = mysqli_query($conn, $sql);

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="dengue_report_' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');

// Output Excel content
echo "Dengue Cases Report\n";
echo "Period: " . date('F d, Y', strtotime($start_date)) . " to " . date('F d, Y', strtotime($end_date)) . "\n";
if (!empty($barangay_filter)) {
    echo "Barangay: " . $barangay_filter . "\n";
}
echo "\n";

// Headers
echo "ID\t";
echo "Patient Name\t";
echo "Age\t";
echo "Gender\t";
echo "Barangay\t";
echo "Address\t";
echo "Case Status\t";
echo "Symptoms\t";
echo "Platelet Count\t";
echo "Reported Date\t";
echo "Notes\n";

// Data rows
while ($row = mysqli_fetch_assoc($result)) {
    // Format symptoms
    $symptoms = [];
    if ($row['fever']) $symptoms[] = 'Fever';
    if ($row['headache']) $symptoms[] = 'Headache';
    if ($row['pain']) $symptoms[] = 'Pain';
    if ($row['rash']) $symptoms[] = 'Rash';
    if ($row['bleeding']) $symptoms[] = 'Bleeding';
    if ($row['nausea']) $symptoms[] = 'Nausea';
    $symptoms_str = implode(', ', $symptoms);

    echo $row['id'] . "\t";
    echo $row['patient_name'] . "\t";
    echo $row['patient_age'] . "\t";
    echo $row['gender'] . "\t";
    echo $row['barangay'] . "\t";
    echo $row['address'] . "\t";
    echo $row['case_status'] . "\t";
    echo $symptoms_str . "\t";
    echo $row['platelet_count'] . "\t";
    echo date('Y-m-d', strtotime($row['reported_date'])) . "\t";
    echo $row['notes'] . "\n";
}

exit; 