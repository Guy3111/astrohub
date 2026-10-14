<?php
/**
 * logout.php
 * ออกจากระบบ และล้างค่า Session ทั้งหมด แล้วเด้งกลับไปหน้า login.php
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ล้างข้อมูล session ทั้งหมด
$_SESSION = [];

// ลบคุกกี้ session ถ้ามี
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// ทำลาย session
session_destroy();

// เด้งกลับไปยังหน้า login.php
header("Location: login.php?msg=logged_out");
exit();
?>

