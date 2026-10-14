<?php
/**
 * calendar.php
 * ปฏิทินปรากฏการณ์ดาราศาสตร์ - ดึงข้อมูลทั้งหมดจากตาราง events
 */
include('db_connect.php');
include 'header.php';

// แปลงวันที่เป็นภาษาไทยแบบเต็ม
function formatThaiDateFull($dateString) {
    if (!$dateString) return '';
    $timestamp = strtotime($dateString);
    $months = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
        5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
        9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
    ];
    $day = date('j', $timestamp);
    $month = $months[(int)date('n', $timestamp)];
    $year = (int)date('Y', $timestamp) + 543;
    return "$day $month $year";
}

// ดึงข้อมูลปรากฏการณ์ทั้งหมด เรียงตามวันที่
$events_sql = "SELECT * FROM events ORDER BY event_date ASC, id ASC";
$events_query = $conn->query($events_sql);
$events = [];
if ($events_query && $events_query->num_rows > 0) {
    while ($row = $events_query->fetch_assoc()) {
        $events[] = $row;
    }
}
?>

<style>
    .page-header {
        background: radial-gradient(circle at 50% 10%, rgba(56, 189, 248, 0.15), transparent 70%),
                    linear-gradient(180deg, #080b12 0%, #0b0f19 100%);
        padding: 50px 0 40px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .event-grid-card {
        background: #111827;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .event-grid-card:hover {
        transform: translateY(-6px);
        border-color: rgba(56, 189, 248, 0.4);
        box-shadow: 0 16px 35px rgba(0, 0, 0, 0.6), 0 0 20px rgba(56, 189, 248, 0.15);
    }
    .card-thumb-wrapper {
        position: relative;
        height: 220px;
        background-color: #0b0f19;
        overflow: hidden;
    }
    .card-thumb-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .event-grid-card:hover .card-thumb-wrapper img {
        transform: scale(1.08);
    }
    .date-badge {
        position: absolute;
        bottom: 12px;
        left: 12px;
        background: rgba(8, 11, 18, 0.85);
        border: 1px solid rgba(56, 189, 248, 0.35);
        color: #fff;
        backdrop-filter: blur(8px);
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 0.85rem;
    }
    .category-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        backdrop-filter: blur(6px);
    }
    .info-list-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        margin-bottom: 8px;
    }
</style>

<!-- ส่วนหัวเรื่องหน้าเว็บ -->
<section class="page-header text-center">
    <div class="container">
        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2 rounded-pill mb-3">
            <i class="bi bi-calendar-check me-1"></i> Astronomical Calendar
        </span>
        <h1 class="display-6 fw-bold text-white mb-2">ปฏิทินปรากฏการณ์ดาราศาสตร์</h1>
        <p class="text-secondary lead fs-6 mx-auto mb-0" style="max-width: 650px;">
            รวบรวมเหตุการณ์สำคัญบนท้องฟ้าตลอดทั้งปี ทั้งฝนดาวตก สุริยุปราคา จันทรุปราคา และดาวเคราะห์สำคัญ ให้คุณไม่พลาดทุกค่ำคืนพิเศษ
        </p>
    </div>
</section>

