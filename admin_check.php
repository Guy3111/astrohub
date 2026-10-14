<?php
/**
 * admin_check.php
 * ไฟล์สำหรับตรวจสอบสถานะการ Login ของผู้ดูแลระบบ (Admin)
 * หากยังไม่ได้เข้าสู่ระบบ จะ Redirect ไปยังหน้า login.php โดยอัตโนมัติ
 */

// เริ่มต้น Session หากยังไม่ได้เริ่ม
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ตรวจสอบตัวแปร session ว่าได้ทำการ Login แล้วหรือไม่
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // ส่ง header ไปยังหน้า login.php พร้อมระบุ error code (optional)
    header("Location: login.php?error=unauthorized");
    exit();
}
?>

