<?php
require_once '../includes/auth.php';
requireRole('Manajer');
require_once '../config/database.php';

$currentPage = 'inventori';

$query = "SELECT m.*, k.nama_kategori 
          FROM menu m 
          JOIN kategori k ON m.id_kategori = k.id_kategori 
          WHERE m.is_deleted = 0 
          ORDER BY m.id_menu DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

$kategoriList = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll(PDO::FETCH_ASSOC);

$totalMenu  = count($menus);
$totalStok  = array_sum(array_column($menus, 'stok'));
$stokMenipis = count(array_filter($menus, fn($m) => $m['stok'] <= 10));

$filterKat  = $_GET['kategori'] ?? '';
$filterCari = trim($_GET['cari'] ?? '');

$pesan = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventori — TokoRoti</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue-50:  #EBF3FD;
            --blue-100: #C4DCFA;
            --blue-400: #3B8EE8;
            --blue-500: #1F72D3;
            --blue-600: #1559A8;
            --gray-50:  #F7F8FA;
            --gray-100: #EDEEF2;
            --gray-200: #D8DAE2;
            --gray-400: #9094A4;
            --gray-600: #5A5E6E;
            --gray-800: #2C2F3C;
            --white:    #FFFFFF;
            --green-50: #ECFDF5;
            --green-600:#059669;
            --green-700:#065F46;
            --amber-50: #FFFBEB;
            --amber-700:#92400E;
            --red-50:   #FEF2F2;
            --red-500:  #EF4444;
            --red-600:  #DC2626;
            --red-700:  #7F1D1D;
            --purple-50:#F5F3FF;
            --purple-600:#7C3AED;
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
            display: flex; flex-direction: column;
            flex-shrink: 0;
            position: sticky; top: 0; height: 100vh;
        }
        .sidebar-brand { padding: 24px 20px 20px; border-bottom: 1px solid rgba(255,255,255,.12); }
        .sidebar-brand-name { font-size: 20px; font-weight: 800; color: var(--white); letter-spacing: -.3px; }
        .sidebar-brand-role { font-size: 11px; font-weight: 500; color: rgba(255,255,255,.55); margin-top: 2px; text-transform: uppercase; letter-spacing: .5px; }
        .sidebar-nav { flex: 1; padding: 12px; display: flex; flex-direction: column; gap: 2px; }
        .nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 12px; border-radius: var(--radius-md);
            color: rgba(255,255,255,.75); text-decoration: none;
            font-size: 14px; font-weight: 600; transition: all .15s;
        }
        .nav-item:hover { background: rgba(255,255,255,.12); color: var(--white); }
        .nav-item.active { background: rgba(255,255,255,.2); color: var(--white); }
        .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; opacity: .85; }
        .nav-item.active svg { opacity: 1; }
        .sidebar-bottom { padding: 12px; border-top: 1px solid rgba(255,255,255,.1); }
        .nav-item.logout { color: rgba(255,255,255,.6); }
        .nav-item.logout:hover { background: rgba(255,255,255,.1); color: var(--white); }

        /* ── MAIN ── */
        .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }

        /* TOPBAR */
        .topbar {
            background: var(--white); border-bottom: 1px solid var(--gray-100);
            padding: 0 28px; height: 60px;
            display: flex; align-items: center; justify-content: space-between;
            flex-shrink: 0;
        }
        .topbar-title { font-size: 20px; font-weight: 800; }
        .btn-tambah {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 18px;
            background: var(--blue-500); color: var(--white);
            border-radius: var(--radius-md); text-decoration: none;
            font-size: 13px; font-weight: 700;
            box-shadow: 0 3px 10px rgba(31,114,211,.3);
            transition: background .15s;
        }
        .btn-tambah:hover { background: var(--blue-600); }
        .btn-tambah svg { width: 16px; height: 16px; }

        /* CONTENT */
        .content { flex: 1; overflow-y: auto; padding: 24px 28px; display: flex; flex-direction: column; gap: 20px; }
        .content::-webkit-scrollbar { width: 4px; }
        .content::-webkit-scrollbar-thumb { background: var(--gray-200); border-radius: 4px; }

        /* TOAST */
        .toast {
            padding: 12px 18px; border-radius: var(--radius-md);
            font-size: 13px; font-weight: 600;
            display: flex; align-items: center; gap: 8px;
        }
        .toast.sukses { background: #D1FAE5; color: var(--green-700); border: 1px solid #6EE7B7; }
        .toast.gagal  { background: #FEE2E2; color: var(--red-700);   border: 1px solid #FCA5A5; }
        .toast svg { width: 16px; height: 16px; flex-shrink: 0; }

        /* STAT CARDS */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .stat-card {
            background: var(--white); border-radius: var(--radius-lg);
            border: 1px solid var(--gray-100); padding: 18px 20px;
            display: flex; flex-direction: column; gap: 4px;
            position: relative; overflow: hidden;
        }
        .stat-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }
        .stat-card.blue::before   { background: var(--blue-500); }
        .stat-card.purple::before { background: var(--purple-600); }
        .stat-card.amber::before  { background: #FBBF24; }
        .stat-icon {
            width: 36px; height: 36px; border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center; margin-bottom: 6px;
        }
        .stat-icon svg { width: 18px; height: 18px; }
        .stat-card.blue .stat-icon   { background: var(--blue-50);   color: var(--blue-500); }
        .stat-card.purple .stat-icon { background: var(--purple-50); color: var(--purple-600); }
        .stat-card.amber .stat-icon  { background: var(--amber-50);  color: #D97706; }
        .stat-label { font-size: 12px; font-weight: 600; color: var(--gray-400); text-transform: uppercase; letter-spacing: .4px; }
        .stat-val   { font-size: 28px; font-weight: 800; color: var(--gray-800); line-height: 1.1; }
        .stat-card.amber .stat-val { color: #D97706; }
        .stat-sub   { font-size: 12px; color: var(--gray-400); font-weight: 500; }

        /* FILTER BAR */
        .filter-bar {
            background: var(--white); border-radius: var(--radius-lg);
            border: 1px solid var(--gray-100); padding: 14px 18px;
            display: flex; align-items: center; gap: 12px;
        }
        .filter-search {
            flex: 1; position: relative;
        }
        .filter-search svg {
            position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
            color: var(--gray-400); pointer-events: none;
            width: 15px; height: 15px;
        }
        .filter-search input {
            width: 100%; padding: 9px 12px 9px 34px;
            border: 1.5px solid var(--gray-200); border-radius: var(--radius-md);
            font-family: var(--font); font-size: 13px; color: var(--gray-800);
            outline: none; background: var(--gray-50);
            transition: border-color .15s, box-shadow .15s;
        }
        .filter-search input:focus { border-color: var(--blue-400); box-shadow: 0 0 0 3px rgba(59,142,232,.12); background: var(--white); }
        .filter-search input::placeholder { color: var(--gray-400); }
        .filter-select {
            padding: 9px 36px 9px 14px;
            border: 1.5px solid var(--gray-200); border-radius: var(--radius-md);
            font-family: var(--font); font-size: 13px; color: var(--gray-700, #374151);
            outline: none; background: var(--gray-50);
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%239094A4' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            cursor: pointer; min-width: 160px;
            transition: border-color .15s;
        }
        .filter-select:focus { border-color: var(--blue-400); }
        .btn-filter {
            padding: 9px 18px;
            background: var(--blue-500); color: var(--white);
            border: none; border-radius: var(--radius-md);
            font-family: var(--font); font-size: 13px; font-weight: 600;
            cursor: pointer; transition: background .15s; white-space: nowrap;
        }
        .btn-filter:hover { background: var(--blue-600); }
        .btn-reset-filter {
            padding: 9px 14px;
            background: none; color: var(--gray-500, #6B7280);
            border: 1.5px solid var(--gray-200); border-radius: var(--radius-md);
            font-family: var(--font); font-size: 13px; font-weight: 600;
            cursor: pointer; text-decoration: none;
            transition: all .15s; white-space: nowrap;
        }
        .btn-reset-filter:hover { border-color: var(--gray-400); color: var(--gray-800); }

        /* TABLE CARD */
        .table-card {
            background: var(--white); border-radius: var(--radius-lg);
            border: 1px solid var(--gray-100); overflow: hidden;
        }
        .table-card-header {
            padding: 16px 22px;
            border-bottom: 1px solid var(--gray-100);
            display: flex; align-items: center; justify-content: space-between;
        }
        .table-card-title { font-size: 14px; font-weight: 700; }
        .table-count { font-size: 12px; font-weight: 600; color: var(--gray-400); }

        .inv-table { width: 100%; border-collapse: collapse; }
        .inv-table thead tr { background: var(--gray-50); }
        .inv-table th {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .5px; color: var(--gray-400);
            padding: 12px 16px; text-align: left;
        }
        .inv-table th.center { text-align: center; }
        .inv-table th.right  { text-align: right; }
        .inv-table tbody tr { border-bottom: 1px solid var(--gray-100); transition: background .12s; }
        .inv-table tbody tr:last-child { border-bottom: none; }
        .inv-table tbody tr:hover { background: var(--gray-50); }
        .inv-table td { padding: 13px 16px; vertical-align: middle; }
        .inv-table td.center { text-align: center; }
        .inv-table td.right  { text-align: right; }

        .menu-img {
            width: 44px; height: 44px; border-radius: var(--radius-sm);
            object-fit: cover; background: var(--gray-100); display: block;
        }
        .menu-img-placeholder {
            width: 44px; height: 44px; border-radius: var(--radius-sm);
            background: linear-gradient(135deg, var(--blue-50), var(--gray-100));
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
        }

        .menu-id   { font-size: 12px; font-weight: 700; color: var(--blue-500); font-family: monospace; }
        .menu-nama { font-size: 13px; font-weight: 700; color: var(--gray-800); }
        .menu-kat  { font-size: 11px; color: var(--gray-400); margin-top: 2px; }
        .menu-harga { font-size: 13px; font-weight: 700; color: var(--gray-800); }

        .stok-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 4px 10px; border-radius: 100px;
            font-size: 12px; font-weight: 700;
        }
        .stok-aman   { background: var(--green-50); color: var(--green-600); }
        .stok-low    { background: var(--amber-50); color: #D97706; }
        .stok-danger { background: var(--red-50);   color: var(--red-600); }

        .aksi-wrap { display: flex; align-items: center; gap: 6px; justify-content: flex-end; }
        .btn-edit {
            display: flex; align-items: center; gap: 5px;
            padding: 6px 12px; border-radius: var(--radius-sm);
            background: var(--blue-50); color: var(--blue-600);
            text-decoration: none; font-size: 12px; font-weight: 700;
            transition: all .12s;
        }
        .btn-edit:hover { background: var(--blue-500); color: var(--white); }
        .btn-edit svg { width: 13px; height: 13px; }
        .btn-hapus {
            display: flex; align-items: center; gap: 5px;
            padding: 6px 12px; border-radius: var(--radius-sm);
            background: var(--red-50); color: var(--red-600);
            text-decoration: none; font-size: 12px; font-weight: 700;
            transition: all .12s;
        }
        .btn-hapus:hover { background: var(--red-500); color: var(--white); }
        .btn-hapus svg { width: 13px; height: 13px; }

        .empty-state {
            padding: 56px 24px; text-align: center;
            color: var(--gray-400); font-size: 14px;
        }
        .empty-state svg { width: 44px; height: 44px; margin-bottom: 12px; opacity: .4; display: block; margin-left: auto; margin-right: auto; }
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
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
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
        <div class="topbar-title">Inventori</div>
        <a href="tambah.php" class="btn-tambah">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Tambah Menu
        </a>
    </div>

    <div class="content">

        <?php if ($pesan === 'tambah'): ?>
        <div class="toast sukses"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Menu berhasil ditambahkan!</div>
        <?php elseif ($pesan === 'edit'): ?>
        <div class="toast sukses"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Menu berhasil diperbarui!</div>
        <?php elseif ($pesan === 'hapus'): ?>
        <div class="toast sukses"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Menu berhasil dihapus!</div>
        <?php elseif ($pesan === 'gagal'): ?>
        <div class="toast gagal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Terjadi kesalahan, coba lagi.</div>
        <?php endif; ?>

        <!-- STAT CARDS -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
                </div>
                <div class="stat-label">Total Menu</div>
                <div class="stat-val"><?= $totalMenu ?></div>
                <div class="stat-sub">produk aktif</div>
            </div>
            <div class="stat-card purple">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
                </div>
                <div class="stat-label">Total Stok</div>
                <div class="stat-val"><?= number_format($totalStok, 0, ',', '.') ?></div>
                <div class="stat-sub">unit tersedia</div>
            </div>
            <div class="stat-card amber">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
                <div class="stat-label">Stok Menipis</div>
                <div class="stat-val"><?= $stokMenipis ?></div>
                <div class="stat-sub"><?= $stokMenipis > 0 ? 'perlu restock' : 'semua aman' ?></div>
            </div>
        </div>

        <!-- FILTER -->
        <form method="GET" action="inventori.php">
            <div class="filter-bar">
                <div class="filter-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input type="text" name="cari" placeholder="Cari nama menu..." value="<?= htmlspecialchars($filterCari) ?>">
                </div>
                <select name="kategori" class="filter-select">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategoriList as $k): ?>
                    <option value="<?= $k['id_kategori'] ?>" <?= $filterKat == $k['id_kategori'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['nama_kategori']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-filter">Cari</button>
                <?php if ($filterKat || $filterCari): ?>
                <a href="inventori.php" class="btn-reset-filter">Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- TABLE -->
        <?php
        // Filter PHP-side
        $menuFiltered = array_filter($menus, function($m) use ($filterKat, $filterCari) {
            $matchKat  = !$filterKat  || $m['id_kategori'] == $filterKat;
            $matchCari = !$filterCari || stripos($m['nama_menu'], $filterCari) !== false;
            return $matchKat && $matchCari;
        });
        ?>
        <div class="table-card">
            <div class="table-card-header">
                <div class="table-card-title">Daftar Menu</div>
                <div class="table-count"><?= count($menuFiltered) ?> item ditemukan</div>
            </div>
            <table class="inv-table">
                <thead>
                    <tr>
                        <th style="width:52px;"></th>
                        <th>ID</th>
                        <th>Nama Menu</th>
                        <th>Kategori</th>
                        <th class="center">Stok</th>
                        <th class="right">Harga</th>
                        <th class="right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($menuFiltered)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                                Tidak ada menu yang sesuai filter.
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($menuFiltered as $m):
                        $stokClass = $m['stok'] <= 5 ? 'stok-danger' : ($m['stok'] <= 10 ? 'stok-low' : 'stok-aman');
                        $stokLabel = $m['stok'] <= 5 ? '⚠ ' . $m['stok'] . ' unit' : $m['stok'] . ' unit';
                    ?>
                    <tr>
                        <td>
                            <?php if (!empty($m['gambar'])): ?>
                            <img src="assets/<?= htmlspecialchars($m['gambar']) ?>" class="menu-img" alt="<?= htmlspecialchars($m['nama_menu']) ?>">
                            <?php else: ?>
                            <div class="menu-img-placeholder">🍞</div>
                            <?php endif; ?>
                        </td>
                        <td><span class="menu-id"><?= htmlspecialchars($m['id_menu']) ?></span></td>
                        <td>
                            <div class="menu-nama"><?= htmlspecialchars($m['nama_menu']) ?></div>
                        </td>
                        <td><span style="font-size:13px; color:var(--gray-600);"><?= htmlspecialchars($m['nama_kategori']) ?></span></td>
                        <td class="center">
                            <span class="stok-badge <?= $stokClass ?>"><?= $stokLabel ?></span>
                        </td>
                        <td class="right">
                            <span class="menu-harga">Rp <?= number_format($m['harga'], 0, ',', '.') ?></span>
                        </td>
                        <td class="right">
                            <div class="aksi-wrap">
                                <a href="edit.php?id=<?= $m['id_menu'] ?>" class="btn-edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit
                                </a>
                                <a href="hapus.php?id=<?= $m['id_menu'] ?>" class="btn-hapus">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                                    Hapus
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
</body>
</html>
