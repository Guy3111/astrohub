<?php
/**
 * locations.php
 * พิกัดและสถานที่ชมดาวครบ 77 จังหวัดทั่วไทย - ดึงข้อมูลจากตาราง locations
 */
require_once 'db_connect.php';
include 'header.php';

// รับค่าค้นหาและตัวกรองระดับความมืด
$search_query   = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_darkness = isset($_GET['darkness']) ? (int)$_GET['darkness'] : 0;

$where_clauses = [];
if (!empty($search_query)) {
    $safe_search = $conn->real_escape_string($search_query);
    $where_clauses[] = "(name LIKE '%$safe_search%' OR province LIKE '%$safe_search%')";
}
if ($filter_darkness > 0) {
    $where_clauses[] = "darkness_level = $filter_darkness";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";
$locations_sql = "SELECT * FROM locations $where_sql ORDER BY darkness_level DESC, id ASC";
$locations_query = $conn->query($locations_sql);
$locations = [];
if ($locations_query && $locations_query->num_rows > 0) {
    while ($row = $locations_query->fetch_assoc()) {
        $locations[] = $row;
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
    .location-card {
        background: #111827;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .location-card:hover {
        transform: translateY(-6px);
        border-color: rgba(56, 189, 248, 0.45);
        box-shadow: 0 16px 35px rgba(0, 0, 0, 0.6), 0 0 20px rgba(56, 189, 248, 0.15);
    }
    .loc-img-wrapper {
        position: relative;
        height: 210px;
        background-color: #0b0f19;
        overflow: hidden;
    }
    .loc-img-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .location-card:hover .loc-img-wrapper img {
        transform: scale(1.08);
    }
    .province-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        background: rgba(8, 11, 18, 0.85);
        border: 1px solid rgba(56, 189, 248, 0.4);
        color: #38bdf8;
        backdrop-filter: blur(8px);
        padding: 5px 12px;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    .darkness-chip {
        position: absolute;
        bottom: 12px;
        right: 12px;
        background: rgba(0, 0, 0, 0.85);
        border: 1px solid rgba(255, 193, 7, 0.4);
        color: #ffc107;
        backdrop-filter: blur(8px);
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.8rem;
    }
    .darkness-bar {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 10px;
        padding: 8px 12px;
    }
    .facility-box {
        background: rgba(255, 255, 255, 0.02);
        border-radius: 8px;
        padding: 8px 12px;
    }
    .search-input-dark {
        background-color: #080b12 !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
        color: #f1f5f9 !important;
        border-radius: 10px;
        padding: 10px 16px;
    }
    .search-input-dark:focus {
        border-color: #38bdf8 !important;
        box-shadow: 0 0 0 0.25rem rgba(56, 189, 248, 0.25) !important;
    }
</style>

<!-- Header หน้าพิกัดชมดาว -->
<section class="page-header text-center">
    <div class="container">
        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2 rounded-pill mb-3">
            <i class="bi bi-geo-alt-fill me-1"></i> Dark Sky Locations (77 Provinces)
        </span>
        <h1 class="display-6 fw-bold text-white mb-2">พิกัดสถานที่ชมดาว 77 จังหวัดทั่วไทย</h1>
        <p class="text-secondary lead fs-6 mx-auto mb-0" style="max-width: 700px;">
            รวบรวมจุดชมดาว ท้องฟ้ามืดสนิท และลานกางเต็นท์ธรรมชาติครบทุกจังหวัดในประเทศไทย พร้อมเปิด Google Maps นำทางได้ทันที
        </p>
    </div>
</section>

<!-- กล่องค้นหาและตัวกรอง -->
<section class="py-4 bg-black bg-opacity-30 border-bottom border-secondary border-opacity-25">
    <div class="container">
        <form method="GET" action="locations.php" class="row g-2 align-items-center justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="input-group">
                    <span class="input-group-text bg-black border-secondary border-opacity-50 text-info">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" 
                           name="search" 
                           class="form-control search-input-dark" 
                           placeholder="ค้นหาชื่อสถานที่ หรือชื่อจังหวัด (เช่น เชียงใหม่, น่าน)..." 
                           value="<?= htmlspecialchars($search_query) ?>">
                </div>
            </div>
            <div class="col-md-3 col-lg-3">
                <select name="darkness" class="form-select search-input-dark">
                    <option value="0" <?= $filter_darkness === 0 ? 'selected' : '' ?>>ระดับความมืดทั้งหมด (1-5)</option>
                    <option value="5" <?= $filter_darkness === 5 ? 'selected' : '' ?>>ระดับ 5: มืดสนิท (Bortle 1-2)</option>
                    <option value="4" <?= $filter_darkness === 4 ? 'selected' : '' ?>>ระดับ 4: มืดมาก (Bortle 3-4)</option>
                    <option value="3" <?= $filter_darkness === 3 ? 'selected' : '' ?>>ระดับ 3: ปานกลาง (Bortle 5)</option>
                </select>
            </div>
            <div class="col-md-3 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-info w-100 fw-semibold text-dark">
                    <i class="bi bi-funnel me-1"></i> กรองข้อมูล
                </button>
                <?php if (!empty($search_query) || $filter_darkness > 0): ?>
                    <a href="locations.php" class="btn btn-outline-secondary" title="ล้างตัวกรอง">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</section>

<!-- รายการสถานที่ชมดาว -->
<section class="py-5">
    <div class="container py-2">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="text-secondary small">
                พบสถานที่ทั้งหมด <strong class="text-info"><?= count($locations) ?></strong> แห่ง
                <?php if (!empty($search_query)): ?>
                    (คำค้น: <span class="text-white">"<?= htmlspecialchars($search_query) ?>"</span>)
                <?php endif; ?>
            </div>
            <div class="text-secondary small">
                <i class="bi bi-info-circle me-1 text-info"></i> ครอบคลุมทั้ง 77 จังหวัดทั่วไทย
            </div>
        </div>

        <?php if (!empty($locations)): ?>
            <div class="row g-4">
                <?php foreach ($locations as $index => $loc): ?>
                    <?php 
                        $img_src = !empty($loc['cover_image']) ? htmlspecialchars($loc['cover_image']) : "https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=800&q=80";
                        $darkness = (int)($loc['darkness_level'] ?? 3);
                        $search_target = urlencode($loc['name'] . ' ' . $loc['province']);
                        $maps_url = "https://www.google.com/maps/search/?api=1&query=" . $search_target;
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="location-card">
                            <div class="loc-img-wrapper">
                                <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($loc['name']) ?>" loading="lazy">
                                <div class="province-badge">
                                    <i class="bi bi-geo-alt-fill me-1"></i><?= htmlspecialchars($loc['province']) ?>
                                </div>
                                <div class="darkness-chip">
                                    <i class="bi bi-moon-stars-fill me-1"></i> ความมืดระดับ <?= $darkness ?>/5
                                </div>
                            </div>

                            <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                <h4 class="text-white fw-bold mb-3 fs-5">
                                    <?= htmlspecialchars($loc['name']) ?>
                                </h4>

                                <!-- ระดับความมืด -->
                                <div class="darkness-bar mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-secondary">ระดับความมืดของฟ้า:</small>
                                        <small class="text-warning fw-semibold">เกณฑ์ <?= $darkness ?> เต็ม 5</small>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-moon-stars-fill fs-6 <?= ($i <= $darkness) ? 'text-warning' : 'text-secondary opacity-25' ?>"></i>
                                        <?php endfor; ?>
                                        <span class="ms-2 small text-secondary">
                                            <?php 
                                                if ($darkness >= 5) echo "(มืดสนิท ยอดเยี่ยม)";
                                                elseif ($darkness >= 4) echo "(มืดมาก เห็นทางช้างเผือก)";
                                                elseif ($darkness >= 3) echo "(ค่อนข้างมืด เหมาะดูดาว)";
                                                else echo "(มีแสงเมืองบางส่วน)";
                                            ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- สิ่งอำนวยความสะดวก -->
                                <?php if (!empty($loc['facilities'])): ?>
                                    <div class="facility-box mb-4 flex-grow-1">
                                        <small class="text-secondary d-block mb-1">
                                            <i class="bi bi-tools text-info me-1"></i> สิ่งอำนวยความสะดวก:
                                        </small>
                                        <p class="text-light small mb-0 opacity-80 leading-relaxed">
                                            <?= htmlspecialchars($loc['facilities']) ?>
                                        </p>
                                    </div>
                                <?php else: ?>
                                    <div class="mb-4 flex-grow-1"></div>
                                <?php endif; ?>

                                <!-- ปุ่มเปิด Google Maps -->
                                <div class="pt-3 border-top border-secondary border-opacity-25 mt-auto">
                                    <a href="<?= $maps_url ?>" 
                                       target="_blank" 
                                       rel="noopener noreferrer" 
                                       class="btn btn-outline-info w-100 fw-medium d-flex align-items-center justify-content-center gap-2">
                                        <i class="bi bi-map-fill text-info"></i>
                                        <span>เปิด Google Maps นำทาง</span>
                                        <i class="bi bi-box-arrow-up-right small"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5 border border-secondary border-opacity-25 rounded-4 bg-black bg-opacity-25">
                <i class="bi bi-geo-alt text-secondary fs-1 mb-3 d-block"></i>
                <h4 class="text-white">ไม่พบสถานที่ชมดาวตามเงื่อนไขที่ค้นหา</h4>
                <p class="text-secondary small mb-3">ลองค้นหาด้วยชื่ออำเภอ จังหวัด หรือเลือกดูระดับความมืดอื่น</p>
                <a href="locations.php" class="btn btn-outline-info btn-sm">ดูสถานที่ทั้งหมด 77 จังหวัด</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php 
include 'footer.php'; 
?>
