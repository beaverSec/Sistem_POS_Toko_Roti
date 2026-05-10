<?php
require_once '../includes/auth.php';
requireRole('Manajer');
require_once '../config/database.php';

$currentPage = 'dashboard';

$totalMenu = $conn->query("SELECT COUNT(*) FROM menu WHERE is_deleted = 0")->fetchColumn();
$stokMenipis = $conn->query("SELECT COUNT(*) FROM menu WHERE stok <= 10 AND is_deleted = 0")->fetchColumn();
$transaksBulanIni = $conn->query(
    "SELECT COUNT(*) FROM transaksi 
     WHERE MONTH(waktu_transaksi) = MONTH(NOW()) AND YEAR(waktu_transaksi) = YEAR(NOW()) AND status = 'Selesai'"
)->fetchColumn();
$pendapatanBulanIni = $conn->query(
    "SELECT COALESCE(SUM(total_bayar), 0) FROM transaksi 
     WHERE MONTH(waktu_transaksi) = MONTH(NOW()) AND YEAR(waktu_transaksi) = YEAR(NOW()) AND status = 'Selesai'"
)->fetchColumn();

// Pendapatan 6 bulan terakhir untuk chart
$pendapatanBulanan = $conn->query(
    "SELECT DATE_FORMAT(waktu_transaksi, '%b') as bulan,
            MONTH(waktu_transaksi) as bulan_num,
            COALESCE(SUM(total_bayar), 0) as total
     FROM transaksi
     WHERE waktu_transaksi >= DATE_SUB(NOW(), INTERVAL 6 MONTH) AND status = 'Selesai'
     GROUP BY MONTH(waktu_transaksi), DATE_FORMAT(waktu_transaksi, '%b')
     ORDER BY bulan_num ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$menuTerlaris = $conn->query(
    "SELECT m.nama_menu, SUM(dt.jumlah) as total_terjual
     FROM detail_transaksi dt
     JOIN menu m ON dt.id_menu = m.id_menu
     GROUP BY dt.id_menu ORDER BY total_terjual DESC LIMIT 5"
)->fetchAll(PDO::FETCH_ASSOC);

$daftarStokMenipis = $conn->query(
    "SELECT nama_menu, stok FROM menu WHERE stok <= 10 AND is_deleted = 0 ORDER BY stok ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$transaksiTerbaru = $conn->query(
    "SELECT t.id_transaksi, t.waktu_transaksi, t.total_bayar, t.status,
            GROUP_CONCAT(m.nama_menu ORDER BY dt.jumlah DESC SEPARATOR ', ') as item_names
     FROM transaksi t
     LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
     LEFT JOIN menu m ON dt.id_menu = m.id_menu
     GROUP BY t.id_transaksi
     ORDER BY t.waktu_transaksi DESC LIMIT 5"
)->fetchAll(PDO::FETCH_ASSOC);

$bulanIni = date('F Y');
$maxTerlaris = $menuTerlaris ? max(array_column($menuTerlaris, 'total_terjual')) : 1;
$maxPendapatan = $pendapatanBulanan ? max(array_column($pendapatanBulanan, 'total')) : 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — TokoRoti</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue-50:  #EBF3FD;
            --blue-100: #C4DCFA;
            --blue-200: #90BEF5;
            --blue-400: #3B8EE8;
            --blue-500: #1F72D3;
            --blue-600: #1559A8;
            --blue-700: #0D3F7A;
            --blue-800: #082A52;
            --gray-50:  #F7F8FA;
            --gray-100: #EDEEF2;
            --gray-200: #D8DAE2;
            --gray-400: #9094A4;
            --gray-600: #5A5E6E;
            --gray-800: #2C2F3C;
            --white:    #FFFFFF;
            --green-50: #ECFDF5;
            --green-500:#10B981;
            --green-700:#065F46;
            --amber-50: #FFFBEB;
            --amber-400:#FBBF24;
            --amber-700:#92400E;
            --red-50:   #FEF2F2;
            --red-500:  #EF4444;
            --red-700:  #7F1D1D;
            --sidebar-w: 220px;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,.06);
            --shadow-md: 0 4px 16px rgba(0,0,0,.08);
            --font: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            font-family: var(--font);
            background: #F0F4FB;
            color: var(--gray-800);
            min-height: 100vh;
            display: flex;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: var(--sidebar-w);
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
            font-size: 20px; font-weight: 800;
            color: var(--white); letter-spacing: -.3px;
        }
        .sidebar-brand-role {
            font-size: 11px; font-weight: 500;
            color: rgba(255,255,255,.55);
            margin-top: 2px; text-transform: uppercase; letter-spacing: .5px;
        }
        .sidebar-nav { flex: 1; padding: 12px 12px; display: flex; flex-direction: column; gap: 2px; }
        .nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 12px; border-radius: var(--radius-md);
            color: rgba(255,255,255,.75); text-decoration: none;
            font-size: 14px; font-weight: 600;
            transition: all .15s;
        }
        .nav-item:hover { background: rgba(255,255,255,.12); color: var(--white); }
        .nav-item.active { background: rgba(255,255,255,.2); color: var(--white); }
        .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; opacity: .85; }
        .nav-item.active svg { opacity: 1; }
        .sidebar-divider { border: none; border-top: 1px solid rgba(255,255,255,.1); margin: 8px 12px; }
        .nav-item.logout { color: rgba(255,255,255,.6); margin-top: auto; }
        .nav-item.logout:hover { background: rgba(255,255,255,.1); color: var(--white); }
        .sidebar-bottom { padding: 12px; border-top: 1px solid rgba(255,255,255,.1); }

        /* ── MAIN ── */
        .main {
            flex: 1; display: flex; flex-direction: column;
            overflow: hidden; min-width: 0;
        }

        /* TOPBAR */
        .topbar {
            background: var(--white);
            border-bottom: 1px solid var(--gray-100);
            padding: 0 28px;
            height: 60px;
            display: flex; align-items: center; justify-content: space-between;
            flex-shrink: 0;
        }
        .topbar-title { font-size: 20px; font-weight: 800; color: var(--gray-800); }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .topbar-greeting { font-size: 13px; font-weight: 500; color: var(--gray-600); }
        .topbar-greeting strong { color: var(--gray-800); font-weight: 700; }
        .topbar-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: var(--blue-500);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 800; color: white;
        }

        /* CONTENT */
        .content { flex: 1; overflow-y: auto; padding: 24px 28px; display: flex; flex-direction: column; gap: 20px; }
        .content::-webkit-scrollbar { width: 4px; }
        .content::-webkit-scrollbar-thumb { background: var(--gray-200); border-radius: 4px; }

        /* STAT CARDS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }
        .stat-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--gray-100);
            padding: 18px 20px;
            display: flex; flex-direction: column; gap: 4px;
            position: relative; overflow: hidden;
            transition: box-shadow .15s;
        }
        .stat-card:hover { box-shadow: var(--shadow-md); }
        .stat-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 3px;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }
        .stat-card.blue::before   { background: var(--blue-500); }
        .stat-card.green::before  { background: var(--green-500); }
        .stat-card.purple::before { background: #8B5CF6; }
        .stat-card.amber::before  { background: var(--amber-400); }
        .stat-icon {
            width: 36px; height: 36px; border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 8px;
        }
        .stat-icon svg { width: 18px; height: 18px; }
        .stat-card.blue .stat-icon   { background: var(--blue-50); color: var(--blue-500); }
        .stat-card.green .stat-icon  { background: var(--green-50); color: var(--green-500); }
        .stat-card.purple .stat-icon { background: #F5F3FF; color: #7C3AED; }
        .stat-card.amber .stat-icon  { background: var(--amber-50); color: #D97706; }
        .stat-label { font-size: 12px; font-weight: 600; color: var(--gray-400); text-transform: uppercase; letter-spacing: .4px; }
        .stat-val   { font-size: 26px; font-weight: 800; color: var(--gray-800); line-height: 1.1; margin: 2px 0; }
        .stat-card.amber .stat-val { color: #D97706; }
        .stat-sub   { font-size: 12px; color: var(--gray-400); font-weight: 500; }

        /* ROW 2 — Chart + Terlaris */
        .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

        /* CARD */
        .card {
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--gray-100);
            padding: 20px 22px;
        }
        .card-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 18px;
        }
        .card-title { font-size: 14px; font-weight: 700; color: var(--gray-800); }
        .card-badge {
            font-size: 11px; font-weight: 600;
            padding: 3px 10px; border-radius: 100px;
            background: var(--blue-50); color: var(--blue-600);
        }

        /* BAR CHART */
        .bar-chart {
            display: flex; align-items: flex-end;
            gap: 10px; height: 130px;
        }
        .bar-col {
            flex: 1; display: flex; flex-direction: column;
            align-items: center; gap: 6px; height: 100%;
            justify-content: flex-end;
        }
        .bar-val { font-size: 10px; font-weight: 600; color: var(--gray-400); }
        .bar {
            width: 100%; border-radius: 5px 5px 0 0;
            background: var(--blue-100);
            min-height: 4px;
            position: relative; overflow: hidden;
            transition: background .2s;
        }
        .bar.current { background: var(--blue-500); }
        .bar:hover { background: var(--blue-400); }
        .bar-label { font-size: 11px; font-weight: 600; color: var(--gray-400); }
        .bar-label.current { color: var(--blue-500); }

        /* TOP MENU */
        .menu-terlaris-list { display: flex; flex-direction: column; gap: 10px; }
        .menu-rank-item { display: flex; align-items: center; gap: 10px; }
        .rank-num {
            width: 22px; height: 22px; border-radius: 50%;
            background: var(--gray-100); color: var(--gray-600);
            font-size: 11px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .rank-num.top { background: var(--blue-50); color: var(--blue-600); }
        .rank-info { flex: 1; min-width: 0; }
        .rank-nama { font-size: 13px; font-weight: 600; color: var(--gray-800); margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .rank-bar-wrap { height: 5px; background: var(--gray-100); border-radius: 100px; overflow: hidden; }
        .rank-bar-fill { height: 100%; background: var(--blue-400); border-radius: 100px; }
        .rank-bar-fill.top { background: var(--blue-500); }
        .rank-terjual { font-size: 12px; font-weight: 700; color: var(--blue-500); flex-shrink: 0; }

        /* ROW 3 — Stok + Transaksi */
        .row3 { display: grid; grid-template-columns: 1fr 1.4fr; gap: 14px; }

        /* STOK MENIPIS */
        .stok-list { display: flex; flex-direction: column; gap: 8px; }
        .stok-item {
            display: flex; align-items: center; justify-content: space-between;
            gap: 10px; padding: 10px 14px;
            border-radius: var(--radius-md);
            border: 1px solid;
        }
        .stok-item.danger { background: var(--red-50); border-color: #FECACA; }
        .stok-item.warn   { background: var(--amber-50); border-color: #FDE68A; }
        .stok-nama { font-size: 13px; font-weight: 600; }
        .stok-item.danger .stok-nama { color: #991B1B; }
        .stok-item.warn   .stok-nama { color: #92400E; }
        .stok-badge {
            font-size: 11px; font-weight: 700;
            padding: 3px 10px; border-radius: 100px; white-space: nowrap;
        }
        .stok-item.danger .stok-badge { background: #FEE2E2; color: var(--red-500); }
        .stok-item.warn   .stok-badge { background: #FEF3C7; color: #D97706; }
        .stok-aman {
            display: flex; align-items: center; gap: 8px;
            color: var(--green-500); font-size: 13px; font-weight: 600;
            padding: 12px 0;
        }
        .stok-aman svg { width: 18px; height: 18px; }

        /* TRANSAKSI TERBARU */
        .tx-table { width: 100%; border-collapse: collapse; }
        .tx-table thead tr { border-bottom: 1.5px solid var(--gray-100); }
        .tx-table th {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .4px; color: var(--gray-400);
            padding: 0 0 10px; text-align: left;
        }
        .tx-table th:last-child { text-align: right; }
        .tx-table tbody tr { border-bottom: 1px solid var(--gray-100); transition: background .12s; }
        .tx-table tbody tr:last-child { border-bottom: none; }
        .tx-table tbody tr:hover { background: var(--gray-50); }
        .tx-table td { padding: 10px 0; vertical-align: middle; }
        .tx-id { font-size: 12px; font-weight: 700; color: var(--blue-500); font-family: monospace; }
        .tx-items { font-size: 12px; color: var(--gray-600); max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .tx-time { font-size: 11px; color: var(--gray-400); }
        .tx-total { font-size: 13px; font-weight: 700; color: var(--gray-800); text-align: right; }
        .tx-status {
            font-size: 11px; font-weight: 600; padding: 3px 9px;
            border-radius: 100px; display: inline-block;
        }
        .tx-status.selesai { background: var(--green-50); color: var(--green-700); }
        .tx-status.pending { background: var(--amber-50); color: var(--amber-700); }
        .tx-status.batal   { background: var(--red-50);   color: var(--red-700); }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-name">TokoRoti</div>
        <div class="sidebar-brand-role">Manajer</div>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a href="inventori.php" class="nav-item <?= $currentPage === 'inventori' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
            Inventori
        </a>
        <a href="laporan.php" class="nav-item <?= $currentPage === 'laporan' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Laporan
        </a>
        <a href="karyawan.php" class="nav-item <?= $currentPage === 'karyawan' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            Karyawan
        </a>
    </nav>
    <div class="sidebar-bottom">
        <a href="../process/logout.php" class="nav-item logout">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
            Logout
        </a>
    </div>
</aside>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <div class="topbar-title">Dashboard</div>
        <div class="topbar-right">
            <div class="topbar-greeting">Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong> 👋</div>
            <div class="topbar-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 2)) ?></div>
        </div>
    </div>

    <div class="content">

        <!-- STAT CARDS -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                </div>
                <div class="stat-label">Pendapatan bulan ini</div>
                <div class="stat-val">Rp <?= number_format($pendapatanBulanIni / 1000, 0, ',', '.') ?>rb</div>
                <div class="stat-sub"><?= date('F Y') ?></div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
                <div class="stat-label">Transaksi bulan ini</div>
                <div class="stat-val"><?= $transaksBulanIni ?></div>
                <div class="stat-sub"><?= date('F Y') ?></div>
            </div>
            <div class="stat-card purple">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
                </div>
                <div class="stat-label">Total menu aktif</div>
                <div class="stat-val"><?= $totalMenu ?></div>
                <div class="stat-sub">produk tersedia</div>
            </div>
            <div class="stat-card amber">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
                <div class="stat-label">Stok menipis</div>
                <div class="stat-val"><?= $stokMenipis ?></div>
                <div class="stat-sub"><?= $stokMenipis > 0 ? 'Perlu restock' : 'Semua aman' ?></div>
            </div>
        </div>

        <!-- ROW 2: CHART + TERLARIS -->
        <div class="row2">

            <!-- Bar Chart Pendapatan -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Pendapatan per bulan</div>
                    <div class="card-badge">6 bulan terakhir</div>
                </div>
                <div class="bar-chart">
                    <?php
                    $bulanSekarang = date('n');
                    foreach ($pendapatanBulanan as $pb):
                        $pct = $maxPendapatan > 0 ? round(($pb['total'] / $maxPendapatan) * 100) : 4;
                        $isCurrent = ((int)$pb['bulan_num'] === (int)$bulanSekarang);
                        $valLabel = $pb['total'] >= 1000000
                            ? 'Rp ' . round($pb['total']/1000000, 1) . 'jt'
                            : ($pb['total'] >= 1000 ? 'Rp ' . round($pb['total']/1000) . 'rb' : 'Rp ' . $pb['total']);
                    ?>
                    <div class="bar-col">
                        <div class="bar-val"><?= $valLabel ?></div>
                        <div class="bar <?= $isCurrent ? 'current' : '' ?>" style="height: <?= max($pct, 4) ?>%;"></div>
                        <div class="bar-label <?= $isCurrent ? 'current' : '' ?>"><?= $pb['bulan'] ?></div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($pendapatanBulanan)): ?>
                    <div style="width:100%; text-align:center; color:var(--gray-400); font-size:13px; padding-top:40px;">Belum ada data transaksi.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Menu Terlaris -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Menu Terlaris</div>
                    <div class="card-badge">Top 5</div>
                </div>
                <?php if ($menuTerlaris): ?>
                <div class="menu-terlaris-list">
                    <?php foreach ($menuTerlaris as $i => $m):
                        $pct = round(($m['total_terjual'] / $maxTerlaris) * 100);
                        $isTop = $i === 0;
                    ?>
                    <div class="menu-rank-item">
                        <div class="rank-num <?= $isTop ? 'top' : '' ?>"><?= $i + 1 ?></div>
                        <div class="rank-info">
                            <div class="rank-nama"><?= htmlspecialchars($m['nama_menu']) ?></div>
                            <div class="rank-bar-wrap">
                                <div class="rank-bar-fill <?= $isTop ? 'top' : '' ?>" style="width: <?= $pct ?>%;"></div>
                            </div>
                        </div>
                        <div class="rank-terjual"><?= $m['total_terjual'] ?> terjual</div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p style="font-size:13px; color:var(--gray-400);">Belum ada data penjualan.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- ROW 3: STOK + TRANSAKSI -->
        <div class="row3">

            <!-- Stok Menipis -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Stok Menipis ⚠️</div>
                    <?php if ($stokMenipis > 0): ?>
                    <div class="card-badge" style="background:#FEE2E2; color:#DC2626;"><?= $stokMenipis ?> item</div>
                    <?php endif; ?>
                </div>
                <?php if ($daftarStokMenipis): ?>
                <div class="stok-list">
                    <?php foreach ($daftarStokMenipis as $s):
                        $isDanger = $s['stok'] <= 5;
                    ?>
                    <div class="stok-item <?= $isDanger ? 'danger' : 'warn' ?>">
                        <span class="stok-nama"><?= htmlspecialchars($s['nama_menu']) ?></span>
                        <span class="stok-badge">Sisa <?= $s['stok'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="stok-aman">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Semua stok dalam kondisi aman
                </div>
                <?php endif; ?>
            </div>

            <!-- Transaksi Terbaru -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Transaksi Terbaru</div>
                    <a href="laporan.php" style="font-size:12px; font-weight:600; color:var(--blue-500); text-decoration:none;">Lihat semua →</a>
                </div>
                <table class="tx-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Item</th>
                            <th>Waktu</th>
                            <th style="text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transaksiTerbaru as $t):
                            $statusClass = strtolower($t['status']) === 'selesai' ? 'selesai' : (strtolower($t['status']) === 'batal' ? 'batal' : 'pending');
                        ?>
                        <tr>
                            <td>
                                <div class="tx-id"><?= htmlspecialchars($t['id_transaksi']) ?></div>
                                <span class="tx-status <?= $statusClass ?>"><?= htmlspecialchars($t['status']) ?></span>
                            </td>
                            <td>
                                <div class="tx-items"><?= htmlspecialchars($t['item_names'] ?? '—') ?></div>
                            </td>
                            <td>
                                <div class="tx-time"><?= date('d M', strtotime($t['waktu_transaksi'])) ?></div>
                                <div class="tx-time"><?= date('H:i', strtotime($t['waktu_transaksi'])) ?></div>
                            </td>
                            <td class="tx-total">Rp <?= number_format($t['total_bayar'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($transaksiTerbaru)): ?>
                        <tr><td colspan="4" style="text-align:center; color:var(--gray-400); font-size:13px; padding:20px 0;">Belum ada transaksi.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div><!-- .content -->
</div><!-- .main -->
</body>
</html>
