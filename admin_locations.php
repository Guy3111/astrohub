<?php
/**
 * admin_locations.php
 * จัดการข้อมูลสถานที่ชมดาว (ตาราง locations) - เพิ่ม ลบ แก้ไข พร้อมรองรับการอัปโหลดไฟล์รูปภาพจริง
 */
include 'admin_header.php';

$success_msg = '';
$error_msg   = '';

// สร้างโฟลเดอร์ uploads อัตโนมัติถ้ายังไม่มี
$upload_dir = 'uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// -------------------------------------------------------------
// 1. จัดการการลบข้อมูล (Delete)
// -------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $del_id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM locations WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    if ($stmt->execute()) {
        $success_msg = "ลบข้อมูลสถานที่รหัส #$del_id เรียบร้อยแล้ว";
    } else {
        $error_msg = "ลบข้อมูลไม่สำเร็จ: " . $conn->error;
    }
}

// -------------------------------------------------------------
// 2. จัดการการบันทึกข้อมูล (Create & Update พร้อม Upload รูปจริง)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loc_id         = !empty($_POST['loc_id']) ? (int)$_POST['loc_id'] : 0;
    $name           = trim($_POST['name'] ?? '');
    $province       = trim($_POST['province'] ?? '');
    $darkness_level = isset($_POST['darkness_level']) ? (int)$_POST['darkness_level'] : 3;
    $facilities     = trim($_POST['facilities'] ?? '');
    $cover_image    = trim($_POST['cover_image_url'] ?? '');

    // ตรวจสอบว่ามีการอัปโหลดไฟล์รูปภาพจริงจากเครื่องหรือไม่
    if (isset($_FILES['cover_file']) && $_FILES['cover_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['cover_file']['tmp_name'];
        $file_name = $_FILES['cover_file']['name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed   = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (in_array($file_ext, $allowed)) {
            // ตั้งชื่อไฟล์ใหม่เพื่อป้องกันชื่อซ้ำ เช่น loc_1697200000_abc12.jpg
            $new_file_name = 'loc_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.' . $file_ext;
            $target_path   = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $target_path)) {
                $cover_image = $target_path; // เก็บเป็น path เช่น uploads/loc_xxx.jpg
            } else {
                $error_msg = "เกิดข้อผิดพลาดในการบันทึกไฟล์รูปภาพไปยังโฟลเดอร์ uploads/";
            }
        } else {
            $error_msg = "ประเภทไฟล์ไม่ถูกต้อง (อนุญาตเฉพาะ .jpg, .jpeg, .png, .webp)";
        }
    }

    if (empty($name) || empty($province)) {
        $error_msg = "กรุณากรอกชื่อสถานที่และจังหวัด";
    } elseif (empty($error_msg)) {
        if ($loc_id > 0) {
            // กรณีแก้ไข ถ้าไม่ได้อัปโหลดรูปใหม่และไม่ได้กรอก URL ใหม่ ให้ใช้รูปเดิม
            if (empty($cover_image) && !empty($_POST['existing_image'])) {
                $cover_image = $_POST['existing_image'];
            }

            // แก้ไข (Update)
            $stmt = $conn->prepare("UPDATE locations SET name=?, province=?, darkness_level=?, facilities=?, cover_image=? WHERE id=?");
            $stmt->bind_param("ssissi", $name, $province, $darkness_level, $facilities, $cover_image, $loc_id);
            if ($stmt->execute()) {
                $success_msg = "แก้ไขข้อมูลสถานที่ชมดาวเรียบร้อยแล้ว";
            } else {
                $error_msg = "แก้ไขไม่สำเร็จ: " . $conn->error;
            }
        } else {
            // เพิ่มใหม่ (Create)
            $stmt = $conn->prepare("INSERT INTO locations (name, province, darkness_level, facilities, cover_image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiss", $name, $province, $darkness_level, $facilities, $cover_image);
            if ($stmt->execute()) {
                $success_msg = "เพิ่มสถานที่ชมดาวแห่งใหม่สำเร็จ (จะแสดงใน locations.php และ index.php ทันที)";
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
    $stmt = $conn->prepare("SELECT * FROM locations WHERE id = ?");
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
$locations_list = $conn->query("SELECT * FROM locations ORDER BY darkness_level DESC, id ASC");
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-white fw-bold mb-1">
                <i class="bi bi-geo-alt text-warning me-2"></i>จัดการสถานที่ชมดาว (locations)
            </h2>
            <p class="text-secondary small mb-0">เพิ่ม แก้ไข ลบ พิกัดดูดาว ปรับระดับความมืด และอัปโหลดรูปภาพจริงได้จากเครื่อง</p>
        </div>
        <a href="locations.php" target="_blank" class="btn btn-outline-warning btn-sm">
            <i class="bi bi-eye me-1"></i>ดูผลลัพธ์หน้า locations.php
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
                    <i class="bi <?= $edit_data ? 'bi-pencil-square text-warning' : 'bi-plus-circle-fill text-warning' ?> me-2"></i>
                    <?= $edit_data ? 'แก้ไขสถานที่ชมดาว' : 'เพิ่มสถานที่ชมดาวใหม่' ?>
                </h5>

                <form method="POST" action="admin_locations.php" enctype="multipart/form-data">
                    <input type="hidden" name="loc_id" value="<?= $edit_data['id'] ?? 0 ?>">
                    <input type="hidden" name="existing_image" value="<?= htmlspecialchars($edit_data['cover_image'] ?? '') ?>">

                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">ชื่อสถานที่ <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control bg-black text-light border-secondary border-opacity-50" required 
                               value="<?= htmlspecialchars($edit_data['name'] ?? '') ?>" placeholder="เช่น ยอดดอยอินทนนท์">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-light small fw-medium">จังหวัด <span class="text-danger">*</span></label>
                            <input type="text" name="province" class="form-control bg-black text-light border-secondary border-opacity-50" required 
                                   value="<?= htmlspecialchars($edit_data['province'] ?? '') ?>" placeholder="เช่น เชียงใหม่">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-light small fw-medium">ระดับความมืด (1-5)</label>
                            <select name="darkness_level" class="form-select bg-black text-light border-secondary border-opacity-50">
                                <?php $dark = (int)($edit_data['darkness_level'] ?? 4); ?>
                                <option value="5" <?= $dark === 5 ? 'selected' : '' ?>>ระดับ 5 (มืดสนิท)</option>
                                <option value="4" <?= $dark === 4 ? 'selected' : '' ?>>ระดับ 4 (มืดมาก)</option>
                                <option value="3" <?= $dark === 3 ? 'selected' : '' ?>>ระดับ 3 (ปานกลาง)</option>
                                <option value="2" <?= $dark === 2 ? 'selected' : '' ?>>ระดับ 2 (มีแสงเมือง)</option>
                                <option value="1" <?= $dark === 1 ? 'selected' : '' ?>>ระดับ 1 (แสงรบกวนสูง)</option>
                            </select>
                        </div>
                    </div>

                    <!-- อัปโหลดไฟล์รูปภาพจริงจากคอมพิวเตอร์ -->
                    <div class="mb-3 p-3 bg-black bg-opacity-40 rounded-3 border border-secondary border-opacity-25">
                        <label class="form-label text-info small fw-bold d-block mb-1">
                            <i class="bi bi-upload me-1"></i> 1. อัปโหลดรูปภาพจริงจากเครื่อง (แนะนำ):
                        </label>
                        <input type="file" name="cover_file" class="form-control form-control-sm bg-dark text-light border-secondary border-opacity-50" accept="image/*">
                        <small class="text-secondary d-block mt-1">ไฟล์จะถูกบันทึกไว้ในโฟลเดอร์ <code>uploads/</code> อัตโนมัติ</small>

                        <div class="text-center my-2 text-secondary small">-- หรือ --</div>

                        <label class="form-label text-light small fw-medium mb-1">2. ระบุเป็น URL รูปภาพ:</label>
                        <input type="text" name="cover_image_url" class="form-control form-control-sm bg-black text-light border-secondary border-opacity-50" 
                               value="<?= htmlspecialchars($edit_data['cover_image'] ?? '') ?>" placeholder="https://... หรือ uploads/ชื่อรูป.jpg">
                    </div>

                    <?php if (!empty($edit_data['cover_image'])): ?>
                        <div class="mb-3">
                            <small class="text-secondary d-block mb-1">รูปภาพปัจจุบัน:</small>
                            <img src="<?= htmlspecialchars($edit_data['cover_image']) ?>" class="img-fluid rounded border border-secondary border-opacity-25" style="max-height: 120px; width: 100%; object-fit: cover;">
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">สิ่งอำนวยความสะดวก</label>
                        <textarea name="facilities" rows="3" class="form-control bg-black text-light border-secondary border-opacity-50" 
                                  placeholder="เช่น ห้องน้ำ, ลานกางเต็นท์, จุดต่อไฟฟ้า, ที่จอดรถ"><?= htmlspecialchars($edit_data['facilities'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning w-100 fw-semibold text-dark">
                            <i class="bi bi-save me-1"></i><?= $edit_data ? 'บันทึกการแก้ไข' : 'เพิ่มสถานที่' ?>
                        </button>
                        <?php if ($edit_data): ?>
                            <a href="admin_locations.php" class="btn btn-secondary">ยกเลิก</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- ฝั่งตารางแสดงรายการ (Read, Edit, Delete) -->
        <div class="col-lg-8">
            <div class="card card-custom p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="text-white fw-bold mb-0">รายการสถานที่ชมดาวทั้งหมด</h5>
                    <span class="badge bg-warning text-dark"><?= $locations_list ? $locations_list->num_rows : 0 ?> แห่ง</span>
                </div>

                <div class="table-responsive" style="max-height: 650px; overflow-y: auto;">
                    <table class="table table-dark table-hover table-dark-custom align-middle small mb-0">
                        <thead class="sticky-top bg-dark">
                            <tr>
                                <th style="width: 50px;">ID</th>
                                <th>รูปภาพ</th>
                                <th>ชื่อสถานที่</th>
                                <th>จังหวัด</th>
                                <th>ระดับความมืด</th>
                                <th class="text-center" style="width: 100px;">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($locations_list && $locations_list->num_rows > 0): ?>
                                <?php while ($loc = $locations_list->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-secondary">#<?= $loc['id'] ?></td>
                                        <td style="width: 70px;">
                                            <img src="<?= htmlspecialchars($loc['cover_image']) ?>" 
                                                 alt="<?= htmlspecialchars($loc['name']) ?>" 
                                                 class="rounded border border-secondary border-opacity-25" 
                                                 style="width: 60px; height: 42px; object-fit: cover;"
                                                 onerror="this.src='https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=800&q=80'">
                                        </td>
                                        <td>
                                            <strong class="text-white d-block"><?= htmlspecialchars($loc['name']) ?></strong>
                                            <small class="text-secondary text-truncate d-block" style="max-width: 200px;">
                                                <?= htmlspecialchars($loc['facilities'] ?? '-') ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                                <?= htmlspecialchars($loc['province']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="text-warning">
                                                <?php for ($s = 1; $s <= 5; $s++): ?>
                                                    <i class="bi bi-moon-stars-fill <?= ($s <= (int)$loc['darkness_level']) ? 'text-warning' : 'text-secondary opacity-25' ?> small"></i>
                                                <?php endfor; ?>
                                                <small class="ms-1 text-secondary">(<?= $loc['darkness_level'] ?>/5)</small>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <a href="admin_locations.php?action=edit&id=<?= $loc['id'] ?>" class="btn btn-sm btn-outline-warning p-1 px-2" title="แก้ไข">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <a href="admin_locations.php?action=delete&id=<?= $loc['id'] ?>" class="btn btn-sm btn-outline-danger p-1 px-2" 
                                               onclick="return confirm('ยืนยันที่จะลบสถานที่ <?= addslashes($loc['name']) ?> ใช่หรือไม่?');" title="ลบ">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-secondary">ยังไม่มีข้อมูลสถานที่ชมดาวในระบบ</td>
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
