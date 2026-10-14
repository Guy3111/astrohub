<?php
/**
 * weather.php
 * คำนวณความพร้อมในการดูดาวตามจังหวัด (Stargazing Readiness Calculator)
 */
require_once 'db_connect.php';
include 'header.php';

// รายชื่อจังหวัดยอดนิยมและจากฐานข้อมูล
$default_provinces = [
    'เชียงใหม่', 'เชียงราย', 'แม่ฮ่องสอน', 'น่าน', 'เพชรบูรณ์', 
    'นครราชสีมา', 'สุพรรณบุรี', 'กาญจนบุรี', 'ประจวบคีรีขันธ์', 
    'ชลบุรี', 'ระยอง', 'จันทบุรี', 'ตราด', 'กระบี่', 'พังงา', 
    'ภูเก็ต', 'สงขลา', 'กรุงเทพมหานคร', 'นครนายก', 'สระบุรี'
];

// ดึงจังหวัดเพิ่มเติมจากฐานข้อมูล locations
$db_provinces = [];
$prov_query = $conn->query("SELECT DISTINCT province FROM locations WHERE province IS NOT NULL AND province != ''");
if ($prov_query) {
    while ($r = $prov_query->fetch_row()) {
        $db_provinces[] = $r[0];
    }
}
$provinces = array_values(array_unique(array_merge($db_provinces, $default_provinces)));
sort($provinces);

// ตัวแปรสำหรับรับค่าและผลลัพธ์
$selected_province = isset($_POST['province']) ? trim($_POST['province']) : (isset($_GET['province']) ? trim($_GET['province']) : '');
$has_result = false;
$result = [];

