<?php
/**
 * index.php
 * หน้าแรกของ ASTROHUB
 */
include('db_connect.php');
include 'header.php';

// แปลงวันที่เป็นรูปแบบภาษาไทย
function formatThaiDate($dateString) {
    if (!$dateString) return '';
    $timestamp = strtotime($dateString);
    $months = [
        1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
        5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
        9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
    ];
    $day = date('j', $timestamp);
    $month = $months[(int)date('n', $timestamp)];
    $year = (int)date('Y', $timestamp) + 543;
    return "$day $month $year";
}

// -------------------------------------------------------------
// 1. ดึงงานดาราศาสตร์ที่ใกล้ที่สุด 1 อัน จากตาราง events
// -------------------------------------------------------------
$today = date('Y-m-d');
// ค้นหางานตั้งแต่วันนี้เป็นต้นไป ถ้าไม่มีให้ดึงงานล่าสุด 1 รายการ
$event_sql = "SELECT * FROM events WHERE event_date >= '$today' ORDER BY event_date ASC LIMIT 1";
$event_query = $conn->query($event_sql);

if (!$event_query || $event_query->num_rows == 0) {
    // กรณีไม่มีงานในอนาคต ดึงงานที่ใกล้เคียงที่สุดจากตาราง
    $event_sql = "SELECT * FROM events ORDER BY event_date DESC LIMIT 1";
    $event_query = $conn->query($event_sql);
}
$nearest_event = ($event_query && $event_query->num_rows > 0) ? $event_query->fetch_assoc() : null;

// -------------------------------------------------------------
// 2. ดึงสถานที่ชมดาวแนะนำ 3 แห่ง จากตาราง locations
// -------------------------------------------------------------
$locations_sql = "SELECT * FROM locations ORDER BY darkness_level DESC, id ASC LIMIT 3";
$locations_query = $conn->query($locations_sql);
$locations = [];
if ($locations_query && $locations_query->num_rows > 0) {
    while ($row = $locations_query->fetch_assoc()) {
        $locations[] = $row;
    }
}

// -------------------------------------------------------------
// 3. ดึงบทความล่าสุด จากตาราง articles
// -------------------------------------------------------------
$articles_sql = "SELECT * FROM articles ORDER BY id DESC LIMIT 3";
$articles_query = $conn->query($articles_sql);
$articles = [];
if ($articles_query && $articles_query->num_rows > 0) {
    while ($row = $articles_query->fetch_assoc()) {
        $articles[] = $row;
    }
}
?>

<style>
    /* สไตล์เฉพาะหน้า index */
    .hero-banner {
        background: radial-gradient(circle at 75% 30%, rgba(56, 189, 248, 0.18), transparent 60%),
                    linear-gradient(135deg, #090e1a 0%, #05070d 100%);
        border-bottom: 1px solid rgba(56, 189, 248, 0.2);
        padding: 50px 0 60px 0;
        position: relative;
    }
    .badge-event-glow {
        background: rgba(56, 189, 248, 0.15);
        color: #38bdf8;
        border: 1px solid rgba(56, 189, 248, 0.4);
        font-weight: 500;
        letter-spacing: 0.5px;
    }
    .event-info-pill {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        padding: 10px 16px;
    }
    .custom-card {
        background: #111827;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .custom-card:hover {
        transform: translateY(-6px);
        border-color: rgba(56, 189, 248, 0.45);
        box-shadow: 0 16px 32px rgba(0, 0, 0, 0.5), 0 0 20px rgba(56, 189, 248, 0.15);
    }
    .card-img-wrapper {
        position: relative;
        height: 200px;
        overflow: hidden;
        background-color: #0b0f19;
    }
    .card-img-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .custom-card:hover .card-img-wrapper img {
        transform: scale(1.06);
    }
    .card-badge-top {
        position: absolute;
        top: 12px;
        left: 12px;
        backdrop-filter: blur(6px);
    }
    .section-title {
        position: relative;
        display: inline-block;
        padding-bottom: 8px;
    }
    .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 45px;
        height: 3px;
        background: #38bdf8;
        border-radius: 2px;
    }
</style>

