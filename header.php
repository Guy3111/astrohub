<?php
/**
 * header.php
 * ส่วนหัวของเว็บไซต์ ASTROHUB (หน้าบ้าน - บุคคลทั่วไปเข้าชมได้)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ตรวจสอบว่าแอดมินล็อกอินอยู่หรือไม่ (ใช้สำหรับแสดงปุ่มแผงควบคุมหลังบ้าน)
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// หาชื่อไฟล์ปัจจุบันเพื่อกำหนด active ให้เมนูอัตโนมัติ
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASTROHUB - แพลตฟอร์มดาราศาสตร์และดูดาว</title>
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #0b0f19;
            color: #e2e8f0;
        }
        .main-content {
            flex: 1 0 auto;
        }
        .brand-logo {
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 1.5px;
            font-weight: 800;
        }
        .navbar-custom {
            background-color: #080b12 !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
        }
        .nav-link {
            transition: all 0.2s ease-in-out;
            font-weight: 400;
        }
        .nav-link:hover {
            color: #38bdf8 !important;
            transform: translateY(-1px);
        }
        .nav-link.active {
            color: #38bdf8 !important;
            font-weight: 600;
        }
    </style>
</head>
<body>

<!-- Navbar Header -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand brand-logo text-info d-flex align-items-center gap-2" href="index.php">
            <i class="bi bi-stars fs-4"></i>
            <span>ASTROHUB</span>
        </a>

        <!-- Hamburger Toggle for Mobile -->
        <button class="navbar-toggler border-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#astroNavbar" aria-controls="astroNavbar" aria-expanded="false" aria-label="สลับแถบเมนู">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links (6 หน้าหลักหน้าบ้าน) -->
        <div class="collapse navbar-collapse" id="astroNavbar">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-lg-2">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'index.php' || $current_page == '') ? 'active' : '' ?>" href="index.php">
                        <i class="bi bi-house-door me-1"></i>หน้าแรก
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'calendar.php') ? 'active' : '' ?>" href="calendar.php">
                        <i class="bi bi-calendar-event me-1"></i>ปฏิทิน
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'locations.php') ? 'active' : '' ?>" href="locations.php">
                        <i class="bi bi-geo-alt me-1"></i>พิกัดชมดาว
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'knowledge.php') ? 'active' : '' ?>" href="knowledge.php">
                        <i class="bi bi-journal-bookmark me-1"></i>ความรู้
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'weather.php' || $current_page == 'sky_check.php') ? 'active' : '' ?>" href="weather.php">
                        <i class="bi bi-cloud-sun me-1"></i>เช็กสภาพฟ้า
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'contact.php') ? 'active' : '' ?>" href="contact.php">
                        <i class="bi bi-envelope me-1"></i>ติดต่อเรา
                    </a>
                </li>

                <!-- ปุ่มสำหรับผู้ดูแลระบบ (Admin) -->
                <li class="nav-item ms-lg-2">
                    <?php if ($is_admin): ?>
                        <div class="d-flex align-items-center gap-2">
                            <a class="btn btn-outline-info btn-sm px-3 rounded-pill" href="admin_dashboard.php">
                                <i class="bi bi-speedometer2 me-1"></i>หลังบ้าน
                            </a>
                            <a class="btn btn-outline-danger btn-sm px-2 rounded-pill" href="logout.php" title="ออกจากระบบ">
                                <i class="bi bi-box-arrow-right"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <a class="btn btn-outline-secondary btn-sm px-3 rounded-pill text-light" href="login.php" title="สำหรับผู้ดูแลระบบ">
                            <i class="bi bi-lock-fill me-1"></i>เข้าสู่ระบบ
                        </a>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- ส่วนเนื้อหาหลักของแต่ละหน้า -->
<main class="main-content">
