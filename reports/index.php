<?php
$page_title = "Reports";
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireLogin();

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1 class="h3 mb-0 text-gray-800">Reports & Analytics</h1>
        <p class="text-muted">Generate and view reports for health data analysis</p>
    </div>
    <div class="col-md-6 text-md-right">
        <p class="mb-0">
            <i class="fas fa-calendar-day mr-1"></i>
            <?php echo date('F d, Y'); ?>
        </p>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Dengue Report</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Dengue Cases Analysis</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="<?php echo $site_url; ?>/reports/dengue_report.php" class="btn btn-danger btn-sm btn-block">
                    <i class="fas fa-file-pdf mr-1"></i> Generate Report
                </a>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Illness Report</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Illness Summary</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="<?php echo $site_url; ?>/reports/illness_report.php" class="btn btn-primary btn-sm btn-block">
                    <i class="fas fa-file-pdf mr-1"></i> Generate Report
                </a>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Barangay Health</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Barangay-specific</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-map-marker-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <button type="button" class="btn btn-success btn-sm btn-block" data-toggle="modal" data-target="#barangayReportModal">
                    <i class="fas fa-file-pdf mr-1"></i> Generate Report
                </button>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Executive Report</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Health Overview</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="<?php echo $site_url; ?>/reports/executive_report.php" class="btn btn-info btn-sm btn-block">
                    <i class="fas fa-file-pdf mr-1"></i> Generate Report
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Custom Report Generator</h6>
    </div>
    <div class="card-body">
        <form method="post" action="<?php echo $site_url; ?>/reports/custom_report.php">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="report_type">Report Type</label>
                        <select class="form-control" id="report_type" name="report_type" required>
                            <option value="">Select Report Type</option>
                            <option value="dengue">Dengue Analysis</option>
                            <option value="illness">General Illness</option>
                            <option value="barangay">Barangay Health</option>
                            <option value="outbreak">Outbreak Risk</option>
                            <option value="resource">Resource Allocation</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="date_range">Date Range</label>
                        <select class="form-control" id="date_range" name="date_range" required>
                            <option value="">Select Date Range</option>
                            <option value="7">Last 7 Days</option>
                            <option value="30">Last 30 Days</option>
                            <option value="90">Last Quarter</option>
                            <option value="365">This Year</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="format">Format</label>
                        <select class="form-control" id="format" name="format" required>
                            <option value="">Select Format</option>
                            <option value="pdf">PDF Document</option>
                            <option value="excel">Excel Spreadsheet</option>
                            <option value="csv">CSV Data</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div id="custom_date_range" class="row mt-3" style="display: none;">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date">
                    </div>
                </div>
            </div>
            
            <div class="text-right mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-file-download mr-1"></i> Generate Report
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Saved Reports</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Report Name</th>
                                <th>Type</th>
                                <th>Generated Date</th>
                                <th>Format</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>August 2023 Dengue Report</td>
                                <td>Dengue Analysis</td>
                                <td>Aug 31, 2023</td>
                                <td>PDF</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-primary">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>Q2 2023 Barangay Health Status</td>
                                <td>Barangay Health</td>
                                <td>Jul 5, 2023</td>
                                <td>Excel</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-primary">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>San Fernando Illness Analytics</td>
                                <td>Barangay Health</td>
                                <td>Jun 15, 2023</td>
                                <td>PDF</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-primary">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Barangay Report Modal -->
<div class="modal fade" id="barangayReportModal" tabindex="-1" role="dialog" aria-labelledby="barangayReportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="barangayReportModalLabel">Generate Barangay Report</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="<?php echo $site_url; ?>/reports/barangay_report.php">
                    <div class="form-group">
                        <label for="barangay">Select Barangay</label>
                        <select class="form-control" id="barangay" name="barangay" required>
                            <option value="">Select Barangay</option>
                            <?php foreach (getBarangays() as $barangay): ?>
                                <option value="<?php echo $barangay; ?>"><?php echo $barangay; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="report_period">Report Period</label>
                        <select class="form-control" id="report_period" name="report_period" required>
                            <option value="30">Last 30 Days</option>
                            <option value="90">Last Quarter</option>
                            <option value="180">Last 6 Months</option>
                            <option value="365">Last Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="report_format">Format</label>
                        <select class="form-control" id="report_format" name="report_format" required>
                            <option value="pdf">PDF Document</option>
                            <option value="excel">Excel Spreadsheet</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-file-pdf mr-1"></i> Generate Report
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Show/hide custom date range based on selection
    document.getElementById('date_range').addEventListener('change', function() {
        var customDateRange = document.getElementById('custom_date_range');
        if (this.value === 'custom') {
            customDateRange.style.display = 'flex';
        } else {
            customDateRange.style.display = 'none';
        }
    });
</script>

<?php include '../includes/footer.php'; ?>