</main>

<footer class="bg-black text-secondary py-3 border-top border-secondary border-opacity-25 mt-auto small">
    <div class="container d-flex justify-content-between align-items-center">
        <span>ASTROHUB Content Management System &copy; <?= date('Y') ?></span>
        <span>เข้าสู่ระบบในฐานะ: <strong class="text-info"><?= htmlspecialchars($_SESSION['admin_username'] ?? 'admin') ?></strong></span>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

