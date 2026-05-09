<?php
require_once '../includes/auth.php';
requireRole('Manajer');
require_once '../config/database.php';

$currentPage = 'laporan';

// Filter tanggal & kasir
$dari    = $_GET['dari']      ?? date('Y-m-01');           // default: awal bulan ini
$sampai  = $_GET['sampai']    ?? date('Y-m-d');            // default: hari ini
$kasir   = $_GET['id_karyawan'] ?? '';

// Build query dengan filter
$sql = "SELECT t.id_transaksi, t.waktu_transaksi, k.nama_karyawan,
               t.total_bayar, t.metode_bayar, t.status
        FROM transaksi t
        JOIN karyawan k ON t.id_karyawan = k.id_karyawan
        WHERE DATE(t.waktu_transaksi) BETWEEN :dari AND :sampai";

$params = [':dari' => $dari, ':sampai' => $sampai];

if (!empty($kasir)) {
    $sql .= " AND t.id_karyawan = :kasir";
    $params[':kasir'] = $kasir;
}

$sql .= " ORDER BY t.waktu_transaksi DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total pendapatan (hanya status Selesai)
$totalPendapatan = array_sum(array_column(
    array_filter($transaksi, fn($t) => $t['status'] === 'Selesai'),
    'total_bayar'
));

// Daftar kasir untuk dropdown filter
$daftarKasir = $conn->query("SELECT id_karyawan, nama_karyawan FROM karyawan WHERE is_active = 1")
                    ->fetchAll(PDO::FETCH_ASSOC);

// Stat cards
$totalTx   = count($transaksi);
$totalSelesai = count(array_filter($transaksi, fn($t) => $t['status'] === 'Selesai'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan - TokoRoti</title>
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main">
        <div class="topbar">
            <h2>Laporan Transaksi</h2>
            <a href="?dari=<?= $dari ?>&sampai=<?= $sampai ?>&export=csv">Export CSV</a>
        </div>

        <!-- Stat Cards -->
        <div class="stats">
            <div class="stat">
                <div class="stat-label">Total transaksi</div>
                <div class="stat-val"><?= $totalTx ?></div>
            </div>
            <div class="stat">
                <div class="stat-label">Total pendapatan</div>
                <div class="stat-val">Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></div>
            </div>
            <div class="stat">
                <div class="stat-label">Transaksi selesai</div>
                <div class="stat-val"><?= $totalSelesai ?></div>
            </div>
            <div class="stat">
                <div class="stat-label">Transaksi batal</div>
                <div class="stat-val"><?= $totalTx - $totalSelesai ?></div>
            </div>
        </div>

        <!-- Filter -->
        <form method="GET">
            Dari <input type="date" name="dari" value="<?= $dari ?>">
            sampai <input type="date" name="sampai" value="<?= $sampai ?>">
            <select name="id_karyawan">
                <option value="">Semua kasir</option>
                <?php foreach ($daftarKasir as $k): ?>
                    <option value="<?= $k['id_karyawan'] ?>" <?= $kasir === $k['id_karyawan'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['nama_karyawan']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Tampilkan</button>
        </form>

        <!-- Tabel -->
        <table border="1" cellpadding="8">
            <tr>
                <th>ID</th>
                <th>Waktu</th>
                <th>Kasir</th>
                <th>Total</th>
                <th>Metode</th>
                <th>Status</th>
                <th>Detail</th>
            </tr>
            <?php if ($transaksi): ?>
                <?php foreach ($transaksi as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['id_transaksi']) ?></td>
                    <td><?= date('d M Y H:i', strtotime($t['waktu_transaksi'])) ?></td>
                    <td><?= htmlspecialchars($t['nama_karyawan']) ?></td>
                    <td>Rp <?= number_format($t['total_bayar'], 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($t['metode_bayar']) ?></td>
                    <td><?= htmlspecialchars($t['status']) ?></td>
                    <td><a href="struk.php?id=<?= $t['id_transaksi'] ?>">Lihat</a></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3"><strong>Total Pendapatan</strong></td>
                    <td><strong>Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></strong></td>
                    <td colspan="3"></td>
                </tr>
            <?php else: ?>
                <tr><td colspan="7" style="text-align:center;">Tidak ada data.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>
</body>
</html>
