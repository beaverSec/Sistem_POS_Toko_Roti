<?php
// $currentPage harus di-set di halaman yang meng-include file ini
// Contoh: $currentPage = 'dashboard';
$currentPage = $currentPage ?? '';
?>
<div class="sidebar">
    <div class="sidebar-logo">🍞 TokoRoti</div>
    <nav>
        <a href="dashboard.php" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
            Dashboard
        </a>
        <a href="index.php" class="nav-item <?= $currentPage === 'inventori' ? 'active' : '' ?>">
            Inventori
        </a>
        <a href="laporan.php" class="nav-item <?= $currentPage === 'laporan' ? 'active' : '' ?>">
            Laporan
        </a>
        <a href="karyawan.php" class="nav-item <?= $currentPage === 'karyawan' ? 'active' : '' ?>">
            Karyawan
        </a>
    </nav>
    <div class="sidebar-footer">
        <span><?= htmlspecialchars($_SESSION['nama']) ?></span>
        <a href="../process/logout.php">Logout</a>
    </div>
</div>
