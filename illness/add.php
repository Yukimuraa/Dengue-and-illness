<?php
ob_start();
// Initialize the session and include necessary files
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
// check_login();

// Set page title
$page_title = "Add Illness Case";
include '../includes/header.php';

// Get barangays for dropdown
$barangays_query = "SELECT id, barangay_name FROM barangays ORDER BY barangay_name";
$barangays_result = $conn->query($barangays_query);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input
    $errors = [];
    
    // Patient information
    $patient_name = trim($_POST['patient_name']);
    $patient_age = trim($_POST['patient_age']);
    $patient_gender = trim($_POST['patient_gender']);
    $barangay_id = trim($_POST['barangay_id']);
    $address = trim($_POST['address']);
    
    // Illness information
    $illness_type = trim($_POST['illness_type']);
    $symptoms = trim($_POST['symptoms']);
    $status = trim($_POST['status']);
    $reported_date = trim($_POST['reported_date']);
    $notes = trim($_POST['notes']);
    
    // Validate required fields
    if (empty($patient_name)) $errors[] = "Patient name is required";
    if (empty($patient_age) || !is_numeric($patient_age)) $errors[] = "Valid patient age is required";
    if (empty($patient_gender)) $errors[] = "Patient gender is required";
    if (empty($barangay_id)) $errors[] = "Barangay is required";
    if (empty($illness_type)) $errors[] = "Illness type is required";
    if (empty($status)) $errors[] = "Status is required";
    if (empty($reported_date)) $errors[] = "Reported date is required";
    
    // If no errors, proceed with saving
    if (empty($errors)) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Check if patient already exists
            $check_patient = $conn->prepare("SELECT id FROM patients WHERE full_name = ? AND age = ? AND gender = ?");
            $check_patient->bind_param("sis", $patient_name, $patient_age, $patient_gender);
            $check_patient->execute();
            $patient_result = $check_patient->get_result();
            
            if ($patient_result->num_rows > 0) {
                // Patient exists, get ID
                $patient_row = $patient_result->fetch_assoc();
                $patient_id = $patient_row['id'];
            } else {
                // Insert new patient
                $insert_patient = $conn->prepare("INSERT INTO patients (full_name, age, gender, barangay_id, address) VALUES (?, ?, ?, ?, ?)");
                $insert_patient->bind_param("sisss", $patient_name, $patient_age, $patient_gender, $barangay_id, $address);
                $insert_patient->execute();
                $patient_id = $conn->insert_id;
            }
            
            // Generate case ID
            $year = date('Y');
            $month = date('m');
            $case_prefix = "ILL-" . $year . $month . "-";
            
            // Get the latest case number
            $latest_case = $conn->query("SELECT case_id FROM illnesses WHERE case_id LIKE '$case_prefix%' ORDER BY id DESC LIMIT 1");
            
            if ($latest_case->num_rows > 0) {
                $latest_id = $latest_case->fetch_assoc()['case_id'];
                $latest_number = intval(substr($latest_id, -3));
                $new_number = $latest_number + 1;
            } else {
                $new_number = 1;
            }
            
            $case_id = $case_prefix . str_pad($new_number, 3, "0", STR_PAD_LEFT);
            
            // Insert illness case
            $insert_illness = $conn->prepare("INSERT INTO illnesses (case_id, patient_id, illness_type, symptoms, status, reported_date, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_illness->bind_param("sisssssi", $case_id, $patient_id, $illness_type, $symptoms, $status, $reported_date, $notes, $_SESSION['user_id']);
            $insert_illness->execute();
            
            // Commit transaction
            $conn->commit();
            
            // Set success message and redirect
            $_SESSION['success_msg'] = "Illness case added successfully!";
            header("Location: index.php");
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Add New Illness Case</h1>
    <p class="mb-4">Enter information about a new illness case.</p>

    <!-- Display errors if any -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Case Information</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3">Patient Information</h5>
                        
                        <div class="form-group">
                            <label for="patient_name">Patient Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="patient_name" name="patient_name" value="<?php echo isset($patient_name) ? htmlspecialchars($patient_name) : ''; ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="patient_age">Age <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="patient_age" name="patient_age" value="<?php echo isset($patient_age) ? htmlspecialchars($patient_age) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="patient_gender">Gender <span class="text-danger">*</span></label>
                                <select class="form-control" id="patient_gender" name="patient_gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php if (isset($patient_gender) && $patient_gender == 'Male') echo 'selected'; ?>>Male</option>
                                    <option value="Female" <?php if (isset($patient_gender) && $patient_gender == 'Female') echo 'selected'; ?>>Female</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="barangay_id">Barangay <span class="text-danger">*</span></label>
                            <select class="form-control" id="barangay_id" name="barangay_id" required>
                                <option value="">Select Barangay</option>
                                <?php while ($barangay = $barangays_result->fetch_assoc()): ?>
                                    <option value="<?php echo $barangay['id']; ?>" <?php if (isset($barangay_id) && $barangay_id == $barangay['id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($barangay['barangay_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Complete Address</label>
                            <input type="text" class="form-control" id="address" name="address" value="<?php echo isset($address) ? htmlspecialchars($address) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h5 class="mb-3">Illness Information</h5>
                        
                        <div class="form-group">
                            <label for="illness_type">Illness Type <span class="text-danger">*</span></label>
                            <select class="form-control" id="illness_type" name="illness_type" required>
                                <option value="">Select Illness Type</option>
                                <option value="Influenza" <?php if (isset($illness_type) && $illness_type == 'Influenza') echo 'selected'; ?>>Influenza</option>
                                <option value="Respiratory Infection" <?php if (isset($illness_type) && $illness_type == 'Respiratory Infection') echo 'selected'; ?>>Respiratory Infection</option>
                                <option value="Diarrhea" <?php if (isset($illness_type) && $illness_type == 'Diarrhea') echo 'selected'; ?>>Diarrhea</option>
                                <option value="Typhoid Fever" <?php if (isset($illness_type) && $illness_type == 'Typhoid Fever') echo 'selected'; ?>>Typhoid Fever</option>
                                <option value="Measles" <?php if (isset($illness_type) && $illness_type == 'Measles') echo 'selected'; ?>>Measles</option>
                                <option value="Tuberculosis" <?php if (isset($illness_type) && $illness_type == 'Tuberculosis') echo 'selected'; ?>>Tuberculosis</option>
                                <option value="Malaria" <?php if (isset($illness_type) && $illness_type == 'Malaria') echo 'selected'; ?>>Malaria</option>
                                <option value="Other" <?php if (isset($illness_type) && $illness_type == 'Other') echo 'selected'; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="symptoms">Symptoms</label>
                            <textarea class="form-control" id="symptoms" name="symptoms" rows="3"><?php echo isset($symptoms) ? htmlspecialchars($symptoms) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="">Select Status</option>
                                <option value="Active" <?php if (isset($status) && $status == 'Active') echo 'selected'; ?>>Active</option>
                                <option value="Under Treatment" <?php if (isset($status) && $status == 'Under Treatment') echo 'selected'; ?>>Under Treatment</option>
                                <option value="Recovered" <?php if (isset($status) && $status == 'Recovered') echo 'selected'; ?>>Recovered</option>
                                <option value="Chronic" <?php if (isset($status) && $status == 'Chronic') echo 'selected'; ?>>Chronic</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="reported_date">Reported Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="reported_date" name="reported_date" value="<?php echo isset($reported_date) ? htmlspecialchars($reported_date) : date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Additional Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo isset($notes) ? htmlspecialchars($notes) : ''; ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 text-center">
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Case</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
ob_end_flush();
include '../includes/footer.php';
?>