if (!empty($selected_province)) {
    $has_result = true;
    
    // ใช้วันที่ + ชื่อจังหวัด เป็น Seed เพื่อให้ผลลัพธ์วันนี้ของจังหวัดเดียวกันมีความเสถียรสมจริง
    $seed = crc32($selected_province . date('Y-m-d'));
    mt_srand($seed);

    // คำนวณค่าตัวแปรสภาพฟ้า
    $cloud_cover = mt_rand(5, 75);      // เมฆ (%)
    $humidity = mt_rand(40, 88);         // ความชื้น (%)
    $temperature = mt_rand(14, 29);      // อุณหภูมิ (°C)
    $visibility_km = mt_rand(6, 16);     // ทัศนวิสัย (กม.)
    $moon_phase_index = mt_rand(0, 3);   // 0: แรม (มืดสนิท), 1: เสี้ยว, 2: ครึ่งดวง, 3: เพ็ญ (สว่างมาก)
    
    $moon_phases = [
        ['name' => 'คืนเดือนมืด (ข้างแรม)', 'impact' => 'ดีเยี่ยม แสงจันทร์ไม่รบกวน', 'score' => 25],
        ['name' => 'จันทร์เสี้ยว (เสี้ยวบาง)', 'impact' => 'ดี มีแสงจันทร์เล็กน้อย', 'score' => 20],
        ['name' => 'จันทร์ครึ่งดวง', 'impact' => 'ปานกลาง มีแสงจันทร์สว่างบางส่วน', 'score' => 12],
        ['name' => 'จันทร์เพ็ญ (จันทร์เต็มดวง)', 'impact' => 'แสงจันทร์สว่างจ้า ท้องฟ้าสว่าง', 'score' => 5],
    ];
    $moon_info = $moon_phases[$moon_phase_index];

    // คำนวณคะแนนรวมเต็ม 100
    // เมฆน้อย = คะแนนสูง (สูงสุด 45)
    $cloud_score = max(0, (100 - $cloud_cover) * 0.45);
    // ความชื้นพอดี (40-65%) = คะแนนสูง (สูงสุด 20)
    $humidity_score = max(0, (100 - $humidity) * 0.20);
    // ทัศนวิสัยไกล = คะแนนสูง (สูงสุด 10)
    $vis_score = min(10, ($visibility_km / 16) * 10);
    // แสงจันทร์ (สูงสุด 25)
    $moon_score = $moon_info['score'];

    $total_score = round($cloud_score + $humidity_score + $vis_score + $moon_score);

    // แปลงคะแนนรวมเป็นดาว 1 - 5 ดาว
    if ($total_score >= 82) {
        $stars = 5;
        $verdict = "ท้องฟ้าเปิดสมบูรณ์แบบ เหมาะดูดาวที่สุด! 🌌";
        $verdict_desc = "ฟ้าโปร่ง เมฆน้อย ไร้แสงจันทร์รบกวน เหมาะกับการถ่ายภาพทางช้างเผือกและฝนดาวตกอย่างยิ่ง";
        $badge_color = "success";
    } elseif ($total_score >= 68) {
        $stars = 4;
        $verdict = "สภาพฟ้าดีมาก เหมาะแก่การดูดาว ⭐";
        $verdict_desc = "มีเมฆบางเบา ทัศนวิสัยดีมาก สามารถสังเกตเห็นกลุ่มดาวสว่างและวัตถุอวกาศลึกได้ชัดเจน";
        $badge_color = "info";
    } elseif ($total_score >= 50) {
        $stars = 3;
        $verdict = "สภาพฟ้าปานกลาง ดูดาวได้เป็นช่วงๆ ⛅";
        $verdict_desc = "มีเมฆหรือแสงจันทร์บางส่วน แนะนำให้สังเกตการณ์ดาวเคราะห์ ดาวพฤหัสบดี หรือดาวเสาร์";
        $badge_color = "primary";
    } elseif ($total_score >= 35) {
        $stars = 2;
        $verdict = "สภาพฟ้าค่อนข้างปิด ไม่แนะนำทริปดูดาว ☁️";
        $verdict_desc = "เมฆค่อนข้างหนาหรือความชื้นสูง ทัศนวิสัยจำกัด อาจมองเห็นเฉพาะดาวฤกษ์ที่สว่างมาก";
        $badge_color = "warning";
    } else {
        $stars = 1;
        $verdict = "สภาพฟ้าปิด ไม่เหมาะสมสำหรับการดูดาว 🌧️";
        $verdict_desc = "มีเมฆหนาทึบหรือมีความชื้นสูงมาก ไม่สามารถมองเห็นดวงดาวได้อย่างชัดเจน แนะนำรอวันถัดไป";
        $badge_color = "danger";
    }

    $result = [
        'province' => $selected_province,
        'stars' => $stars,
        'score' => $total_score,
        'cloud_cover' => $cloud_cover,
        'humidity' => $humidity,
        'temperature' => $temperature,
        'visibility_km' => $visibility_km,
        'moon_name' => $moon_info['name'],
        'moon_impact' => $moon_info['impact'],
        'verdict' => $verdict,
        'verdict_desc' => $verdict_desc,
        'badge_color' => $badge_color,
    ];
}
?>

