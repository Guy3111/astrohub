<?php
/**
 * admin_articles.php
 * จัดการบทความความรู้ดาราศาสตร์ (ตาราง articles) - เพิ่ม ลบ แก้ไข พร้อมรองรับการอัปโหลดรูปภาพจริงหรือระบุ URL
 */
include 'admin_header.php';

$success_msg = '';
$error_msg   = '';

// โฟลเดอร์สำหรับเก็บรูปภาพ
$upload_dir = 'uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// -------------------------------------------------------------
// 1. จัดการการลบข้อมูล (Delete)
// -------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $del_id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM articles WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    if ($stmt->execute()) {
        $success_msg = "ลบบทความรหัส #$del_id เรียบร้อยแล้ว";
    } else {
        $error_msg = "ลบข้อมูลไม่สำเร็จ: " . $conn->error;
    }
}

// -------------------------------------------------------------
// 2. จัดการการบันทึกข้อมูล (Create & Update)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $art_id   = !empty($_POST['art_id']) ? (int)$_POST['art_id'] : 0;
    $title    = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? 'ความรู้ทั่วไป');
    $content  = trim($_POST['content'] ?? '');
    $author   = trim($_POST['author'] ?? 'ASTROHUB Team');
    $image    = trim($_POST['image_url'] ?? '');

    // ตรวจสอบการอัปโหลดไฟล์รูปภาพจริงจากเครื่อง
    if (isset($_FILES['art_file']) && $_FILES['art_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['art_file']['tmp_name'];
        $file_name = $_FILES['art_file']['name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed   = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (in_array($file_ext, $allowed)) {
            $new_file_name = 'art_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.' . $file_ext;
            $target_path   = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $target_path)) {
                $image = $target_path; // บันทึกเป็น uploads/art_xxx.jpg
            } else {
                $error_msg = "เกิดข้อผิดพลาดในการบันทึกไฟล์ภาพไปยังโฟลเดอร์ uploads/";
            }
        } else {
            $error_msg = "ประเภทไฟล์ไม่ถูกต้อง (อนุญาตเฉพาะ .jpg, .jpeg, .png, .webp, .gif)";
        }
    }

    if (empty($title) || empty($content)) {
        $error_msg = "กรุณากรอกชื่อบทความและเนื้อหา";
    } elseif (empty($error_msg)) {
        // หากเป็นการแก้ไขและไม่ได้อัปโหลดหรือกรอกรูปใหม่ ให้ใช้รูปเดิม
        if ($art_id > 0 && empty($image) && !empty($_POST['existing_image'])) {
            $image = $_POST['existing_image'];
        }

        if ($art_id > 0) {
            // อัปเดตข้อมูลเดิม (Update)
            $stmt = $conn->prepare("UPDATE articles SET title=?, category=?, content=?, author=?, image=? WHERE id=?");
            $stmt->bind_param("sssssi", $title, $category, $content, $author, $image, $art_id);
            if ($stmt->execute()) {
                $success_msg = "แก้ไขบทความความรู้เรียบร้อยแล้ว";
            } else {
                $error_msg = "แก้ไขไม่สำเร็จ: " . $conn->error;
            }
        } else {
            // เพิ่มข้อมูลใหม่ (Create)
            $stmt = $conn->prepare("INSERT INTO articles (title, category, content, author, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $title, $category, $content, $author, $image);
            if ($stmt->execute()) {
                $success_msg = "เพิ่มบทความใหม่เรียบร้อยแล้ว (จะแสดงในหน้า knowledge.php และ index.php ทันที)";
            } else {
                $error_msg = "เพิ่มข้อมูลไม่สำเร็จ: " . $conn->error;
            }
        }
    }
}

// -------------------------------------------------------------
// 3. ตรวจสอบว่ากำลังแก้ไขรายการใดอยู่หรือไม่
// -------------------------------------------------------------
$edit_data = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $edit_data = $res->fetch_assoc();
    }
}

