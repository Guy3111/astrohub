<?php
/**
 * admin_header.php
 * เมนูด้านบนและโครงสร้างสำหรับเว็บหลังบ้าน (Admin Panel)
 */
require_once 'admin_check.php';
require_once 'db_connect.php';

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการหลังบ้าน - ASTROHUB Admin</title>
    <!-- Google Fonts (Prompt & Orbitron) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #0b0f19;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .main-content {
            flex: 1 0 auto;
        }
        .brand-logo {
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 1.5px;
            font-weight: 800;
        }
        .navbar-admin {
            background-color: #050811 !important;
            border-bottom: 1px solid rgba(56, 189, 248, 0.2);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }
        .nav-link {
            transition: all 0.2s ease-in-out;
            font-weight: 400;
        }
        .nav-link:hover, .nav-link.active {
            color: #38bdf8 !important;
            font-weight: 600;
        }
        .table-dark-custom {
            --bs-table-bg: #111827;
            --bs-table-striped-bg: #162032;
            --bs-table-hover-bg: #1a273e;
            color: #e2e8f0;
            border-color: rgba(255, 255, 255, 0.08);
        }
        .card-custom {
            background: #111827;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-admin sticky-top">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand brand-logo text-info d-flex align-items-center gap-2" href="admin_dashboard.php">
            <i class="bi bi-shield-lock-fill fs-4 text-warning"></i>
            <span>ASTROHUB <small class="fs-6 text-secondary fw-normal">ADMIN</small></span>
        </a>

        <button class="navbar-toggler border-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-lg-2 ms-lg-3">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'admin_dashboard.php') ? 'active' : '' ?>" href="admin_dashboard.php">
                        <i class="bi bi-speedometer2 me-1"></i>แดชบอร์ด
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'admin_events.php') ? 'active' : '' ?>" href="admin_events.php">
                        <i class="bi bi-calendar-event me-1"></i>จัดการปรากฏการณ์
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'admin_locations.php') ? 'active' : '' ?>" href="admin_locations.php">
                        <i class="bi bi-geo-alt me-1"></i>จัดการพิกัดดูดาว
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'admin_articles.php') ? 'active' : '' ?>" href="admin_articles.php">
                        <i class="bi bi-journal-bookmark me-1"></i>จัดการบทความความรู้
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <a href="index.php" target="_blank" class="btn btn-outline-info btn-sm rounded-pill">
                    <i class="bi bi-box-arrow-up-right me-1"></i>ดูหน้าเว็บ (หน้าบ้าน)
                </a>
                <span class="text-secondary small">
                    <i class="bi bi-person-circle text-info"></i> <?= htmlspecialchars($_SESSION['admin_username'] ?? 'admin') ?>
                </span>
                <a href="logout.php" class="btn btn-danger btn-sm rounded-pill px-3">
                    <i class="bi bi-box-arrow-right me-1"></i>ออกจากระบบ
                </a>
            </div>
        </div>
    </div>
</nav>

<main class="main-content py-4">

