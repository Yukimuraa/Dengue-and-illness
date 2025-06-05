<?php
// Clean input data
function clean($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Get all barangays
function getBarangays() {
    return [
        'San Fernando',
        'Matab-ang',
        'Dos Hermanas',
        'Concepcion',
        'Zone 12',
        'Cabatangan',
        'Efigenio Lizares'
    ];
}

// Get dengue case status options
function getDengueStatusOptions() {
    return [
        'suspected' => 'Suspected',
        'probable' => 'Probable',
        'confirmed' => 'Confirmed',
        'recovered' => 'Recovered'
    ];
}

// Get illness type options
function getIllnessTypeOptions() {
    return [
        'influenza' => 'Influenza',
        'respiratory' => 'Respiratory Infection',
        'diarrhea' => 'Diarrhea',
        'typhoid' => 'Typhoid Fever',
        'measles' => 'Measles',
        'other' => 'Other'
    ];
}

// Get illness status options
function getIllnessStatusOptions() {
    return [
        'active' => 'Active',
        'under-treatment' => 'Under Treatment',
        'recovered' => 'Recovered',
        'chronic' => 'Chronic'
    ];
}

// Format date for display
function formatDate($date) {
    return date('F d, Y', strtotime($date));
}

// Get total dengue cases
function getTotalDengueCases() {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM dengue_cases";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// // Get total illness cases
// function getTotalIllnessCases() {
//     global $conn;
//     $sql = "SELECT COUNT(*) as total FROM illness_cases";
//     $result = mysqli_query($conn, $sql);
//     $row = mysqli_fetch_assoc($result);
//     return $row['total'];
// }

// Get dengue cases by barangay
function getDengueCasesByBarangay() {
    global $conn;
    $sql = "SELECT barangay, COUNT(*) as count FROM dengue_cases GROUP BY barangay ORDER BY count DESC";
    $result = mysqli_query($conn, $sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

// // Get illness cases by type
// function getIllnessCasesByType() {
//     global $conn;
//     $sql = "SELECT illness_type, COUNT(*) as count FROM illness_cases GROUP BY illness_type ORDER BY count DESC";
//     $result = mysqli_query($conn, $sql);
//     $data = [];
//     while ($row = mysqli_fetch_assoc($result)) {
//         $data[] = $row;
//     }
//     return $data;
// }

// Get monthly dengue cases for current year
function getMonthlyDengueCases() {
    global $conn;
    $year = date('Y');
    $sql = "SELECT MONTH(reported_date) as month, COUNT(*) as count 
            FROM dengue_cases 
            WHERE YEAR(reported_date) = '$year'
            GROUP BY MONTH(reported_date)
            ORDER BY month";
    $result = mysqli_query($conn, $sql);
    $data = array_fill(1, 12, 0); // Initialize all months with 0
    while ($row = mysqli_fetch_assoc($result)) {
        $data[$row['month']] = (int)$row['count'];
    }
    return $data;
}

// Get user by ID
function getUserById($id) {
    global $conn;
    $id = (int)$id;
    $sql = "SELECT * FROM users WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result);
}

// Check if user has admin role
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}





// Generate status badge HTML
function getStatusBadge($status, $type = 'dengue') {
    $class = '';
    
    if ($type === 'dengue') {
        switch ($status) {
            case 'confirmed':
                $class = 'badge-danger';
                break;
            case 'suspected':
                $class = 'badge-warning';
                break;
            case 'probable':
                $class = 'badge-info';
                break;
            case 'recovered':
                $class = 'badge-success';
                break;
            default:
                $class = 'badge-secondary';
        }
    } else {
        switch ($status) {
            case 'active':
                $class = 'badge-danger';
                break;
            case 'under-treatment':
                $class = 'badge-warning';
                break;
            case 'recovered':
                $class = 'badge-success';
                break;
            case 'chronic':
                $class = 'badge-info';
                break;
            default:
                $class = 'badge-secondary';
        }
    }
    
    return '<span class="badge ' . $class . '">' . ucfirst(str_replace('-', ' ', $status)) . '</span>';
}

// Check if user has super admin role
function isSuperAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'super_admin';
}

// Get user roles for dropdown
function getUserRoles() {
    if (isSuperAdmin()) {
        return [
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'health_officer' => 'Health Officer',
            'health_worker' => 'Health Worker'
        ];
    } elseif (isAdmin()) {
        return [
            'health_officer' => 'Health Officer',
            'health_worker' => 'Health Worker'
        ];
    } else {
        return [
            'health_worker' => 'Health Worker'
        ];
    }
}

// Get all users
function getAllUsers() {
    global $conn;
    $sql = "SELECT * FROM users ORDER BY id DESC";
    $result = mysqli_query($conn, $sql);
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    return $users;
}

// Get user count by role
function getUserCountByRole($role) {
    global $conn;
    $role = clean($role);
    $sql = "SELECT COUNT(*) as count FROM users WHERE role = '$role'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}


  



?>
<?php
// Check if user is logged in
function check_login() {
    // For now, we'll just return true to bypass login check
    // In a real application, you would check session variables
    return true;
}

// Format date
function format_date($date) {
    return date('M d, Y', strtotime($date));
}

// Get status badge class
function get_status_badge($status) {
    switch ($status) {
        case 'Active':
            return 'badge-danger';
        case 'Under Treatment':
            return 'badge-warning';
        case 'Recovered':
            return 'badge-success';
        case 'Chronic':
            return 'badge-secondary';
        default:
            return 'badge-info';
    }
}

// Sanitize input
function sanitize($data) {
    global $conn;
    return $conn->real_escape_string(trim($data));
}
// Get total illness cases
function getTotalIllnessCases() {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM illnesses";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Get illness cases by type
function getIllnessCasesByType() {
    global $conn;
    $sql = "SELECT illness_type, COUNT(*) as count FROM illnesses GROUP BY illness_type ORDER BY count DESC";
    $result = mysqli_query($conn, $sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

?>