<!-- ============================================================= -->
<!-- ส่วนที่ 1: ป้าย Banner ด้านบน - งานดาราศาสตร์อันใกล้ที่สุด 1 อัน -->
<!-- ============================================================= -->
<section class="hero-banner">
    <div class="container">
        <?php if ($nearest_event): ?>
            <?php 
                // จัดเตรียมรูปภาพสำรองหากรูปในตารางไม่มีไฟล์อยู่จริง
                $default_event_img = "https://images.unsplash.com/photo-1506703719100-a0f3a48c0f86?auto=format&fit=crop&w=800&q=80";
                $event_img = (!empty($nearest_event['image_url']) && file_exists($nearest_event['image_url']))
                    ? htmlspecialchars($nearest_event['image_url']) 
                    : $default_event_img;
            ?>
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge badge-event-glow px-3 py-2 rounded-pill">
                            <i class="bi bi-broadcast me-1"></i> ปรากฏการณ์ดาราศาสตร์ใกล้ที่สุด
                        </span>
                        <?php if (!empty($nearest_event['category'])): ?>
                            <span class="badge bg-secondary bg-opacity-50 text-light px-2 py-1">
                                <?= htmlspecialchars($nearest_event['category']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <h1 class="display-5 fw-bold text-white mb-3">
                        <?= htmlspecialchars($nearest_event['title']) ?>
                    </h1>

                    <p class="text-secondary lead fs-6 mb-4">
                        <?= htmlspecialchars($nearest_event['description']) ?>
                    </p>

                    <!-- ข้อมูลสำคัญของงาน -->
                    <div class="row g-2 mb-4">
                        <div class="col-sm-6">
                            <div class="event-info-pill d-flex align-items-center gap-3">
                                <i class="bi bi-calendar-event fs-4 text-info"></i>
                                <div>
                                    <small class="text-secondary d-block">วันที่สังเกตการณ์</small>
                                    <strong class="text-white"><?= formatThaiDate($nearest_event['event_date']) ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="event-info-pill d-flex align-items-center gap-3">
                                <i class="bi bi-clock-history fs-4 text-info"></i>
                                <div>
                                    <small class="text-secondary d-block">ช่วงเวลาพีค (Peak Time)</small>
                                    <strong class="text-white"><?= htmlspecialchars($nearest_event['peak_time'] ?? 'ตลอดทั้งคืน') ?></strong>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($nearest_event['direction'])): ?>
                        <div class="col-sm-6">
                            <div class="event-info-pill d-flex align-items-center gap-3">
                                <i class="bi bi-compass fs-4 text-warning"></i>
                                <div>
                                    <small class="text-secondary d-block">ทิศทางท้องฟ้า</small>
                                    <strong class="text-white"><?= htmlspecialchars($nearest_event['direction']) ?></strong>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($nearest_event['visibility_level'])): ?>
                        <div class="col-sm-6">
                            <div class="event-info-pill d-flex align-items-center gap-3">
                                <i class="bi bi-eye fs-4 text-success"></i>
                                <div>
                                    <small class="text-secondary d-block">ระดับการมองเห็น</small>
                                    <strong class="text-white"><?= htmlspecialchars($nearest_event['visibility_level']) ?></strong>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="calendar.php" class="btn btn-info px-4 py-2 fw-medium text-dark shadow">
                            <i class="bi bi-calendar3 me-1"></i> ดูปฏิทินทั้งหมด
                        </a>
                        <a href="locations.php" class="btn btn-outline-light px-4 py-2">
                            <i class="bi bi-geo-alt me-1"></i> หาพิกัดชมปรากฏการณ์นี้
                        </a>
                    </div>
                </div>

                <div class="col-lg-5 text-center">
                    <div class="position-relative p-2 rounded-4 border border-secondary border-opacity-25 shadow-lg bg-black bg-opacity-40">
                        <img src="<?= $event_img ?>" 
                             alt="<?= htmlspecialchars($nearest_event['title']) ?>" 
                             class="img-fluid rounded-4 shadow w-100"
                             style="max-height: 340px; object-fit: cover;">
                        <div class="position-absolute bottom-0 start-50 translate-middle-x mb-3 bg-dark bg-opacity-80 backdrop-blur px-3 py-1 rounded-pill text-info small border border-secondary border-opacity-50">
                            <i class="bi bi-stars me-1"></i> ปรากฏการณ์แนะนำประจำฤดูกาล
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- กรณีไม่มีข้อมูลเหตุการณ์ -->
            <div class="text-center py-5">
                <i class="bi bi-stars text-info fs-1 mb-3 d-block"></i>
                <h2 class="text-white">ยังไม่มีข้อมูลปรากฏการณ์ดาราศาสตร์ในขณะนี้</h2>
                <p class="text-secondary">โปรดติดตามอัปเดตตารางเวลาและกิจกรรมดูดาวได้เร็วๆ นี้</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============================================================= -->
