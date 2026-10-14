<?php
/**
 * db_connect.php
 * ไฟล์สำหรับเชื่อมต่อฐานข้อมูล MySQL (XAMPP)
 */

$host     = "localhost";
$username = "root";
$password = "";
$dbname   = "astrohub_db";

// เชื่อมต่อฐานข้อมูลโดยใช้ mysqli
$conn = new mysqli($host, $username, $password, $dbname);

// ตรวจสอบสถานะการเชื่อมต่อ
if ($conn->connect_error) {
    // กำหนด header เป็น UTF-8 เพื่อให้ภาษาไทยในข้อความแจ้งเตือนไม่เพี้ยน
    header('Content-Type: text/html; charset=utf-8');
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

// ตั้งค่า charset เป็น utf8mb4 เพื่อรองรับภาษาไทย และอีโมจิ ป้องกันตัวอักษรต่างดาว
if (!$conn->set_charset("utf8mb4")) {
    header('Content-Type: text/html; charset=utf-8');
    die("เกิดข้อผิดพลาดในการตั้งค่าชุดอักขระ (utf8mb4): " . $conn->error);
}
?>

