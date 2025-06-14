<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo $site_name; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $site_url; ?>/assets/css/style.css">
</head>
<body>
    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo $site_url; ?>/dashboard.php">
                <i class="fas fa-heartbeat mr-2"></i>
                <?php echo $site_name; ?>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item <?php echo ($page_title == 'Dashboard') ? 'active' : ''; ?>">
                        <a class="nav-link" href="<?php echo $site_url; ?>/dashboard.php">
                            <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                        </a>
                    </li>
                    
                    <li class="nav-item dropdown <?php echo (strpos($_SERVER['PHP_SELF'], '/dengue/') !== false) ? 'active' : ''; ?>">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownDengue" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-mosquito mr-1"></i> Dengue Cases
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownDengue">
                            <a class="dropdown-item <?php echo ($_SERVER['PHP_SELF'] == '/dengue/index.php') ? 'active' : ''; ?>" href="<?php echo $site_url; ?>/dengue/index.php">All Cases</a>
                            <a class="dropdown-item <?php echo ($_SERVER['PHP_SELF'] == '/dengue/add.php') ? 'active' : ''; ?>" href="<?php echo $site_url; ?>/dengue/add.php">Add New Case</a>
                            <a class="dropdown-item <?php echo ($_SERVER['PHP_SELF'] == '/dengue/reports.php') ? 'active' : ''; ?>" href="<?php echo $site_url; ?>/dengue/reports.php">Reports</a>
                        </div>
                    </li>
                    
                    <li class="nav-item dropdown <?php echo (strpos($_SERVER['PHP_SELF'], '/illness/') !== false) ? 'active' : ''; ?>">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownIllness" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-virus mr-1"></i> Illness Cases
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownIllness">
                        
<a class="dropdown-item <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/illness/') !== false) ? 'active' : ''; ?>" href="<?php echo $site_url; ?>/illness/index.php">All Cases</a>
<a class="dropdown-item <?php echo (basename($_SERVER['PHP_SELF']) == 'add.php' && strpos($_SERVER['PHP_SELF'], '/illness/') !== false) ? 'active' : ''; ?>" href="<?php echo $site_url; ?>/illness/add.php">Add New Case</a>
<a class="dropdown-item <?php echo (basename($_SERVER['PHP_SELF']) == 'analytics.php' && strpos($_SERVER['PHP_SELF'], '/illness/') !== false) ? 'active' : ''; ?>" href="<?php echo $site_url; ?>/illness/analytics.php">Analytics</a>
<a class="dropdown-item <?php echo (basename($_SERVER['PHP_SELF']) == 'reports.php' && strpos($_SERVER['PHP_SELF'], '/illness/') !== false) ? 'active' : ''; ?>" href="<?php echo $site_url; ?>/illness/reports.php">Reports</a>
                        </div>
                    </li>
<!--                     
                    <li class="nav-item <?php echo (strpos($_SERVER['PHP_SELF'], '/reports/') !== false) ? 'active' : ''; ?>">
                        <a class="nav-link" href="<?php echo $site_url; ?>/reports/index.php">
                            <i class="fas fa-chart-bar mr-1"></i> Reports & Analytics
                        </a>
                    </li> -->
                    
                    <!-- <li class="nav-item <?php echo (strpos($_SERVER['PHP_SELF'], '/alerts/') !== false) ? 'active' : ''; ?>">
                        <a class="nav-link" href="<?php echo $site_url; ?>/alerts/index.php">
                            <i class="fas fa-bell mr-1"></i> Alerts
                        </a>
                    </li>
                     -->
                    <?php if ($_SESSION['user_role'] == 'admin'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdmin" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-cog mr-1"></i> Administration
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownAdmin">
                            <a class="dropdown-item <?php echo ($_SERVER['PHP_SELF'] == '/users/index.php') ? 'active' : ''; ?>" href="<?php echo $site_url; ?>/users/index.php">User Management</a>
                            <!-- <a class="dropdown-item <?php echo ($_SERVER['PHP_SELF'] == '/settings/index.php') ? 'active' : ''; ?>" href="<?php echo $site_url; ?>/settings/index.php">System Settings</a> -->
                        </div>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownNotifications" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <span class="badge badge-danger badge-counter">3+</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownNotifications">
                            <h6 class="dropdown-header">Notifications</h6>
                            <a class="dropdown-item d-flex align-items-center" href="#">
                                <div class="mr-3">
                                    <div class="icon-circle bg-primary">
                                        <i class="fas fa-file-alt text-white"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="small text-gray-500">Today</div>
                                    <span>New dengue case reported in San Fernando</span>
                                </div>
                            </a>
                            <a class="dropdown-item d-flex align-items-center" href="#">
                                <div class="mr-3">
                                    <div class="icon-circle bg-warning">
                                        <i class="fas fa-exclamation-triangle text-white"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="small text-gray-500">Yesterday</div>
                                    Dengue cases in Zone 12 have exceeded threshold
                                </div>
                            </a>
                            <a class="dropdown-item text-center small text-gray-500" href="<?php echo $site_url; ?>/alerts/index.php">Show All Alerts</a>
                        </div>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUser" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle mr-1"></i> <?php echo $_SESSION['full_name']; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownUser">
                            <a class="dropdown-item" href="<?php echo $site_url; ?>/profile.php">
                                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i> Profile
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo $site_url; ?>/logout.php">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <div class="container-fluid py-4">