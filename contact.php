<?php
/**
 * contact.php
 * หน้าติดต่อเรา - ฟอร์มกรอกข้อมูลและบันทึกลงตาราง inquiries ใน MySQL
 */
require_once 'db_connect.php';
include 'header.php';

$success_msg = '';
$error_msg   = '';

// ตรวจสอบการส่งฟอร์มผ่านวิธี POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? 'ติดต่อทั่วไป');
    $message = trim($_POST['message'] ?? '');

    // ตรวจสอบว่ากรอกข้อมูลครบถ้วนหรือไม่
    if (empty($name) || empty($email) || empty($message)) {
        $error_msg = 'กรุณากรอกชื่อ, อีเมล และข้อความให้ครบถ้วน';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = 'รูปแบบอีเมลไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง';
    } else {
        // ใช้ Prepared Statement เพื่อป้องกัน SQL Injection
        $stmt = $conn->prepare("INSERT INTO inquiries (name, email, subject, message) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssss", $name, $email, $subject, $message);
            if ($stmt->execute()) {
                $success_msg = "ส่งข้อความติดต่อเรียบร้อยแล้ว ขอบคุณที่ติดต่อ ASTROHUB ทีมงานจะติดต่อกลับโดยเร็วที่สุด";
                // ล้างค่าฟอร์ม
                $name = $email = $subject = $message = '';
            } else {
                $error_msg = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        } else {
            $error_msg = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . htmlspecialchars($conn->error);
        }
    }
}
?>

