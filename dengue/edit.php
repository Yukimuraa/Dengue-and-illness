<?php
$page_title = "Edit Dengue Case";
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireLogin();

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "Invalid dengue case ID";
    $_SESSION['message_type'] = "danger";
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clean and validate input
    $patient_name = clean($_POST['patient_name']);
    $patient_age = (int)$_POST['patient_age'];
    $gender = isset($_POST['gender']) ? clean($_POST['gender']) : 'male';
    $barangay = clean($_POST['barangay']);
    $address = clean($_POST['address']);
    $case_status = clean($_POST['case_status']);
    $platelet_count = isset($_POST['platelet_count']) ? clean($_POST['platelet_count']) : '';
    $notes = isset($_POST['notes']) ? clean($_POST['notes']) : '';
    $reported_date = clean($_POST['reported_date']);
    
    // Process checkboxes
    $fever = isset($_POST['fever']) ? 1 : 0;
    $headache = isset($_POST['headache']) ? 1 : 0;
    $pain = isset($_POST['pain']) ? 1 : 0;
    $rash = isset($_POST['rash']) ? 1 : 0;
    $bleeding = isset($_POST['bleeding']) ? 1 : 0;
    $nausea = isset($_POST['nausea']) ? 1 : 0;

    // Validate required fields
    if (empty($patient_name) || empty($barangay) || empty($case_status)) {
        $_SESSION['message'] = "Please fill in all required fields";
        $_SESSION['message_type'] = "danger";
    } else {
        // Update the record
        $sql = "UPDATE dengue_cases SET 
                patient_name = ?, 
                patient_age = ?, 
                gender = ?, 
                barangay = ?, 
                address = ?, 
                case_status = ?, 
                fever = ?,
                headache = ?,
                pain = ?,
                rash = ?,
                bleeding = ?,
                nausea = ?,
                platelet_count = ?,
                notes = ?,
                reported_date = ?
                WHERE id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'sissssiiiiissssi', 
                $patient_name, 
                $patient_age, 
                $gender, 
                $barangay, 
                $address, 
                $case_status, 
                $fever, 
                $headache, 
                $pain,
                $rash, 
                $bleeding, 
                $nausea, 
                $platelet_count, 
                $notes,
                $reported_date, 
                $id
            );
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = "Dengue case updated successfully";
                $_SESSION['message_type'] = "success";
                header("Location: index.php");
                exit;
            } else {
                $_SESSION['message'] = "Error updating dengue case: " . mysqli_error($conn);
                $_SESSION['message_type'] = "danger";
            }
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['message'] = "Error preparing statement: " . mysqli_error($conn);
            $_SESSION['message_type'] = "danger";
        }
    }
}

// Get the current record
$sql = "SELECT * FROM dengue_cases WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    $_SESSION['message'] = "Dengue case not found";
    $_SESSION['message_type'] = "danger";
    header("Location: index.php");
    exit;
}

$case = mysqli_fetch_assoc($result);

// Set default values for fields that might be NULL
$case['gender'] = $case['gender'] ?? 'male';
$case['address'] = $case['address'] ?? '';
$case['platelet_count'] = $case['platelet_count'] ?? '';
$case['notes'] = $case['notes'] ?? '';
$case['reported_date'] = $case['reported_date'] ?? date('Y-m-d');
$case['fever'] = $case['fever'] ?? 0;
$case['headache'] = $case['headache'] ?? 0;
$case['pain'] = $case['pain'] ?? 0;
$case['rash'] = $case['rash'] ?? 0;
$case['bleeding'] = $case['bleeding'] ?? 0;
$case['nausea'] = $case['nausea'] ?? 0;

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1 class="h3 mb-0 text-gray-800">Edit Dengue Case</h1>
        <p class="text-muted">Update dengue case information</p>
    </div>
    <div class="col-md-6 text-md-right">
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <form method="post" action="">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="patient_name">Patient Name *</label>
                        <input type="text" class="form-control" id="patient_name" name="patient_name" 
                               value="<?php echo htmlspecialchars($case['patient_name']); ?>" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="patient_age">Age</label>
                        <input type="number" class="form-control" id="patient_age" name="patient_age" 
                               value="<?php echo $case['patient_age']; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Gender</label>
                        <div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="gender_male" name="gender" value="male" 
                                       class="custom-control-input" <?php echo $case['gender'] === 'male' ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="gender_male">Male</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="gender_female" name="gender" value="female" 
                                       class="custom-control-input" <?php echo $case['gender'] === 'female' ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="gender_female">Female</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="reported_date">Date Reported *</label>
                        <input type="date" class="form-control" id="reported_date" name="reported_date" 
                               value="<?php echo $case['reported_date']; ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="barangay">Barangay *</label>
                        <select class="form-control" id="barangay" name="barangay" required>
                            <option value="">Select Barangay</option>
                            <?php foreach (getBarangays() as $barangay): ?>
                                <option value="<?php echo $barangay; ?>" <?php echo $case['barangay'] === $barangay ? 'selected' : ''; ?>>
                                    <?php echo $barangay; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="case_status">Case Status *</label>
                        <select class="form-control" id="case_status" name="case_status" required>
                            <?php foreach (getDengueStatusOptions() as $value => $label): ?>
                                <option value="<?php echo $value; ?>" <?php echo $case['case_status'] === $value ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="address">Complete Address</label>
                <input type="text" class="form-control" id="address" name="address" 
                       value="<?php echo htmlspecialchars($case['address']); ?>">
            </div>

            <div class="form-group">
                <label>Symptoms</label>
                <div class="row">
                    <div class="col-md-4">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="fever" name="fever" value="1"
                                   <?php echo $case['fever'] ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="fever">High Fever</label>
                        </div>
                        <div class="custom-control custom-checkbox mt-2">
                            <input type="checkbox" class="custom-control-input" id="headache" name="headache" value="1"
                                   <?php echo $case['headache'] ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="headache">Severe Headache</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="pain" name="pain" value="1"
                                   <?php echo $case['pain'] ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="pain">Eye/Muscle/Joint Pain</label>
                        </div>
                        <div class="custom-control custom-checkbox mt-2">
                            <input type="checkbox" class="custom-control-input" id="rash" name="rash" value="1"
                                   <?php echo $case['rash'] ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="rash">Rash</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="bleeding" name="bleeding" value="1"
                                   <?php echo $case['bleeding'] ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="bleeding">Mild Bleeding</label>
                        </div>
                        <div class="custom-control custom-checkbox mt-2">
                            <input type="checkbox" class="custom-control-input" id="nausea" name="nausea" value="1"
                                   <?php echo $case['nausea'] ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="nausea">Nausea/Vomiting</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="platelet_count">Platelet Count (if available)</label>
                <input type="text" class="form-control" id="platelet_count" name="platelet_count" 
                       value="<?php echo htmlspecialchars($case['platelet_count']); ?>" placeholder="e.g., 120,000">
            </div>

            <div class="form-group">
                <label for="notes">Additional Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($case['notes']); ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Update Case
                    </button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 