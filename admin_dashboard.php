<?php
/**
 * admin_dashboard.php
 * แผงควบคุมหลักสำหรับผู้ดูแลระบบ
 */
include 'admin_header.php';

// ดึงจำนวนแถวของแต่ละตาราง
$count_events    = $conn->query("SELECT COUNT(*) FROM events")->fetch_row()[0] ?? 0;
$count_locations = $conn->query("SELECT COUNT(*) FROM locations")->fetch_row()[0] ?? 0;
$count_articles  = $conn->query("SELECT COUNT(*) FROM articles")->fetch_row()[0] ?? 0;
$count_inquiries = $conn->query("SELECT COUNT(*) FROM inquiries")->fetch_row()[0] ?? 0;

// ดึงรายการข้อความติดต่อล่าสุด 5 ข้อความ
$inquiries_query = $conn->query("SELECT * FROM inquiries ORDER BY id DESC LIMIT 5");
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-white fw-bold mb-1">
                <i class="bi bi-speedometer2 text-info me-2"></i>แผงควบคุมผู้ดูแลระบบ
            </h2>
            <p class="text-secondary small mb-0">ยินดีต้อนรับสู่ระบบจัดการข้อมูล ASTROHUB</p>
        </div>
        <div class="d-flex gap-2">
            <a href="admin_events.php" class="btn btn-info btn-sm text-dark fw-medium">
                <i class="bi bi-plus-circle me-1"></i>เพิ่มปรากฏการณ์
            </a>
            <a href="admin_locations.php" class="btn btn-warning btn-sm text-dark fw-medium">
                <i class="bi bi-plus-circle me-1"></i>เพิ่มจุดชมดาว
            </a>
        </div>
    </div>

    <!-- การ์ดสรุปสถิติ 4 ใบ -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-custom p-3 border-info border-opacity-25 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary small">ปรากฏการณ์ดาราศาสตร์</span>
                    <i class="bi bi-calendar-event fs-3 text-info"></i>
                </div>
                <h3 class="text-white fw-bold mb-2"><?= $count_events ?> <small class="fs-6 fw-normal text-secondary">รายการ</small></h3>
                <a href="admin_events.php" class="text-info text-decoration-none small">
                    จัดการข้อมูล <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-custom p-3 border-warning border-opacity-25 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary small">พิกัดสถานที่ชมดาว</span>
                    <i class="bi bi-geo-alt fs-3 text-warning"></i>
                </div>
                <h3 class="text-white fw-bold mb-2"><?= $count_locations ?> <small class="fs-6 fw-normal text-secondary">แห่ง</small></h3>
                <a href="admin_locations.php" class="text-warning text-decoration-none small">
                    จัดการข้อมูล <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-custom p-3 border-success border-opacity-25 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary small">บทความความรู้</span>
                    <i class="bi bi-journal-text fs-3 text-success"></i>
                </div>
                <h3 class="text-white fw-bold mb-2"><?= $count_articles ?> <small class="fs-6 fw-normal text-secondary">เรื่อง</small></h3>
                <a href="admin_articles.php" class="text-success text-decoration-none small">
                    จัดการข้อมูล <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-custom p-3 border-danger border-opacity-25 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary small">ข้อความติดต่อเข้า</span>
                    <i class="bi bi-envelope-check fs-3 text-danger"></i>
                </div>
                <h3 class="text-white fw-bold mb-2"><?= $count_inquiries ?> <small class="fs-6 fw-normal text-secondary">ข้อความ</small></h3>
                <span class="text-secondary small">ตาราง inquiries</span>
            </div>
        </div>
    </div>

    <!-- เมนูลัดและการจัดการ -->
    <div class="row g-4">
        <!-- กล่องทางลัดจัดการ 2 ตารางหลัก -->
        <div class="col-lg-5">
            <div class="card card-custom p-4 h-100">
                <h5 class="text-white fw-bold mb-3">
                    <i class="bi bi-gear-wide-connected text-info me-2"></i>การจัดการข้อมูลหลัก (CRUD)
                </h5>
                <p class="text-secondary small mb-4">
                    คุณสามารถเพิ่ม ลบ และแก้ไขข้อมูลได้ครบถ้วน ข้อมูลที่บันทึกจะแสดงในหน้าเว็บหน้าบ้านทันที
                </p>

                <div class="d-grid gap-3">
                    <a href="admin_events.php" class="btn btn-outline-info text-start p-3 rounded-3 d-flex align-items-center justify-content-between">
                        <div>
                            <strong class="d-block text-white">ตารางที่ 1: จัดการปรากฏการณ์ (events)</strong>
                            <small class="text-secondary">เพิ่ม / แก้ไข / ลบ ตารางเวลาฝนดาวตก สุริยุปราคา ฯลฯ</small>
                        </div>
                        <i class="bi bi-chevron-right fs-4 text-info"></i>
                    </a>

                    <a href="admin_locations.php" class="btn btn-outline-warning text-start p-3 rounded-3 d-flex align-items-center justify-content-between">
                        <div>
                            <strong class="d-block text-white">ตารางที่ 2: จัดการสถานที่ชมดาว (locations)</strong>
                            <small class="text-secondary">เพิ่ม / แก้ไข / ลบ พิกัดดูดาว ระดับความมืด เปลี่ยนรูปภาพ</small>
                        </div>
                        <i class="bi bi-chevron-right fs-4 text-warning"></i>
                    </a>

                    <a href="admin_articles.php" class="btn btn-outline-success text-start p-3 rounded-3 d-flex align-items-center justify-content-between">
                        <div>
                            <strong class="d-block text-white">ตารางที่ 3: จัดการบทความความรู้ (articles)</strong>
                            <small class="text-secondary">เพิ่ม / แก้ไข / ลบ บทความดาราศาสตร์ อัปโหลดรูปภาพ</small>
                        </div>
                        <i class="bi bi-chevron-right fs-4 text-success"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- กล่องแสดงข้อความติดต่อล่าสุด -->
        <div class="col-lg-7">
            <div class="card card-custom p-4 h-100">
                <h5 class="text-white fw-bold mb-3">
                    <i class="bi bi-chat-left-dots text-info me-2"></i>ข้อความติดต่อล่าสุดจากผู้ใช้งาน
                </h5>

                <?php if ($inquiries_query && $inquiries_query->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover table-dark-custom align-middle small mb-0">
                            <thead>
                                <tr>
                                    <th>วันที่</th>
                                    <th>ผู้ติดต่อ</th>
                                    <th>หัวข้อ</th>
                                    <th>ข้อความ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($inq = $inquiries_query->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-secondary" style="white-space: nowrap;"><?= date('d/m/Y H:i', strtotime($inq['created_at'])) ?></td>
                                        <td class="fw-semibold text-white">
                                            <?= htmlspecialchars($inq['name']) ?><br>
                                            <small class="text-secondary fw-normal"><?= htmlspecialchars($inq['email']) ?></small>
                                        </td>
                                        <td class="text-info"><?= htmlspecialchars($inq['subject'] ?? '-') ?></td>
                                        <td class="text-secondary" style="max-width: 200px;">
                                            <div class="text-truncate"><?= htmlspecialchars($inq['message']) ?></div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-secondary small mb-0">ยังไม่มีข้อความติดต่อเข้ามาในระบบ</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>

