<?php
/**
 * knowledge.php
 * คลังความรู้และบทความดาราศาสตร์ - ดึงข้อมูลทั้งหมดจากตาราง articles
 */
require_once 'db_connect.php';
include 'header.php';

// รับค่าตัวกรองหมวดหมู่ถ้ามี
$selected_category = isset($_GET['cat']) ? trim($_GET['cat']) : '';

// ดึงหมวดหมู่ทั้งหมดที่มีในฐานข้อมูล
$cat_sql = "SELECT DISTINCT category FROM articles WHERE category IS NOT NULL AND category != ''";
$cat_query = $conn->query($cat_sql);
$categories = [];
if ($cat_query) {
    while ($c = $cat_query->fetch_assoc()) {
        $categories[] = $c['category'];
    }
}

// ดึงบทความตามหมวดหมู่ หรือทั้งหมด
if (!empty($selected_category)) {
    $stmt = $conn->prepare("SELECT * FROM articles WHERE category = ? ORDER BY id DESC");
    $stmt->bind_param("s", $selected_category);
    $stmt->execute();
    $articles_query = $stmt->get_result();
} else {
    $articles_sql = "SELECT * FROM articles ORDER BY id DESC";
    $articles_query = $conn->query($articles_sql);
}

$articles = [];
if ($articles_query && $articles_query->num_rows > 0) {
    while ($row = $articles_query->fetch_assoc()) {
        $articles[] = $row;
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
    .article-card {
        background: #111827;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .article-card:hover {
        transform: translateY(-6px);
        border-color: rgba(56, 189, 248, 0.45);
        box-shadow: 0 16px 35px rgba(0, 0, 0, 0.6), 0 0 20px rgba(56, 189, 248, 0.15);
    }
    .art-img-wrapper {
        position: relative;
        height: 220px;
        background-color: #0b0f19;
        overflow: hidden;
    }
    .art-img-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .article-card:hover .art-img-wrapper img {
        transform: scale(1.08);
    }
    .cat-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        backdrop-filter: blur(8px);
    }
    .filter-btn {
        transition: all 0.2s ease;
        border-radius: 50px;
        padding: 6px 18px;
    }
    .filter-btn.active {
        background-color: #38bdf8 !important;
        color: #080b12 !important;
        font-weight: 600;
        border-color: #38bdf8 !important;
    }
</style>

<!-- Header หน้าบทความ -->
<section class="page-header text-center">
    <div class="container">
        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2 rounded-pill mb-3">
            <i class="bi bi-journal-richtext me-1"></i> Astronomy Knowledge Hub
        </span>
        <h1 class="display-6 fw-bold text-white mb-2">คลังความรู้ดาราศาสตร์น่าอ่าน</h1>
        <p class="text-secondary lead fs-6 mx-auto mb-0" style="max-width: 650px;">
            บทความ เทคนิคการดูดาว การใช้กล้องโทรทรรศน์ และเกร็ดความรู้จักรวาล ทั้งสำหรับมือใหม่และนักดาราศาสตร์สมัครเล่น
        </p>
    </div>
</section>

<!-- เนื้อหาบทความ -->
<section class="py-5">
    <div class="container py-2">
        <!-- ตัวกรองหมวดหมู่ -->
        <div class="d-flex flex-wrap gap-2 justify-content-center mb-5">
            <a href="knowledge.php" class="btn btn-outline-secondary filter-btn <?= empty($selected_category) ? 'active' : 'text-light' ?>">
                <i class="bi bi-grid-fill me-1"></i> บทความทั้งหมด
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="knowledge.php?cat=<?= urlencode($cat) ?>" 
                   class="btn btn-outline-secondary filter-btn <?= ($selected_category == $cat) ? 'active' : 'text-light' ?>">
                    <i class="bi bi-tag-fill me-1"></i> <?= htmlspecialchars($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="text-secondary small">
                แสดงบทความ <strong class="text-info"><?= count($articles) ?></strong> เรื่อง 
                <?= !empty($selected_category) ? "ในหมวดหมู่ <span class='badge bg-info text-dark'>".htmlspecialchars($selected_category)."</span>" : "" ?>
            </div>
            <div class="text-secondary small">
                <i class="bi bi-book me-1 text-info"></i> อัปเดตเนื้อหาความรู้ต่อเนื่อง
            </div>
        </div>

        <?php if (!empty($articles)): ?>
            <div class="row g-4">
                <?php 
                    // รูปภาพสำรองตามเนื้อหาดาราศาสตร์
                    $default_art_images = [
                        "https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=800&q=80",
                        "https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?auto=format&fit=crop&w=800&q=80",
                        "https://images.unsplash.com/photo-1502134249126-9f3755a50d78?auto=format&fit=crop&w=800&q=80",
                        "https://images.unsplash.com/photo-1506703719100-a0f3a48c0f86?auto=format&fit=crop&w=800&q=80"
                    ];
                ?>
                <?php foreach ($articles as $index => $art): ?>
                    <?php 
                        // ตรวจสอบรูปภาพ
                        $art_img = (!empty($art['image']) && file_exists($art['image']))
                            ? htmlspecialchars($art['image'])
                            : $default_art_images[$index % count($default_art_images)];
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="article-card">
                            <!-- รูปภาพ & ป้ายหมวดหมู่ -->
                            <div class="art-img-wrapper">
                                <img src="<?= $art_img ?>" alt="<?= htmlspecialchars($art['title']) ?>">
                                
                                <?php if (!empty($art['category'])): ?>
                                    <div class="cat-badge">
                                        <span class="badge bg-info text-dark fw-semibold px-2 py-1 shadow-sm">
                                            <i class="bi bi-bookmark-fill me-1"></i><?= htmlspecialchars($art['category']) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- เนื้อหาบทความ -->
                            <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                <h4 class="text-white fw-bold mb-2 fs-5 line-clamp-2">
                                    <?= htmlspecialchars($art['title']) ?>
                                </h4>

                                <p class="text-secondary small mb-4 flex-grow-1 leading-relaxed">
                                    <?= nl2br(htmlspecialchars($art['content'] ?? '')) ?>
                                </p>

                                <!-- ผู้เขียนและปุ่มอ่านเนื้อหา -->
                                <div class="d-flex justify-content-between align-items-center pt-3 border-top border-secondary border-opacity-25 mt-auto">
                                    <div class="d-flex align-items-center gap-2 small text-secondary">
                                        <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                        <span><?= htmlspecialchars($art['author'] ?? 'ASTROHUB Team') ?></span>
                                    </div>

                                    <!-- ปุ่มเปิด Modal อ่านบทความฉบับเต็ม -->
                                    <button type="button" 
                                            class="btn btn-outline-info btn-sm fw-medium"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#articleModal<?= $art['id'] ?>">
                                        อ่านเนื้อหา <i class="bi bi-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal แสดงเนื้อหาบทความแบบเต็ม -->
                    <div class="modal fade" id="articleModal<?= $art['id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $art['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content bg-dark text-light border border-secondary border-opacity-25">
                                <div class="modal-header border-secondary border-opacity-25">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-info text-dark"><?= htmlspecialchars($art['category'] ?? 'บทความ') ?></span>
                                        <h5 class="modal-title fw-bold text-white mb-0" id="modalLabel<?= $art['id'] ?>">
                                            <?= htmlspecialchars($art['title']) ?>
                                        </h5>
                                    </div>
                                    <button type="button" class="btn-close btn-close-white" data-bs-target="#articleModal<?= $art['id'] ?>" data-bs-dismiss="modal" aria-label="ปิด"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <img src="<?= $art_img ?>" alt="<?= htmlspecialchars($art['title']) ?>" class="img-fluid rounded-3 mb-4 w-100" style="max-height: 320px; object-fit: cover;">
                                    <div class="d-flex align-items-center gap-3 text-secondary small mb-3">
                                        <span><i class="bi bi-person-circle text-info me-1"></i> ผู้เขียน: <?= htmlspecialchars($art['author'] ?? 'ASTROHUB') ?></span>
                                        <span><i class="bi bi-calendar3 text-info me-1"></i> แหล่งความรู้ ASTROHUB</span>
                                    </div>
                                    <div class="p-3 bg-black bg-opacity-40 rounded-3 text-light leading-loose fs-6">
                                        <?= nl2br(htmlspecialchars($art['content'] ?? '')) ?>
                                    </div>
                                </div>
                                <div class="modal-footer border-secondary border-opacity-25">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5 border border-secondary border-opacity-25 rounded-4 bg-black bg-opacity-25">
                <i class="bi bi-journal-x text-secondary fs-1 mb-3 d-block"></i>
                <h4 class="text-white">ไม่พบบทความในหมวดหมู่นี้</h4>
                <p class="text-secondary small mb-3">ยังไม่มีเนื้อหาสำหรับหมวดหมู่นี้ หรือกำลังอยู่ในระหว่างการจัดเตรียม</p>
                <a href="knowledge.php" class="btn btn-outline-info btn-sm">ดูบทความทั้งหมด</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php 
include 'footer.php'; 
?>