<!-- เนื้อหาหลัก - แสดงการ์ดรายการทั้งหมด -->
<section class="py-5">
    <div class="container py-2">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="text-secondary small">
                พบทั้งหมด <strong class="text-info"><?= count($events) ?></strong> รายการ
            </div>
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle text-light" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-funnel me-1"></i> ปรากฏการณ์ทั้งหมด
                </button>
                <ul class="dropdown-menu dropdown-menu-dark">
                    <li><a class="dropdown-item active" href="#">ทั้งหมด</a></li>
                    <li><a class="dropdown-item" href="#">ฝนดาวตก</a></li>
                    <li><a class="dropdown-item" href="#">สุริยุปราคา / จันทรุปราคา</a></li>
                    <li><a class="dropdown-item" href="#">ดาวเคราะห์</a></li>
                </ul>
            </div>
        </div>

        <?php if (!empty($events)): ?>
            <div class="row g-4">
                <?php 
                    // รูปภาพสำรองตามหมวดหมู่หรือลำดับ
                    $backup_images = [
                        "https://images.unsplash.com/photo-1506703719100-a0f3a48c0f86?auto=format&fit=crop&w=800&q=80",
                        "https://images.unsplash.com/photo-1532693322450-2cb5c511067d?auto=format&fit=crop&w=800&q=80",
                        "https://images.unsplash.com/photo-1614728894747-a83421e2b9c9?auto=format&fit=crop&w=800&q=80",
                        "https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=800&q=80"
                    ];
                ?>
                <?php foreach ($events as $index => $item): ?>
                    <?php 
                        // ตรวจสอบไฟล์รูปภาพ
                        $img_src = (!empty($item['image_url']) && file_exists($item['image_url']))
                            ? htmlspecialchars($item['image_url'])
                            : $backup_images[$index % count($backup_images)];
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="event-grid-card">
                            <!-- รูปภาพ & ป้ายกำกับ -->
                            <div class="card-thumb-wrapper">
                                <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                                
                                <!-- ป้ายหมวดหมู่ -->
                                <?php if (!empty($item['category'])): ?>
                                    <div class="category-badge">
                                        <span class="badge bg-info text-dark fw-semibold px-2 py-1 shadow-sm">
                                            <?= htmlspecialchars($item['category']) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <!-- ป้ายวันที่บนรูป -->
                                <div class="date-badge">
                                    <i class="bi bi-calendar2-event text-info me-1"></i>
                                    <strong><?= formatThaiDateFull($item['event_date']) ?></strong>
                                </div>
                            </div>

                            <!-- ส่วนเนื้อหาในการ์ด -->
                            <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                <h4 class="text-white fw-bold mb-2 fs-5">
                                    <?= htmlspecialchars($item['title']) ?>
                                </h4>

                                <p class="text-secondary small mb-3 flex-grow-1">
                                    <?= htmlspecialchars($item['description'] ?? 'ไม่มีรายละเอียดเพิ่มเติม') ?>
                                </p>

                                <!-- ข้อมูลรายละเอียด: วันที่, เวลา, ทิศทาง, ระดับการมองเห็น -->
                                <div class="mb-3">
                                    <!-- 1. วันที่ -->
                                    <div class="info-list-item">
                                        <i class="bi bi-calendar3 text-info fs-6"></i>
                                        <div class="small">
                                            <span class="text-secondary">วันที่:</span>
                                            <span class="text-white fw-medium ms-1"><?= formatThaiDateFull($item['event_date']) ?></span>
                                        </div>
                                    </div>

                                    <!-- 2. เวลา (Peak Time) -->
                                    <div class="info-list-item">
                                        <i class="bi bi-clock-history text-warning fs-6"></i>
                                        <div class="small">
                                            <span class="text-secondary">เวลา:</span>
                                            <span class="text-white fw-medium ms-1"><?= htmlspecialchars($item['peak_time'] ?? 'ตลอดทั้งคืน') ?></span>
                                        </div>
                                    </div>

                                    <!-- 3. ทิศทางในการมองเห็น -->
                                    <div class="info-list-item">
                                        <i class="bi bi-compass text-danger fs-6"></i>
                                        <div class="small">
                                            <span class="text-secondary">ทิศทาง:</span>
                                            <span class="text-white fw-medium ms-1"><?= htmlspecialchars($item['direction'] ?? 'ทุกทิศทางบนท้องฟ้า') ?></span>
                                        </div>
                                    </div>

                                    <!-- 4. ระดับการมองเห็น (Visibility Level) -->
                                    <?php if (!empty($item['visibility_level'])): ?>
                                    <div class="info-list-item">
                                        <i class="bi bi-eye text-success fs-6"></i>
                                        <div class="small">
                                            <span class="text-secondary">การมองเห็น:</span>
                                            <span class="badge bg-success bg-opacity-25 text-success ms-1">
                                                <?= htmlspecialchars($item['visibility_level']) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- ปุ่มเชื่อมโยง -->
                                <div class="pt-2 border-top border-secondary border-opacity-25 mt-auto">
                                    <a href="locations.php" class="btn btn-outline-info btn-sm w-100">
                                        <i class="bi bi-geo-alt me-1"></i> หาพิกัดดูปรากฏการณ์นี้
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5 border border-secondary border-opacity-25 rounded-4 bg-black bg-opacity-25">
                <i class="bi bi-calendar-x text-secondary fs-1 mb-3 d-block"></i>
                <h4 class="text-white">ยังไม่มีข้อมูลปรากฏการณ์ในตาราง</h4>
                <p class="text-secondary small mb-0">ระบบยังไม่มีรายการเหตุการณ์ดาราศาสตร์ที่บันทึกไว้ในขณะนี้</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php 
include 'footer.php'; 
?>