// -------------------------------------------------------------
// 4. ดึงข้อมูลทั้งหมดมาแสดงในตาราง
// -------------------------------------------------------------
$articles_list = $conn->query("SELECT * FROM articles ORDER BY id DESC");
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-white fw-bold mb-1">
                <i class="bi bi-journal-bookmark-fill text-success me-2"></i>จัดการบทความความรู้ (articles)
            </h2>
            <p class="text-secondary small mb-0">เพิ่ม แก้ไข ลบ คลังความรู้ดาราศาสตร์ และเปลี่ยนภาพประกอบได้ทั้งแบบไฟล์ในเครื่องหรือ URL</p>
        </div>
        <a href="knowledge.php" target="_blank" class="btn btn-outline-success btn-sm">
            <i class="bi bi-eye me-1"></i>ดูผลลัพธ์หน้า knowledge.php
        </a>
    </div>

    <!-- แจ้งเตือนผลลัพธ์ -->
    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success bg-success bg-opacity-10 border-success text-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success_msg) ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger bg-danger bg-opacity-10 border-danger text-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error_msg) ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- ฝั่งฟอร์ม (เพิ่ม / แก้ไข) -->
        <div class="col-lg-4">
            <div class="card card-custom p-4">
                <h5 class="text-white fw-bold mb-3">
                    <i class="bi <?= $edit_data ? 'bi-pencil-square text-warning' : 'bi-plus-circle-fill text-success' ?> me-2"></i>
                    <?= $edit_data ? 'แก้ไขบทความ' : 'เพิ่มบทความใหม่' ?>
                </h5>

                <form method="POST" action="admin_articles.php" enctype="multipart/form-data">
                    <input type="hidden" name="art_id" value="<?= $edit_data['id'] ?? 0 ?>">
                    <input type="hidden" name="existing_image" value="<?= htmlspecialchars($edit_data['image'] ?? '') ?>">

                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">ชื่อเรื่องบทความ <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control bg-black text-light border-secondary border-opacity-50" required 
                               value="<?= htmlspecialchars($edit_data['title'] ?? '') ?>" placeholder="เช่น เทคนิคการสังเกตดาวพฤหัสบดี">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-light small fw-medium">หมวดหมู่</label>
                            <input type="text" name="category" class="form-control bg-black text-light border-secondary border-opacity-50" 
                                   value="<?= htmlspecialchars($edit_data['category'] ?? '') ?>" placeholder="เช่น มือใหม่หัดดูดาว">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-light small fw-medium">ผู้เขียน</label>
                            <input type="text" name="author" class="form-control bg-black text-light border-secondary border-opacity-50" 
                                   value="<?= htmlspecialchars($edit_data['author'] ?? 'ASTROHUB Team') ?>" placeholder="เช่น ดร. นภา">
                        </div>
                    </div>

                    <!-- ส่วนจัดการรูปภาพ: รองรับทั้งไฟล์และ URL -->
                    <div class="mb-3 p-3 bg-black bg-opacity-40 rounded-3 border border-secondary border-opacity-25">
                        <label class="form-label text-info small fw-bold d-block mb-1">
                            <i class="bi bi-upload me-1"></i> 1. อัปโหลดรูปภาพจากเครื่อง:
                        </label>
                        <input type="file" name="art_file" class="form-control form-control-sm bg-dark text-light border-secondary border-opacity-50" accept="image/*">
                        <small class="text-secondary d-block mt-1">ไฟล์จะถูกบันทึกไว้ในโฟลเดอร์ <code>uploads/</code></small>

                        <div class="text-center my-2 text-secondary small">-- หรือ --</div>

                        <label class="form-label text-light small fw-medium mb-1">2. ระบุเป็น URL รูปภาพ:</label>
                        <input type="text" name="image_url" class="form-control form-control-sm bg-black text-light border-secondary border-opacity-50" 
                               value="<?= htmlspecialchars($edit_data['image'] ?? '') ?>" placeholder="https://... หรือ uploads/ชื่อรูป.jpg">
                    </div>

                    <?php if (!empty($edit_data['image'])): ?>
                        <div class="mb-3">
                            <small class="text-secondary d-block mb-1">รูปภาพปัจจุบัน:</small>
                            <img src="<?= htmlspecialchars($edit_data['image']) ?>" class="img-fluid rounded border border-secondary border-opacity-25" style="max-height: 120px; width: 100%; object-fit: cover;">
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">เนื้อหาบทความ <span class="text-danger">*</span></label>
                        <textarea name="content" rows="6" class="form-control bg-black text-light border-secondary border-opacity-50" 
                                  placeholder="พิมพ์เนื้อหาบทความ..." required><?= htmlspecialchars($edit_data['content'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn <?= $edit_data ? 'btn-warning' : 'btn-success' ?> w-100 fw-semibold text-dark">
                            <i class="bi bi-save me-1"></i><?= $edit_data ? 'บันทึกการแก้ไข' : 'เพิ่มบทความ' ?>
                        </button>
                        <?php if ($edit_data): ?>
                            <a href="admin_articles.php" class="btn btn-secondary">ยกเลิก</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- ฝั่งตารางแสดงรายการ (Read, Edit, Delete) -->
        <div class="col-lg-8">
            <div class="card card-custom p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="text-white fw-bold mb-0">รายการบทความทั้งหมดในระบบ</h5>
                    <span class="badge bg-success text-dark"><?= $articles_list ? $articles_list->num_rows : 0 ?> เรื่อง</span>
                </div>

                <div class="table-responsive" style="max-height: 650px; overflow-y: auto;">
                    <table class="table table-dark table-hover table-dark-custom align-middle small mb-0">
                        <thead class="sticky-top bg-dark">
                            <tr>
                                <th style="width: 50px;">ID</th>
                                <th>รูปภาพ</th>
                                <th>ชื่อเรื่องบทความ</th>
                                <th>หมวดหมู่</th>
                                <th>ผู้เขียน</th>
                                <th class="text-center" style="width: 100px;">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($articles_list && $articles_list->num_rows > 0): ?>
                                <?php while ($art = $articles_list->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-secondary">#<?= $art['id'] ?></td>
                                        <td style="width: 70px;">
                                            <img src="<?= htmlspecialchars($art['image']) ?>" 
                                                 alt="<?= htmlspecialchars($art['title']) ?>" 
                                                 class="rounded border border-secondary border-opacity-25" 
                                                 style="width: 60px; height: 42px; object-fit: cover;"
                                                 onerror="this.src='https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=800&q=80'">
                                        </td>
                                        <td>
                                            <strong class="text-white d-block"><?= htmlspecialchars($art['title']) ?></strong>
                                            <small class="text-secondary text-truncate d-block" style="max-width: 250px;">
                                                <?= htmlspecialchars(mb_substr(strip_tags($art['content']), 0, 80)) ?>...
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                                <?= htmlspecialchars($art['category'] ?? 'ทั่วไป') ?>
                                            </span>
                                        </td>
                                        <td class="text-secondary"><?= htmlspecialchars($art['author'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <a href="admin_articles.php?action=edit&id=<?= $art['id'] ?>" class="btn btn-sm btn-outline-warning p-1 px-2" title="แก้ไข">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <a href="admin_articles.php?action=delete&id=<?= $art['id'] ?>" class="btn btn-sm btn-outline-danger p-1 px-2" 
                                               onclick="return confirm('ยืนยันที่จะลบบทความ <?= addslashes($art['title']) ?> ใช่หรือไม่?');" title="ลบ">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-secondary">ยังไม่มีข้อมูลบทความในระบบ</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>