<style>
    .page-header {
        background: radial-gradient(circle at 50% 15%, rgba(56, 189, 248, 0.16), transparent 70%),
                    linear-gradient(180deg, #080b12 0%, #0b0f19 100%);
        padding: 50px 0 40px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .contact-card {
        background: #111827;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.5);
    }
    .contact-info-card {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 16px;
        padding: 24px;
        height: 100%;
    }
    .form-control-dark {
        background-color: #080b12 !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        color: #f1f5f9 !important;
        border-radius: 10px;
        padding: 12px 16px;
        transition: all 0.2s ease;
    }
    .form-control-dark:focus {
        border-color: #38bdf8 !important;
        box-shadow: 0 0 0 0.25rem rgba(56, 189, 248, 0.2) !important;
        background-color: #0b0f19 !important;
    }
    .info-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: rgba(56, 189, 248, 0.1);
        color: #38bdf8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }
</style>

<!-- Header หน้าติดต่อเรา -->
<section class="page-header text-center">
    <div class="container">
        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2 rounded-pill mb-3">
            <i class="bi bi-envelope-paper-heart me-1"></i> Get In Touch
        </span>
        <h1 class="display-6 fw-bold text-white mb-2">ติดต่อทีมงาน ASTROHUB</h1>
        <p class="text-secondary lead fs-6 mx-auto mb-0" style="max-width: 650px;">
            มีข้อสงสัย แนะนำพิกัดชมดาวแห่งใหม่ หรือต้องการร่วมเป็นส่วนหนึ่งของเครือข่ายดาราศาสตร์ ส่งข้อความหาเราได้ตลอด 24 ชั่วโมง
        </p>
    </div>
</section>

<div class="container py-5">
    <div class="row g-4 justify-content-center">
        <!-- ข้อมูลติดต่อด่วนทางซ้าย -->
        <div class="col-lg-4">
            <div class="contact-info-card d-flex flex-column gap-4">
                <div>
                    <h5 class="text-white fw-bold mb-2">ช่องทางการติดต่อ</h5>
                    <p class="text-secondary small mb-0">ยินดีตอบทุกคำถามเกี่ยวกับท้องฟ้าและดาราศาสตร์</p>
                </div>


                <div class="d-flex align-items-start gap-3">
                    <div class="info-icon-box">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <div>
                        <h6 class="text-white fw-semibold mb-1">อีเมล</h6>
                        <p class="text-secondary small mb-0">67011213104@msu.ac.th</p>
                        <p class="text-secondary small mb-0">67011213111@msu.ac.th</p>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3">
                    <div class="info-icon-box">
                        <i class="bi bi-telephone-fill"></i>
                    </div>
                    <div>
                        <h6 class="text-white fw-semibold mb-1">เบอร์โทรศัพท์</h6>
                        <p class="text-secondary small mb-0">098-231-3776</p>
                        <p class="text-secondary small mb-0">095-317-5429</p>
                    </div>
                </div>

                <div class="p-3 bg-black bg-opacity-40 rounded-3 mt-auto border border-secondary border-opacity-25">
                    <div class="d-flex align-items-center gap-2 text-info small mb-1">
                        <i class="bi bi-shield-check"></i> <strong>ความปลอดภัยของข้อมูล</strong>
                    </div>
                    <small class="text-secondary">ข้อมูลของคุณจะถูกเก็บรักษาอย่างปลอดภัยในระบบ MySQL เพื่อใช้ในการติดต่อกลับเท่านั้น</small>
                </div>
            </div>
        </div>

        <!-- ฟอร์มกรอกข้อความทางขวา -->
        <div class="col-lg-7">
            <div class="contact-card p-4 p-md-5">
                <h4 class="text-white fw-bold mb-2">ส่งข้อความถึงเรา</h4>
                <p class="text-secondary small mb-4">กรอกข้อมูลลงในแบบฟอร์มด้านล่าง ระบบจะบันทึกลงฐานข้อมูลทันที</p>

                <!-- แจ้งเตือนสถานะสำเร็จ / ผิดพลาด -->
                <?php if (!empty($success_msg)): ?>
                    <div class="alert alert-success bg-success bg-opacity-10 border-success text-success d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <div><?= htmlspecialchars($success_msg) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger bg-danger bg-opacity-10 border-danger text-danger d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div><?= htmlspecialchars($error_msg) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="contact.php">
                    <div class="row g-3">
                        <!-- 1. ชื่อ -->
                        <div class="col-md-6">
                            <label for="name" class="form-label text-light small fw-medium">
                                ชื่อของคุณ <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control form-control-dark" 
                                   id="name" 
                                   name="name" 
                                   value="<?= htmlspecialchars($name ?? '') ?>" 
                                   placeholder="เช่น นภัทร นักดูดาว" 
                                   required>
                        </div>

                        <!-- 2. อีเมล -->
                        <div class="col-md-6">
                            <label for="email" class="form-label text-light small fw-medium">
                                อีเมลติดต่อ <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   class="form-control form-control-dark" 
                                   id="email" 
                                   name="email" 
                                   value="<?= htmlspecialchars($email ?? '') ?>" 
                                   placeholder="name@example.com" 
                                   required>
                        </div>

                        <!-- 3. หัวข้อข้อความ -->
                        <div class="col-12">
                            <label for="subject" class="form-label text-light small fw-medium">
                                หัวข้อข้อความ
                            </label>
                            <input type="text" 
                                   class="form-control form-control-dark" 
                                   id="subject" 
                                   name="subject" 
                                   value="<?= htmlspecialchars($subject ?? '') ?>" 
                                   placeholder="เช่น แนะนำจุดชมดาวใหม่ / สอบถามการจัดกิจกรรม">
                        </div>

                        <!-- 4. ข้อความ -->
                        <div class="col-12">
                            <label for="message" class="form-label text-light small fw-medium">
                                ข้อความ <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control form-control-dark" 
                                      id="message" 
                                      name="message" 
                                      rows="5" 
                                      placeholder="พิมพ์ข้อความของคุณที่นี่..." 
                                      required><?= htmlspecialchars($message ?? '') ?></textarea>
                        </div>

                        <!-- ปุ่มส่ง -->
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-info w-100 py-3 fw-bold text-dark rounded-3 shadow">
                                <i class="bi bi-send-fill me-2"></i> ส่งข้อความ
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
include 'footer.php'; 
?>
