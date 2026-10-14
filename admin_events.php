<?php
/**
 * admin_events.php
 * จัดการข้อมูลปรากฏการณ์ดาราศาสตร์ (ตาราง events) - เพิ่ม ลบ แก้ไข พร้อมรองรับทั้งไฟล์ในเครื่องและ URL
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
    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    if ($stmt->execute()) {
        $success_msg = "ลบข้อมูลปรากฏการณ์รหัส #$del_id สำเร็จแล้ว";
    } else {
        $error_msg = "ลบข้อมูลไม่สำเร็จ: " . $conn->error;
    }
}

// -------------------------------------------------------------
// 2. จัดการการบันทึกข้อมูล (Create & Update)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id         = !empty($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
    $title            = trim($_POST['title'] ?? '');
    $category         = trim($_POST['category'] ?? '');
    $event_date       = trim($_POST['event_date'] ?? '');
    $peak_time        = trim($_POST['peak_time'] ?? '');
    $direction        = trim($_POST['direction'] ?? '');
    $visibility_level = trim($_POST['visibility_level'] ?? '');
    $description      = trim($_POST['description'] ?? '');
    $image_url        = trim($_POST['image_url'] ?? '');

    // ตรวจสอบการอัปโหลดไฟล์รูปภาพจากเครื่อง
    if (isset($_FILES['event_file']) && $_FILES['event_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['event_file']['tmp_name'];
        $file_name = $_FILES['event_file']['name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed   = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (in_array($file_ext, $allowed)) {
            $new_file_name = 'event_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.' . $file_ext;
            $target_path   = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $target_path)) {
                $image_url = $target_path; // บันทึกเป็น uploads/event_xxx.jpg
            } else {
                $error_msg = "เกิดข้อผิดพลาดในการบันทึกไฟล์ภาพไปยังโฟลเดอร์ uploads/";
            }
        } else {
            $error_msg = "ประเภทไฟล์ไม่ถูกต้อง (อนุญาตเฉพาะ .jpg, .jpeg, .png, .webp, .gif)";
        }
    }

    if (empty($title) || empty($event_date)) {
        $error_msg = "กรุณากรอกชื่อปรากฏการณ์และวันที่สังเกตการณ์";
    } elseif (empty($error_msg)) {
        // หากเป็นการแก้ไขและไม่ได้อัปโหลดหรือกรอกรูปใหม่ ให้ใช้รูปเดิม
        if ($event_id > 0 && empty($image_url) && !empty($_POST['existing_image'])) {
            $image_url = $_POST['existing_image'];
        }

        if ($event_id > 0) {
            // อัปเดตข้อมูลเดิม (Update)
            $stmt = $conn->prepare("UPDATE events SET title=?, category=?, event_date=?, peak_time=?, direction=?, visibility_level=?, description=?, image_url=? WHERE id=?");
            $stmt->bind_param("ssssssssi", $title, $category, $event_date, $peak_time, $direction, $visibility_level, $description, $image_url, $event_id);
            if ($stmt->execute()) {
                $success_msg = "แก้ไขข้อมูลปรากฏการณ์เรียบร้อยแล้ว";
            } else {
                $error_msg = "แก้ไขไม่สำเร็จ: " . $conn->error;
            }
        } else {
            // เพิ่มข้อมูลใหม่ (Create)
            $stmt = $conn->prepare("INSERT INTO events (title, category, event_date, peak_time, direction, visibility_level, description, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $title, $category, $event_date, $peak_time, $direction, $visibility_level, $description, $image_url);
            if ($stmt->execute()) {
                $success_msg = "เพิ่มปรากฏการณ์ใหม่เรียบร้อยแล้ว (จะแสดงในหน้า calendar.php และ index.php ทันที)";
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
    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
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
$events_list = $conn->query("SELECT * FROM events ORDER BY event_date ASC, id ASC");
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-white fw-bold mb-1">
                <i class="bi bi-calendar-event text-info me-2"></i>จัดการปรากฏการณ์ดาราศาสตร์ (events)
            </h2>
            <p class="text-secondary small mb-0">เพิ่ม แก้ไข ลบ ปรากฏการณ์บนท้องฟ้า และเปลี่ยนรูปภาพได้ทั้งแบบอัปโหลดจากเครื่องหรือใส่ลิงก์ URL</p>
        </div>
        <a href="calendar.php" target="_blank" class="btn btn-outline-info btn-sm">
            <i class="bi bi-eye me-1"></i>ดูผลลัพธ์หน้า calendar.php
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
                    <i class="bi <?= $edit_data ? 'bi-pencil-square text-warning' : 'bi-plus-circle-fill text-info' ?> me-2"></i>
                    <?= $edit_data ? 'แก้ไขปรากฏการณ์' : 'เพิ่มปรากฏการณ์ใหม่' ?>
                </h5>

                <form method="POST" action="admin_events.php" enctype="multipart/form-data">
                    <input type="hidden" name="event_id" value="<?= $edit_data['id'] ?? 0 ?>">
                    <input type="hidden" name="existing_image" value="<?= htmlspecialchars($edit_data['image_url'] ?? '') ?>">

                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">ชื่อปรากฏการณ์ <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control bg-black text-light border-secondary border-opacity-50" required 
                               value="<?= htmlspecialchars($edit_data['title'] ?? '') ?>" placeholder="เช่น ฝนดาวตกโอไรออนิดส์">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-light small fw-medium">หมวดหมู่</label>
                            <input type="text" name="category" class="form-control bg-black text-light border-secondary border-opacity-50" 
                                   value="<?= htmlspecialchars($edit_data['category'] ?? '') ?>" placeholder="เช่น ฝนดาวตก">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-light small fw-medium">วันที่จัดงาน <span class="text-danger">*</span></label>
                            <input type="date" name="event_date" class="form-control bg-black text-light border-secondary border-opacity-50" required 
                                   value="<?= htmlspecialchars($edit_data['event_date'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-light small fw-medium">เวลาที่สังเกตได้ดี</label>
                            <input type="text" name="peak_time" class="form-control bg-black text-light border-secondary border-opacity-50" 
                                   value="<?= htmlspecialchars($edit_data['peak_time'] ?? '') ?>" placeholder="เช่น 23:00 - 03:00 น.">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-light small fw-medium">ทิศทางการมอง</label>
                            <input type="text" name="direction" class="form-control bg-black text-light border-secondary border-opacity-50" 
                                   value="<?= htmlspecialchars($edit_data['direction'] ?? '') ?>" placeholder="เช่น ทิศตะวันออก">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">ระดับการมองเห็น</label>
                        <select name="visibility_level" class="form-select bg-black text-light border-secondary border-opacity-50">
                            <?php $vis = $edit_data['visibility_level'] ?? 'มองเห็นด้วยตาเปล่า'; ?>
                            <option value="มองเห็นด้วยตาเปล่า" <?= $vis === 'มองเห็นด้วยตาเปล่า' ? 'selected' : '' ?>>มองเห็นด้วยตาเปล่า</option>
                            <option value="กล้องสองตา (Binoculars)" <?= $vis === 'กล้องสองตา (Binoculars)' ? 'selected' : '' ?>>กล้องสองตา (Binoculars)</option>
                            <option value="กล้องโทรทรรศน์" <?= $vis === 'กล้องโทรทรรศน์' ? 'selected' : '' ?>>กล้องโทรทรรศน์</option>
                            <option value="ต้องใช้แว่นดูดวงอาทิตย์" <?= $vis === 'ต้องใช้แว่นดูดวงอาทิตย์' ? 'selected' : '' ?>>ต้องใช้แว่นดูดวงอาทิตย์</option>
                        </select>
                    </div>

                    <!-- ส่วนจัดการรูปภาพ: รองรับทั้งไฟล์และ URL -->
                    <div class="mb-3 p-3 bg-black bg-opacity-40 rounded-3 border border-secondary border-opacity-25">
                        <label class="form-label text-info small fw-bold d-block mb-1">
                            <i class="bi bi-upload me-1"></i> 1. อัปโหลดรูปภาพจากเครื่อง:
                        </label>
                        <input type="file" name="event_file" class="form-control form-control-sm bg-dark text-light border-secondary border-opacity-50" accept="image/*">
                        <small class="text-secondary d-block mt-1">ไฟล์จะถูกบันทึกไว้ในโฟลเดอร์ <code>uploads/</code></small>

                        <div class="text-center my-2 text-secondary small">-- หรือ --</div>

                        <label class="form-label text-light small fw-medium mb-1">2. ระบุเป็น URL รูปภาพ:</label>
                        <input type="text" name="image_url" class="form-control form-control-sm bg-black text-light border-secondary border-opacity-50" 
                               value="<?= htmlspecialchars($edit_data['image_url'] ?? '') ?>" placeholder="https://... หรือ uploads/ชื่อรูป.jpg">
                    </div>

                    <?php if (!empty($edit_data['image_url'])): ?>
                        <div class="mb-3">
                            <small class="text-secondary d-block mb-1">รูปภาพปัจจุบัน:</small>
                            <img src="<?= htmlspecialchars($edit_data['image_url']) ?>" class="img-fluid rounded border border-secondary border-opacity-25" style="max-height: 120px; width: 100%; object-fit: cover;">
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">คำอธิบายรายละเอียด</label>
                        <textarea name="description" rows="3" class="form-control bg-black text-light border-secondary border-opacity-50" 
                                  placeholder="รายละเอียดปรากฏการณ์"><?= htmlspecialchars($edit_data['description'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn <?= $edit_data ? 'btn-warning' : 'btn-info' ?> w-100 fw-semibold text-dark">
                            <i class="bi bi-save me-1"></i><?= $edit_data ? 'บันทึกการแก้ไข' : 'เพิ่มข้อมูล' ?>
                        </button>
                        <?php if ($edit_data): ?>
                            <a href="admin_events.php" class="btn btn-secondary">ยกเลิก</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- ฝั่งตารางแสดงรายการ (Read, Edit, Delete) -->
        <div class="col-lg-8">
            <div class="card card-custom p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="text-white fw-bold mb-0">รายการปรากฏการณ์ทั้งหมดในระบบ</h5>
                    <span class="badge bg-info text-dark"><?= $events_list ? $events_list->num_rows : 0 ?> รายการ</span>
                </div>

                <div class="table-responsive" style="max-height: 650px; overflow-y: auto;">
                    <table class="table table-dark table-hover table-dark-custom align-middle small mb-0">
                        <thead class="sticky-top bg-dark">
                            <tr>
                                <th style="width: 50px;">ID</th>
                                <th>รูปภาพ</th>
                                <th>ชื่อปรากฏการณ์</th>
                                <th>วันที่</th>
                                <th>เวลา / ทิศทาง</th>
                                <th class="text-center" style="width: 100px;">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($events_list && $events_list->num_rows > 0): ?>
                                <?php while ($ev = $events_list->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-secondary">#<?= $ev['id'] ?></td>
                                        <td style="width: 70px;">
                                            <img src="<?= htmlspecialchars($ev['image_url']) ?>" 
                                                 alt="<?= htmlspecialchars($ev['title']) ?>" 
                                                 class="rounded border border-secondary border-opacity-25" 
                                                 style="width: 60px; height: 42px; object-fit: cover;"
                                                 onerror="this.src='https://images.unsplash.com/photo-1506703719100-a0f3a48c0f86?auto=format&fit=crop&w=800&q=80'">
                                        </td>
                                        <td>
                                            <strong class="text-white d-block"><?= htmlspecialchars($ev['title']) ?></strong>
                                            <small class="text-info"><?= htmlspecialchars($ev['category'] ?? 'ทั่วไป') ?></small>
                                        </td>
                                        <td style="white-space: nowrap;">
                                            <span class="badge bg-secondary bg-opacity-50"><?= htmlspecialchars($ev['event_date']) ?></span>
                                        </td>
                                        <td>
                                            <small class="text-warning d-block"><i class="bi bi-clock me-1"></i><?= htmlspecialchars($ev['peak_time'] ?? '-') ?></small>
                                            <small class="text-secondary"><i class="bi bi-compass me-1"></i><?= htmlspecialchars($ev['direction'] ?? '-') ?></small>
                                        </td>
                                        <td class="text-center">
                                            <a href="admin_events.php?action=edit&id=<?= $ev['id'] ?>" class="btn btn-sm btn-outline-warning p-1 px-2" title="แก้ไข">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <a href="admin_events.php?action=delete&id=<?= $ev['id'] ?>" class="btn btn-sm btn-outline-danger p-1 px-2" 
                                               onclick="return confirm('ยืนยันที่จะลบปรากฏการณ์ <?= addslashes($ev['title']) ?> ใช่หรือไม่?');" title="ลบ">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-secondary">ยังไม่มีข้อมูลปรากฏการณ์ในระบบ</td>
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
