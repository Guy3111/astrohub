<?php
/**
 * login.php
 * หน้าเข้าสู่ระบบสำหรับ ASTROHUB
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ถ้าเคย Login อยู่แล้ว ให้ข้ามไปหน้า index.php ทันที
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

$error_msg   = '';
$success_msg = '';

// ตรวจสอบข้อความแจ้งเตือนจาก URL
if (isset($_GET['error']) && $_GET['error'] === 'unauthorized') {
    $error_msg = 'กรุณาเข้าสู่ระบบก่อนเข้าใช้งานเว็บไซต์';
} elseif (isset($_GET['msg']) && $_GET['msg'] === 'logged_out') {
    $success_msg = 'ออกจากระบบเรียบร้อยแล้ว';
}

// ตรวจสอบเมื่อมีการกดปุ่มส่งฟอร์ม (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // กำหนด Username: admin, Password: 1234
    if ($username === 'admin' && $password === '1234') {
        // กำหนด Session บันทึกสถานะการ Login
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username']  = 'admin';

        // เข้าสู่ระบบสำเร็จ เด้งไปหน้าแรก index.php
        header("Location: index.php");
        exit();
    } else {
        $error_msg = 'ชื่อผู้ใช้ (Username) หรือรหัสผ่าน (Password) ไม่ถูกต้อง!';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ASTROHUB</title>
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
            background-color: #080b12;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: radial-gradient(circle at 50% 20%, rgba(56, 189, 248, 0.12), transparent 60%);
        }
        .brand-logo {
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 2px;
            font-weight: 800;
        }
        .login-card {
            background: #111827;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6), 0 0 30px rgba(56, 189, 248, 0.1);
            width: 100%;
            max-width: 420px;
        }
        .form-control-dark {
            background-color: #080b12 !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #f8fafc !important;
            border-radius: 10px;
            padding: 12px 16px;
            transition: all 0.2s ease;
        }
        .form-control-dark:focus {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 0.25rem rgba(56, 189, 248, 0.25) !important;
            background-color: #0b0f19 !important;
        }
        .input-group-text-dark {
            background-color: #080b12;
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #38bdf8;
            border-radius: 10px 0 0 10px;
        }
    </style>
</head>
<body>

<div class="container p-3">
    <div class="row justify-content-center">
        <div class="col-12 d-flex justify-content-center">
            <div class="login-card p-4 p-sm-5">
                <!-- โลโก้และหัวข้อ -->
                <div class="text-center mb-4">
                    <h2 class="brand-logo text-info d-flex align-items-center justify-content-center gap-2 mb-1">
                        <i class="bi bi-stars"></i> ASTROHUB
                    </h2>
                    <p class="text-secondary small mb-0">เข้าสู่ระบบเพื่อสำรวจจักรวาลและดวงดาว</p>
                </div>

                <!-- กล่องแจ้งเตือนความสำเร็จ -->
                <?php if (!empty($success_msg)): ?>
                    <div class="alert alert-success bg-success bg-opacity-10 border-success text-success d-flex align-items-center gap-2 py-2 px-3 small rounded-3 mb-4" role="alert">
                        <i class="bi bi-check-circle-fill fs-5 flex-shrink-0"></i>
                        <div><?= htmlspecialchars($success_msg) ?></div>
                    </div>
                <?php endif; ?>

                <!-- กล่องข้อความแจ้งเตือนข้อผิดพลาด -->
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger bg-danger bg-opacity-10 border-danger text-danger d-flex align-items-center gap-2 py-2 px-3 small rounded-3 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0"></i>
                        <div><?= htmlspecialchars($error_msg) ?></div>
                    </div>
                <?php endif; ?>

                <!-- ฟอร์มเข้าสู่ระบบ -->
                <form method="POST" action="login.php">
                    <!-- Username -->
                    <div class="mb-3">
                        <label for="username" class="form-label text-light small fw-medium">ชื่อผู้ใช้งาน (Username)</label>
                        <div class="input-group">
                            <span class="input-group-text input-group-text-dark">
                                <i class="bi bi-person-fill"></i>
                            </span>
                            <input type="text" 
                                   class="form-control form-control-dark" 
                                   id="username" 
                                   name="username" 
                                   placeholder="กรอก Username" 
                                   required 
                                   autofocus>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="form-label text-light small fw-medium">รหัสผ่าน (Password)</label>
                        <div class="input-group">
                            <span class="input-group-text input-group-text-dark">
                                <i class="bi bi-key-fill"></i>
                            </span>
                            <input type="password" 
                                   class="form-control form-control-dark" 
                                   id="password" 
                                   name="password" 
                                   placeholder="กรอกรหัสผ่าน" 
                                   required>
                        </div>
                    </div>

                    <!-- ปุ่ม Login -->
                    <button type="submit" class="btn btn-info w-100 py-2 fw-semibold text-dark rounded-3 shadow">
                        <i class="bi bi-box-arrow-in-right me-1"></i> เข้าสู่ระบบ
                    </button>
                </form>

                <div class="text-center mt-4 pt-3 border-top border-secondary border-opacity-25">
                    <small class="text-secondary d-block">
                        <i class="bi bi-info-circle me-1"></i> บัญชีทดสอบ: <code>admin</code> / รหัสผ่าน: <code>1234</code>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
