<?php
$page_title = "Add Dengue Case";
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireLogin();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clean and validate input
    $patient_name = clean($_POST['patient_name']);
    $patient_age = (int)$_POST['patient_age'];
    $gender = clean($_POST['gender']);
    $reported_date = clean($_POST['reported_date']);
    $barangay = clean($_POST['barangay']);
    $address = clean($_POST['address']);
    $case_status = clean($_POST['case_status']);
    $platelet_count = clean($_POST['platelet_count']);
    $notes = clean($_POST['notes']);
    
    // Process checkboxes
    $fever = isset($_POST['fever']) ? 1 : 0;
    $headache = isset($_POST['headache']) ? 1 : 0;
    $pain = isset($_POST['pain']) ? 1 : 0;
    $rash = isset($_POST['rash']) ? 1 : 0;
    $bleeding = isset($_POST['bleeding']) ? 1 : 0;
    $nausea = isset($_POST['nausea']) ? 1 : 0;
    
    // Validate required fields
    if (empty($patient_name) || empty($reported_date) || empty($barangay)) {
        $_SESSION['message'] = "Please fill in all required fields.";
        $_SESSION['message_type'] = "danger";
    } else {
        // Insert into database
        $sql = "INSERT INTO dengue_cases (
                    patient_name, patient_age, gender, reported_date, 
                    barangay, address, case_status, fever, headache, 
                    pain, rash, bleeding, nausea, platelet_count, notes
                ) VALUES (
                    '$patient_name', $patient_age, '$gender', '$reported_date', 
                    '$barangay', '$address', '$case_status', $fever, $headache, 
                    $pain, $rash, $bleeding, $nausea, '$platelet_count', '$notes'
                )";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['message'] = "Dengue case added successfully.";
            $_SESSION['message_type'] = "success";
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['message'] = "Error: " . mysqli_error($conn);
            $_SESSION['message_type'] = "danger";
        }
    }
}

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1 class="h3 mb-0 text-gray-800">Add New Dengue Case</h1>
    </div>
    <div class="col-md-6 text-md-right">
        <a href="<?php echo $site_url; ?>/dengue/index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Case Information</h6>
    </div>
    <div class="card-body">
        <form method="post" action="">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="patient_name">Patient Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="patient_name" name="patient_name" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="patient_age">Age <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="patient_age" name="patient_age" min="0" max="120" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Gender <span class="text-danger">*</span></label>
                        <div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="gender_male" name="gender" value="male" class="custom-control-input" checked>
                                <label class="custom-control-label" for="gender_male">Male</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="gender_female" name="gender" value="female" class="custom-control-input">
                                <label class="custom-control-label" for="gender_female">Female</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="reported_date">Date Reported <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="reported_date" name="reported_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="barangay">Barangay <span class="text-danger">*</span></label>
                        <select class="form-control" id="barangay" name="barangay" required>
                            <option value="">Select Barangay</option>
                            <?php foreach (getBarangays() as $barangay): ?>
                                <option value="<?php echo $barangay; ?>"><?php echo $barangay; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="case_status">Case Status <span class="text-danger">*</span></label>
                        <select class="form-control" id="case_status" name="case_status" required>
                            <option value="">Select Status</option>
                            <?php foreach (getDengueStatusOptions() as $value => $label): ?>
                                <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="address">Complete Address</label>
                <input type="text" class="form-control" id="address" name="address">
            </div>
            
            <div class="form-group">
                <label>Symptoms</label>
                <div class="row">
                    <div class="col-md-4">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="fever" name="fever" value="1">
                            <label class="custom-control-label" for="fever">High Fever</label>
                        </div>
                        <div class="custom-control custom-checkbox mt-2">
                            <input type="checkbox" class="custom-control-input" id="headache" name="headache" value="1">
                            <label class="custom-control-label" for="headache">Severe Headache</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="pain" name="pain" value="1">
                            <label class="custom-control-label" for="pain">Eye/Muscle/Joint Pain</label>
                        </div>
                        <div class="custom-control custom-checkbox mt-2">
                            <input type="checkbox" class="custom-control-input" id="rash" name="rash" value="1">
                            <label class="custom-control-label" for="rash">Rash</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="bleeding" name="bleeding" value="1">
                            <label class="custom-control-label" for="bleeding">Mild Bleeding</label>
                        </div>
                        <div class="custom-control custom-checkbox mt-2">
                            <input type="checkbox" class="custom-control-input" id="nausea" name="nausea" value="1">
                            <label class="custom-control-label" for="nausea">Nausea/Vomiting</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="platelet_count">Platelet Count (if available)</label>
                <input type="text" class="form-control" id="platelet_count" name="platelet_count" placeholder="e.g., 120,000">
            </div>
            
            <div class="form-group">
                <label for="notes">Additional Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Save Case
            </button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>