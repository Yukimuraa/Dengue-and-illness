<?php
// Initialize the session and include necessary files
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
check_login();

// Set page title
$page_title = "System Settings";
include '../includes/header.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_illness_type':
                $illness_type = clean($_POST['illness_type']);
                if (!empty($illness_type)) {
                    $sql = "INSERT INTO illness_types (type_name) VALUES (?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $illness_type);
                    if ($stmt->execute()) {
                        $_SESSION['message'] = "Illness type added successfully.";
                        $_SESSION['message_type'] = "success";
                    } else {
                        $_SESSION['message'] = "Error adding illness type.";
                        $_SESSION['message_type'] = "danger";
                    }
                }
                break;

            case 'delete_illness_type':
                $type_id = (int)$_POST['type_id'];
                $sql = "DELETE FROM illness_types WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $type_id);
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Illness type deleted successfully.";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['message'] = "Error deleting illness type.";
                    $_SESSION['message_type'] = "danger";
                }
                break;

            case 'update_settings':
                $settings = [
                    'system_name' => clean($_POST['system_name']),
                    'health_center_name' => clean($_POST['health_center_name']),
                    'health_center_address' => clean($_POST['health_center_address']),
                    'health_center_contact' => clean($_POST['health_center_contact']),
                    'email_notifications' => isset($_POST['email_notifications']) ? 1 : 0,
                    'notification_email' => clean($_POST['notification_email'])
                ];

                foreach ($settings as $key => $value) {
                    $sql = "INSERT INTO system_settings (setting_key, setting_value) 
                            VALUES (?, ?) 
                            ON DUPLICATE KEY UPDATE setting_value = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sss", $key, $value, $value);
                    $stmt->execute();
                }

                $_SESSION['message'] = "Settings updated successfully.";
                $_SESSION['message_type'] = "success";
                break;
        }
    }
}

// Get current settings
$settings_query = "SELECT setting_key, setting_value FROM system_settings";
$settings_result = $conn->query($settings_query);
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Get illness types
$illness_types_query = "SELECT * FROM illness_types ORDER BY type_name";
$illness_types_result = $conn->query($illness_types_query);
?>

<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">System Settings</h1>
    <p class="mb-4">Manage system configurations and illness types.</p>

    <!-- System Information -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">System Information</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_settings">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="system_name">System Name</label>
                            <input type="text" class="form-control" id="system_name" name="system_name" 
                                   value="<?php echo $settings['system_name'] ?? ''; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="health_center_name">Health Center Name</label>
                            <input type="text" class="form-control" id="health_center_name" name="health_center_name" 
                                   value="<?php echo $settings['health_center_name'] ?? ''; ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="health_center_address">Health Center Address</label>
                            <textarea class="form-control" id="health_center_address" name="health_center_address" 
                                      rows="3"><?php echo $settings['health_center_address'] ?? ''; ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="health_center_contact">Contact Number</label>
                            <input type="text" class="form-control" id="health_center_contact" name="health_center_contact" 
                                   value="<?php echo $settings['health_center_contact'] ?? ''; ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="email_notifications" 
                                       name="email_notifications" <?php echo ($settings['email_notifications'] ?? 0) ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="email_notifications">Enable Email Notifications</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="notification_email">Notification Email</label>
                            <input type="email" class="form-control" id="notification_email" name="notification_email" 
                                   value="<?php echo $settings['notification_email'] ?? ''; ?>">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>

    <!-- Illness Types Management -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Illness Types</h6>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addIllnessTypeModal">
                <i class="fas fa-plus"></i> Add New Type
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Illness Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($type = $illness_types_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($type['type_name']); ?></td>
                                <td>
                                    <form method="POST" action="" class="d-inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this illness type?');">
                                        <input type="hidden" name="action" value="delete_illness_type">
                                        <input type="hidden" name="type_id" value="<?php echo $type['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Illness Type Modal -->
<div class="modal fade" id="addIllnessTypeModal" tabindex="-1" role="dialog" aria-labelledby="addIllnessTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addIllnessTypeModalLabel">Add New Illness Type</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_illness_type">
                    <div class="form-group">
                        <label for="illness_type">Illness Type Name</label>
                        <input type="text" class="form-control" id="illness_type" name="illness_type" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 