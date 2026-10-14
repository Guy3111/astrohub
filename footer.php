</main>
<!-- ปิดส่วน main-content -->

<!-- Footer -->
<footer class="bg-black text-light pt-5 pb-4 mt-auto border-top border-secondary border-opacity-25">
    <div class="container">
        <div class="row gy-4">
            <!-- คอลัมน์ที่ 1: ข้อมูลเกี่ยวกับ ASTROHUB -->
            <div class="col-lg-4 col-md-6">
                <h5 class="brand-logo text-info d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-stars"></i>
                    <span>ASTROHUB</span>
                </h5>
                <p class="text-secondary small leading-relaxed">
                    ศูนย์รวมข้อมูลดาราศาสตร์สำหรับผู้หลงใหลในท้องฟ้าและดวงดาว ติดตามปรากฏการณ์ ปฏิทินดาราศาสตร์ พิกัดดูดาวที่ดีที่สุด และพยากรณ์สภาพท้องฟ้าทั่วประเทศไทย
                </p>
                
            </div>

            <!-- คอลัมน์ที่ 2: เมนูด่วน -->
            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="text-white fw-bold mb-3">เมนูหลัก</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="index.php" class="text-secondary text-decoration-none hover-link">หน้าแรก</a></li>
                    <li class="mb-2"><a href="calendar.php" class="text-secondary text-decoration-none hover-link">ปฏิทินดาราศาสตร์</a></li>
                    <li class="mb-2"><a href="locations.php" class="text-secondary text-decoration-none hover-link">พิกัดชมดาว</a></li>
                    <li class="mb-2"><a href="knowledge.php" class="text-secondary text-decoration-none hover-link">คลังความรู้</a></li>
                    <li class="mb-2"><a href="weather.php" class="text-secondary text-decoration-none hover-link">เช็กสภาพฟ้า</a></li>
                    <li class="mb-2"><a href="contact.php" class="text-secondary text-decoration-none hover-link">ติดต่อเรา</a></li>
                </ul>
            </div>

            <!-- คอลัมน์ที่ 3: บริการและข้อมูล -->
            <div class="col-lg-3 col-md-6 col-6">
                <h6 class="text-white fw-bold mb-3">บริการแนะนำ</h6>
                <ul class="list-unstyled small text-secondary">
                    <li class="mb-2"><i class="bi bi-chevron-right me-1 text-info small"></i> พยากรณ์เมฆและทัศนวิสัย</li>
                    <li class="mb-2"><i class="bi bi-chevron-right me-1 text-info small"></i> แผนที่มลภาวะทางแสง (Dark Sky)</li>
                    <li class="mb-2"><i class="bi bi-chevron-right me-1 text-info small"></i> แจ้งเตือนฝนดาวตก & สุริยุปราคา</li>
                    <li class="mb-2"><i class="bi bi-chevron-right me-1 text-info small"></i> แนะนำการใช้อุปกรณ์ถ่ายภาพดาว</li>
                </ul>
            </div>

            <!-- คอลัมน์ที่ 4: ข้อมูลติดต่อ -->
            <div class="col-lg-3 col-md-6">
                <h6 class="text-white fw-bold mb-3">ติดต่อสอบถาม</h6>
                <p class="text-secondary small mb-2">
                    <i class="bi bi-geo-alt me-2 text-info"></i>มหาสารคาม , ประเทศไทย
                </p>
                <p class="text-secondary small mb-2">
                    <i class="bi bi-envelope me-2 text-info"></i> 67011213104@msu.ac.th 
                </p>
                <p class="text-secondary small mb-2">
                    <i class="bi bi-envelope me-2 text-info"></i>  67011213111@msu.ac.th
                </p>
                 <p class="text-secondary small mb-0">
                    <i class="bi bi-telephone me-2 text-info"></i> 098-231-3776
                </p>
                 <p class="text-secondary small mb-0">
                    <i class="bi bi-telephone me-2 text-info"></i> 095-317-5429
                </p>
            </div>
        </div>
    
        <hr class="border-secondary border-opacity-25 my-4">

        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                <span class="text-secondary small">&copy; <?= date('Y') ?> <strong>ASTROHUB</strong>. All rights reserved.</span>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <span class="text-secondary small">ออกแบบและพัฒนาเพื่อคนรักดวงดาว 🌌</span>
            </div>
        </div>
    </div>
</footer>

<style>
    .hover-link:hover {
        color: #38bdf8 !important;
        padding-left: 4px;
        transition: all 0.2s ease;
    }
    .hover-info:hover {
        color: #38bdf8 !important;
        transition: color 0.2s ease;
    }
</style>

<!-- Bootstrap 5 Bundle JS (พร้อม Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
