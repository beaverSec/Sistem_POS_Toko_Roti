<?php
require_once '../includes/auth.php';
requireRole('Manajer');
require_once '../config/database.php';

$currentPage = 'laporan';

// Logika Filter
$dari    = $_GET['dari'] ?? date('Y-m-01');
$sampai  = $_GET['sampai'] ?? date('Y-m-d');
$kasir   = $_GET['id_karyawan'] ?? '';

// Query Data (Gunakan view transaksilengkap)
$sql = "SELECT * FROM transaksilengkap WHERE DATE(waktu_transaksi) BETWEEN :dari AND :sampai";
$params = [':dari' => $dari, ':sampai' => $sampai];

if (!empty($kasir)) {
    $sql .= " AND id_karyawan = :kasir";
    $params[':kasir'] = $kasir;
}

$sql .= " ORDER BY waktu_transaksi DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Daftar Kasir untuk Dropdown
$daftarKasir = $conn->query("SELECT id_karyawan, nama_karyawan FROM karyawan WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi — TokoRoti</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* CSS RESET & VARS IDENTIK DASHBOARD REKANMU */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --sidebar-bg: #0F172A; /* Warna biru gelap aslinya */
            --sidebar-active: #1E293B;
            --blue-500: #1F72D3;
            --gray-400: #94A3B8;
            --white: #FFFFFF;
            --font: 'Plus Jakarta Sans', sans-serif;
        }

        body { font-family: var(--font); background: #F8FAFC; display: flex; min-height: 100vh; }

        /* ── SIDEBAR ── */
.sidebar {
    width: 220px;
    background: var(--blue-500);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    position: sticky;
    top: 0;
    height: 100vh;
}

.sidebar-brand {
    padding: 24px 20px 20px;
    border-bottom: 1px solid rgba(255,255,255,.12);
}

.sidebar-brand-name {
    font-size: 20px;
    font-weight: 800;
    color: var(--white);
    letter-spacing: -.3px;
}

.sidebar-brand-role {
    font-size: 11px;
    font-weight: 500;
    color: rgba(255,255,255,.55);
    margin-top: 2px;
    text-transform: uppercase;
    letter-spacing: .5px;
}

.sidebar-nav {
    flex: 1;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 12px;
    border-radius: 12px;
    color: rgba(255,255,255,.75);
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: all .15s;
}

.nav-item:hover {
    background: rgba(255,255,255,.12);
    color: var(--white);
}

.nav-item.active {
    background: rgba(255,255,255,.2);
    color: var(--white);
}

.nav-item svg {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
    opacity: .85;
}

.nav-item.active svg {
    opacity: 1;
}

.nav-item.logout {
    color: rgba(255,255,255,.6);
}

.nav-item.logout:hover {
    background: rgba(255,255,255,.1);
    color: var(--white);
}

.sidebar-bottom {
    padding: 12px;
    border-top: 1px solid rgba(255,255,255,.1);
}

        /* MAIN CONTENT */
.main {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
    background: #F8FAFC;
}

.topbar {
    height: 72px;
    background: var(--white);
    border-bottom: 1px solid #E2E8F0;
    padding: 0 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.topbar-title {
    font-size: 20px;
    font-weight: 800;
    color: #0F172A;
    letter-spacing: -.3px;
}

.content {
    padding: 28px;
    overflow-y: auto;
}

/* CARDS */
.card {
    background: var(--white);
    border-radius: 18px;
    border: 1px solid #E2E8F0;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
}

/* FILTER */
.filter-row {
    display: flex;
    align-items: flex-end;
    gap: 18px;
    flex-wrap: wrap;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-size: 11px;
    font-weight: 700;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: .4px;
}

input[type="date"],
select {
    height: 44px;
    padding: 0 14px;
    border-radius: 12px;
    border: 1px solid #E2E8F0;
    background: #F8FAFC;
    font-family: inherit;
    font-size: 14px;
    color: #0F172A;
    outline: none;
    transition: .15s;
}

input[type="date"]:focus,
select:focus {
    border-color: var(--blue-500);
    box-shadow: 0 0 0 3px rgba(31,114,211,.12);
}

/* BUTTON */
.btn-filter {
    height: 44px;
    background: var(--blue-500);
    color: var(--white);
    border: none;
    padding: 0 22px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    transition: .15s;
}

.btn-filter:hover {
    opacity: .92;
}

.btn-csv {
    margin-left: auto;
    height: 44px;
    background: #10B981;
    color: var(--white);
    text-decoration: none;
    padding: 0 20px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: .15s;
}

.btn-csv:hover {
    opacity: .92;
}

        /* TABLE */
.report-table {
    width: 100%;
    border-collapse: collapse;
}

.report-table th {
    text-align: left;
    font-size: 12px;
    font-weight: 700;
    color: #64748B;
    padding: 16px;
    border-bottom: 1px solid #E2E8F0;
    text-transform: uppercase;
    letter-spacing: .4px;
    background: #FCFCFD;
}

.report-table td {
    padding: 16px;
    font-size: 14px;
    border-bottom: 1px solid #F1F5F9;
    color: #0F172A;
}

.report-table tr:last-child td {
    border-bottom: none;
}

.report-table tr:hover {
    background: #FAFBFC;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    padding: 6px 12px;
    border-radius: 999px;
    background: #DCFCE7;
    color: #166534;
}
    </style>
</head>
<body>

    <aside class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-name">TokoRoti</div>
        <div class="sidebar-brand-role"><?= $_SESSION['role'] ?></div>
    </div>
        
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>
            <a href="index.php" class="nav-item <?= $currentPage === 'inventori' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                Inventori
            </a>
            <a href="laporan.php" class="nav-item <?= $currentPage === 'laporan' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/></svg>
                Laporan
            </a>
            <a href="karyawan.php" class="nav-item <?= $currentPage === 'karyawan' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                Karyawan
            </a>
        </nav>

        <div class="sidebar-bottom">
            <a href="../process/logout.php" class="nav-item logout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                Logout
            </a>
        </div>
    </aside>

    <div class="main">
        <div class="topbar">
            <div class="topbar-title">Laporan Transaksi</div>
            <div style="font-size: 14px; font-weight: 600;">
                <span style="color: #64748B; font-weight: 400;">Halo,</span> <?= htmlspecialchars($_SESSION['nama']) ?>
            </div>
        </div>

        <div class="content">
            <div class="card">
                <form method="GET" class="filter-row">
                    <div class="form-group">
                        <label>Dari Tanggal</label>
                        <input type="date" name="dari" value="<?= $dari ?>">
                    </div>
                    <div class="form-group">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="sampai" value="<?= $sampai ?>">
                    </div>
                    <div class="form-group">
                        <label>Filter Kasir</label>
                        <select name="id_karyawan">
                            <option value="">Semua Kasir</option>
                            <?php foreach ($daftarKasir as $k): ?>
                                <option value="<?= $k['id_karyawan'] ?>" <?= $kasir == $k['id_karyawan'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama_karyawan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-filter">Update Laporan</button>
                    
                    <a href="export_csv.php?dari=<?= $dari ?>&sampai=<?= $sampai ?>&id_karyawan=<?= $kasir ?>" class="btn-csv">
                        📥 Download CSV
                    </a>
                </form>
            </div>

            <div class="card" style="padding: 0; overflow: hidden;">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Waktu Transaksi</th>
                            <th>Nama Kasir</th>
                            <th>Total Bayar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($transaksi): ?>
                            <?php foreach ($transaksi as $t): ?>
                            <tr>
                                <td style="font-weight: 700; color: var(--blue-500);">#<?= htmlspecialchars($t['id_transaksi']) ?></td>
                                <td><?= date('d M Y, H:i', strtotime($t['waktu_transaksi'])) ?></td>
                                <td><?= htmlspecialchars($t['nama_karyawan']) ?></td>
                                <td style="font-weight: 700;">Rp <?= number_format($t['subtotal'], 0, ',', '.') ?></td>
                                <td><span class="status-pill">Selesai</span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center; padding: 40px; color: #64748B;">Tidak ada transaksi yang ditemukan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>