<!-- ส่วนที่ 2: การ์ด 3 ช่อง - สถานที่ชมดาวแนะนำจากตาราง locations -->
<!-- ============================================================= -->
<section class="py-5">
    <div class="container py-3">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="text-info fw-semibold small text-uppercase tracking-wider">Dark Sky Reserve</span>
                <h3 class="section-title text-white fw-bold mt-1 mb-0">สถานที่ชมดาวแนะนำ</h3>
            </div>
            <a href="locations.php" class="btn btn-outline-info btn-sm">
                ดูสถานที่ทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php if (!empty($locations)): ?>
                <?php 
                    $default_loc_images = [
                        "https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=600&q=80",
                        "https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80",
                        "https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=600&q=80"
                    ];
                ?>
                <?php foreach ($locations as $index => $loc): ?>
                    <?php 
                    $loc_img = !empty($loc['cover_image']) 
    ? htmlspecialchars($loc['cover_image']) 
    : $default_loc_images[$index % count($default_loc_images)];
                    ?>
                    <div class="col-md-4">
                        <div class="custom-card">
                            <div class="card-img-wrapper">
                                <img src="<?= $loc_img ?>" alt="<?= htmlspecialchars($loc['name']) ?>">
                                <div class="card-badge-top">
                                    <span class="badge bg-dark bg-opacity-75 text-info border border-info border-opacity-25 px-2 py-1">
                                        <i class="bi bi-geo-alt-fill me-1"></i> <?= htmlspecialchars($loc['province']) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                <h5 class="text-white fw-semibold mb-2">
                                    <?= htmlspecialchars($loc['name']) ?>
                                </h5>

                                <!-- ระดับความมืด (Darkness Level) -->
                                <div class="mb-3 d-flex align-items-center gap-1">
                                    <small class="text-secondary me-1">ระดับความมืด:</small>
                                    <?php 
                                        $darkness = (int)($loc['darkness_level'] ?? 0);
                                        for ($star = 1; $star <= 5; $star++): 
                                    ?>
                                        <i class="bi bi-moon-stars-fill <?= ($star <= $darkness) ? 'text-warning' : 'text-secondary opacity-25' ?> small"></i>
                                    <?php endfor; ?>
                                    <span class="badge bg-secondary bg-opacity-25 text-warning small ms-1">ระดับ <?= $darkness ?>/5</span>
                                </div>

                                <!-- สิ่งอำนวยความสะดวก -->
                                <div class="mb-4 flex-grow-1">
                                    <small class="text-secondary d-block mb-1">
                                        <i class="bi bi-check-circle-fill text-info me-1"></i> สิ่งอำนวยความสะดวก:
                                    </small>
                                    <p class="text-light small mb-0 opacity-75">
                                        <?= htmlspecialchars($loc['facilities'] ?? 'มีลานกางเต็นท์และที่จอดรถ') ?>
                                    </p>
                                </div>

                                <a href="locations.php" class="btn btn-outline-light btn-sm mt-auto w-100">
                                    <i class="bi bi-map me-1"></i> ดูรายละเอียดพิกัด
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center text-secondary py-4">
                    ยังไม่มีข้อมูลสถานที่ชมดาวในระบบ
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ============================================================= -->
<!-- ส่วนที่ 3: ช่องบทความล่าสุด - ดึงข้อมูลจากตาราง articles       -->
<!-- ============================================================= -->
<section class="py-5 bg-black bg-opacity-40 border-top border-secondary border-opacity-25">
    <div class="container py-3">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="text-info fw-semibold small text-uppercase tracking-wider">Astronomy Knowledge</span>
                <h3 class="section-title text-white fw-bold mt-1 mb-0">บทความความรู้ล่าสุด</h3>
            </div>
            <a href="knowledge.php" class="btn btn-outline-info btn-sm">
                ดูบทความทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php if (!empty($articles)): ?>
                <?php 
                    $default_article_images = [
                        "https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=600&q=80",
                        "https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?auto=format&fit=crop&w=600&q=80",
                        "https://images.unsplash.com/photo-1502134249126-9f3755a50d78?auto=format&fit=crop&w=600&q=80"
                    ];
                ?>
                <?php foreach ($articles as $index => $art): ?>
                    <?php 
                        $art_img = (!empty($art['image']) && file_exists($art['image']))
                            ? htmlspecialchars($art['image'])
                            : $default_article_images[$index % count($default_article_images)];
                    ?>
                    <div class="col-md-4">
                        <div class="custom-card">
                            <div class="card-img-wrapper">
                                <img src="<?= $art_img ?>" alt="<?= htmlspecialchars($art['title']) ?>">
                                <?php if (!empty($art['category'])): ?>
                                    <div class="card-badge-top">
                                        <span class="badge bg-info text-dark fw-semibold px-2 py-1">
                                            <?= htmlspecialchars($art['category']) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                <h5 class="text-white fw-semibold mb-2 line-clamp-2">
                                    <?= htmlspecialchars($art['title']) ?>
                                </h5>

                                <p class="text-secondary small mb-4 flex-grow-1">
                                    <?php 
                                        $content = strip_tags($art['content'] ?? '');
                                        echo (mb_strlen($content) > 90) ? mb_substr($content, 0, 90) . '...' : $content;
                                    ?>
                                </p>

                                <div class="d-flex justify-content-between align-items-center pt-3 border-top border-secondary border-opacity-25 mt-auto">
                                    <small class="text-secondary">
                                        <i class="bi bi-person-circle me-1 text-info"></i>
                                        <?= htmlspecialchars($art['author'] ?? 'ASTROHUB Team') ?>
                                    </small>
                                    <a href="knowledge.php" class="text-info text-decoration-none small fw-medium">
                                        อ่านต่อ <i class="bi bi-chevron-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center text-secondary py-4">
                    ยังไม่มีข้อมูลบทความในระบบ
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php 
// ดึงส่วนท้าย Footer
include 'footer.php'; 
?>