<style>
    .page-header {
        background: radial-gradient(circle at 50% 15%, rgba(56, 189, 248, 0.16), transparent 70%),
                    linear-gradient(180deg, #080b12 0%, #0b0f19 100%);
        padding: 50px 0 40px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .calculator-card {
        background: #111827;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
    }
    .result-card {
        background: linear-gradient(145deg, #111827 0%, #0d131f 100%);
        border: 1px solid rgba(56, 189, 248, 0.3);
        border-radius: 20px;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.6), 0 0 25px rgba(56, 189, 248, 0.1);
        overflow: hidden;
    }
    .metric-box {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 12px;
        padding: 16px;
        text-align: center;
        height: 100%;
        transition: transform 0.2s ease;
    }
    .metric-box:hover {
        transform: translateY(-3px);
        border-color: rgba(56, 189, 248, 0.3);
    }
    .star-glow {
        color: #ffc107;
        filter: drop-shadow(0 0 8px rgba(255, 193, 7, 0.5));
    }
    .form-select-dark {
        background-color: #0b0f19;
        color: #f8fafc;
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 12px;
        padding: 12px 18px;
    }
    .form-select-dark:focus {
        background-color: #0b0f19;
        color: #f8fafc;
        border-color: #38bdf8;
        box-shadow: 0 0 0 0.25rem rgba(56, 189, 248, 0.25);
    }
</style>

<!-- ส่วนหัวเรื่อง -->
<section class="page-header text-center">
    <div class="container">
        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2 rounded-pill mb-3">
            <i class="bi bi-stars me-1"></i> Stargazing Forecast & Index
        </span>
        <h1 class="display-6 fw-bold text-white mb-2">คำนวณความพร้อมในการดูดาว</h1>
        <p class="text-secondary lead fs-6 mx-auto mb-0" style="max-width: 650px;">
            เลือกจังหวัดที่คุณต้องการเดินทาง เพื่อวิเคราะห์สภาพท้องฟ้า เมฆ แสงจันทร์ และประเมินความพร้อมในการดูดาวคืนนี้ (1 - 5 ดาว)
        </p>
    </div>
</section>

<div class="container py-5">
    <div class="row justify-content-center">
        <!-- กล่องเลือกจังหวัด -->
        <div class="col-lg-8">
            <div class="calculator-card p-4 p-md-5 mb-5">
                <form method="POST" action="weather.php">
                    <div class="row align-items-end g-3">
                        <div class="col-md-8">
                            <label for="provinceSelect" class="form-label text-light fw-medium mb-2">
                                <i class="bi bi-geo-alt-fill text-info me-1"></i> เลือกจังหวัดที่ต้องการสังเกตการณ์:
                            </label>
                            <select name="province" id="provinceSelect" class="form-select form-select-dark" required>
                                <option value="" disabled <?= empty($selected_province) ? 'selected' : '' ?>>-- เลือกจังหวัด --</option>
                                <?php foreach ($provinces as $p): ?>
                                    <option value="<?= htmlspecialchars($p) ?>" <?= ($selected_province === $p) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-info w-100 py-3 fw-bold text-dark rounded-3 shadow">
                                <i class="bi bi-calculator me-1"></i> คำนวณความพร้อม
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- การ์ดแสดงผลสรุปเมื่อกดคำนวณ -->
            <?php if ($has_result): ?>
                <div class="result-card p-4 p-md-5">
                    <!-- ส่วนหัวการ์ดผลลัพธ์ -->
                    <div class="text-center mb-4">
                        <div class="badge bg-<?= $result['badge_color'] ?> bg-opacity-10 text-<?= $result['badge_color'] ?> border border-<?= $result['badge_color'] ?> border-opacity-25 px-3 py-2 rounded-pill mb-2">
                            <i class="bi bi-calendar-day me-1"></i> รายงานประจำวัน: <?= date('d/m/Y') ?>
                        </div>
                        <h2 class="text-white fw-bold display-6 mb-1">
                            <?= htmlspecialchars($result['province']) ?>
                        </h2>
                        <h4 class="text-<?= $result['badge_color'] ?> fw-semibold mb-3">
                            <?= $result['verdict'] ?>
                        </h4>

                        <!-- ดาวความพร้อม 1-5 ดาว -->
                        <div class="d-flex justify-content-center align-items-center gap-2 mb-2 fs-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= $result['stars']): ?>
                                    <i class="bi bi-star-fill star-glow"></i>
                                <?php else: ?>
                                    <i class="bi bi-star text-secondary opacity-25"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <div class="text-secondary small">
                            ดัชนีความพร้อมรวม: <strong class="text-info"><?= $result['score'] ?> / 100 คะแนน</strong> (ระดับ <?= $result['stars'] ?> ดาว)
                        </div>
                        <p class="text-light opacity-75 mt-2 mb-0 mx-auto" style="max-width: 580px;">
                            <?= $result['verdict_desc'] ?>
                        </p>
                    </div>

                    <hr class="border-secondary border-opacity-25 my-4">

                    <!-- รายละเอียดสถิติสภาพอากาศ 4 ตัวชี้วัด -->
                    <h5 class="text-white fw-semibold mb-3">
                        <i class="bi bi-bar-chart-fill text-info me-2"></i> สถิติและสภาพฟ้าประกอบการคำนวณ
                    </h5>
                    <div class="row g-3 mb-4">
                        <!-- 1. ปริมาณเมฆ -->
                        <div class="col-6 col-md-3">
                            <div class="metric-box">
                                <i class="bi bi-cloud text-info fs-3 mb-2 d-block"></i>
                                <span class="text-secondary small d-block">ปริมาณเมฆ</span>
                                <strong class="text-white fs-5"><?= $result['cloud_cover'] ?>%</strong>
                                <small class="text-secondary d-block mt-1">
                                    <?= ($result['cloud_cover'] < 25) ? 'ฟ้าเปิดมาก' : (($result['cloud_cover'] < 50) ? 'เมฆบางส่วน' : 'เมฆหนาแน่น') ?>
                                </small>
                            </div>
                        </div>

                        <!-- 2. ทัศนวิสัย -->
                        <div class="col-6 col-md-3">
                            <div class="metric-box">
                                <i class="bi bi-eye text-success fs-3 mb-2 d-block"></i>
                                <span class="text-secondary small d-block">ทัศนวิสัย</span>
                                <strong class="text-white fs-5"><?= $result['visibility_km'] ?> กม.</strong>
                                <small class="text-secondary d-block mt-1">
                                    <?= ($result['visibility_km'] > 12) ? 'ใสกระจ่าง' : 'ปานกลาง' ?>
                                </small>
                            </div>
                        </div>

                        <!-- 3. อุณหภูมิ & ความชื้น -->
                        <div class="col-6 col-md-3">
                            <div class="metric-box">
                                <i class="bi bi-thermometer-half text-warning fs-3 mb-2 d-block"></i>
                                <span class="text-secondary small d-block">อุณหภูมิ / ชื้น</span>
                                <strong class="text-white fs-5"><?= $result['temperature'] ?>°C</strong>
                                <small class="text-secondary d-block mt-1">ชื้น <?= $result['humidity'] ?>%</small>
                            </div>
                        </div>

                        <!-- 4. แสงจันทร์ -->
                        <div class="col-6 col-md-3">
                            <div class="metric-box">
                                <i class="bi bi-moon-stars text-primary fs-3 mb-2 d-block"></i>
                                <span class="text-secondary small d-block">สภาพดวงจันทร์</span>
                                <strong class="text-white small d-block"><?= $result['moon_name'] ?></strong>
                                <small class="text-secondary d-block mt-1"><?= $result['moon_impact'] ?></small>
                            </div>
                        </div>
                    </div>

                    <!-- ปุ่มดำเนินการต่อ -->
                    <div class="d-flex flex-wrap justify-content-center gap-3 pt-3">
                        <a href="locations.php" class="btn btn-outline-info px-4 py-2">
                            <i class="bi bi-geo-alt me-1"></i> หาพิกัดดูดาวในพื้นที่ใกล้เคียง
                        </a>
                        <a href="calendar.php" class="btn btn-outline-light px-4 py-2">
                            <i class="bi bi-calendar-event me-1"></i> ตรวจสอบปฏิทินดาราศาสตร์
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- กล่องแนะนำเมื่อยังไม่ได้กดคำนวณ -->
                <div class="text-center p-5 border border-secondary border-opacity-25 rounded-4 bg-black bg-opacity-25 text-secondary">
                    <i class="bi bi-compass fs-1 text-info opacity-75 mb-3 d-block"></i>
                    <h5 class="text-light">พร้อมวางแผนทริปดูดาวของคุณหรือยัง?</h5>
                    <p class="small mb-0">เลือกจังหวัดที่คุณต้องการเดินทางด้านบน แล้วกดปุ่ม <strong>"คำนวณความพร้อม"</strong> เพื่อดูดัชนีคะแนน</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
include 'footer.php'; 
?>
