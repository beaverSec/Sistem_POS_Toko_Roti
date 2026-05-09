<?php
require_once '../includes/auth.php';
requireRole('Manajer');
require_once '../config/database.php';

$currentPage = 'dashboard';

// Stat: total menu aktif
$totalMenu = $conn->query("SELECT COUNT(*) FROM menu WHERE is_deleted = 0")->fetchColumn();

// Stat: stok menipis (stok <= 10)
$stokMenipis = $conn->query("SELECT COUNT(*) FROM menu WHERE stok <= 10 AND is_deleted = 0")->fetchColumn();

// Stat: total transaksi bulan ini
$transaksBulanIni = $conn->query(
    "SELECT COUNT(*) FROM transaksi 
     WHERE MONTH(waktu_transaksi) = MONTH(NOW()) 
     AND YEAR(waktu_transaksi) = YEAR(NOW())
     AND status = 'Selesai'"
)->fetchColumn();

// Stat: pendapatan bulan ini
$pendapatanBulanIni = $conn->query(
    "SELECT COALESCE(SUM(total_bayar), 0) FROM transaksi 
     WHERE MONTH(waktu_transaksi) = MONTH(NOW()) 
     AND YEAR(waktu_transaksi) = YEAR(NOW())
     AND status = 'Selesai'"
)->fetchColumn();

// Menu terlaris (dari detail_transaksi)
$menuTerlaris = $conn->query(
    "SELECT m.nama_menu, SUM(dt.jumlah) as total_terjual
     FROM detail_transaksi dt
     JOIN menu m ON dt.id_menu = m.id_menu
     GROUP BY dt.id_menu
     ORDER BY total_terjual DESC
     LIMIT 5"
)->fetchAll(PDO::FETCH_ASSOC);

// Stok menipis detail
$daftarStokMenipis = $conn->query(
    "SELECT nama_menu, stok FROM menu 
     WHERE stok <= 10 AND is_deleted = 0 
     ORDER BY stok ASC"
)->fetchAll(PDO::FETCH_ASSOC);

// Transaksi terbaru
$transaksiTerbaru = $conn->query(
    "SELECT id_transaksi, waktu_transaksi, total_bayar, status
     FROM transaksi 
     ORDER BY waktu_transaksi DESC 
     LIMIT 5"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - TokoRoti</title>
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main">
        <div class="topbar">
            <h2>Dashboard</h2>
            <span>Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?> 👋</span>
        </div>

        <!-- Stat Cards -->
        <div class="stats">
            <div class="stat">
                <div class="stat-label">Pendapatan bulan ini</div>
                <div class="stat-val">Rp <?= number_format($pendapatanBulanIni, 0, ',', '.') ?></div>
            </div>
            <div class="stat">
                <div class="stat-label">Transaksi bulan ini</div>
                <div class="stat-val"><?= $transaksBulanIni ?></div>
            </div>
            <div class="stat">
                <div class="stat-label">Total menu aktif</div>
                <div class="stat-val"><?= $totalMenu ?></div>
            </div>
            <div class="stat">
                <div class="stat-label">Stok menipis</div>
                <div class="stat-val"><?= $stokMenipis ?></div>
            </div>
        </div>

        <div class="row2">
            <!-- Menu Terlaris -->
            <div class="card">
                <div class="card-title">Menu Terlaris</div>
                <?php if ($menuTerlaris): ?>
                    <table>
                        <tr><th>Menu</th><th>Terjual</th></tr>
                        <?php foreach ($menuTerlaris as $m): ?>
                        <tr>
                            <td><?= htmlspecialchars($m['nama_menu']) ?></td>
                            <td><?= $m['total_terjual'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <p>Belum ada data.</p>
                <?php endif; ?>
            </div>

            <!-- Stok Menipis -->
            <div class="card">
                <div class="card-title">Stok Menipis ⚠</div>
                <?php if ($daftarStokMenipis): ?>
                    <?php foreach ($daftarStokMenipis as $s): ?>
                        <div class="stok-row">
                            <span><?= htmlspecialchars($s['nama_menu']) ?></span>
                            <span style="color:red;">Sisa <?= $s['stok'] ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Semua stok aman.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Transaksi Terbaru -->
        <div class="card">
            <div class="card-title">Transaksi Terbaru</div>
            <table>
                <tr><th>ID</th><th>Waktu</th><th>Total</th><th>Status</th></tr>
                <?php foreach ($transaksiTerbaru as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['id_transaksi']) ?></td>
                    <td><?= date('d M Y H:i', strtotime($t['waktu_transaksi'])) ?></td>
                    <td>Rp <?= number_format($t['total_bayar'], 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($t['status']) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>
</body>
</html>
