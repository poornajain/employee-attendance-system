<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Attendance System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<div class="wrapper">

    <!-- ===== SIDEBAR ===== -->
    <nav id="sidebar" class="sidebar">

        <div class="sidebar-brand">
            <i class="bi bi-person-badge-fill"></i>
            <span>AttendMS</span>
        </div>

        <ul class="sidebar-nav">
            <li class="sidebar-item">
                <a href="index.php" class="sidebar-link <?= $current_page === 'index' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="employees.php" class="sidebar-link <?= $current_page === 'employees' ? 'active' : '' ?>">
                    <i class="bi bi-people-fill"></i>
                    <span>Employees</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="attendance.php" class="sidebar-link <?= $current_page === 'attendance' ? 'active' : '' ?>">
                    <i class="bi bi-calendar-check-fill"></i>
                    <span>Attendance</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="report.php" class="sidebar-link <?= $current_page === 'report' ? 'active' : '' ?>">
                    <i class="bi bi-bar-chart-fill"></i>
                    <span>Monthly Report</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <i class="bi bi-circle-fill text-success me-1" style="font-size:8px;"></i>
            <span class="text-muted small">System Online</span>
        </div>

    </nav>
    <!-- ===== END SIDEBAR ===== -->


    <!-- ===== PAGE CONTENT ===== -->
    <div id="content">

        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="container-fluid d-flex align-items-center">

                <!-- Sidebar Toggle Button -->
                <button type="button" id="sidebarCollapse" class="sidebar-toggle-btn btn btn-sm">
                    <i class="bi bi-list fs-5"></i>
                </button>

                <!-- Right Side: Date + Avatar -->
                <div class="ms-auto d-flex align-items-center gap-3">
                    <span class="text-muted small d-none d-md-inline">
                        <i class="bi bi-calendar3 me-1"></i>
                        <?= date('D, d M Y') ?>
                    </span>
                    <div class="avatar-circle">
                        <i class="bi bi-person-fill"></i>
                    </div>
                </div>

            </div>
        </nav>
        <!-- End Top Navbar -->

        <!-- Main Content Area starts here -->
        <main class="main-content">