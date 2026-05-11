<?php
require_once '../includes/auth.php';
requireRole('Manajer');
require_once '../config/database.php';
$currentPage = 'karyawan';

$karyawan = $conn->query("SELECT * FROM karyawan ORDER BY jabatan DESC, nama_karyawan ASC")
                 ->fetchAll(PDO::FETCH_ASSOC);

$pesan = $_GET['pesan'] ?? '';

// Mode: tambah / edit
$mode = $_GET['mode'] ?? 'tambah';
$editData = null;
if ($mode === 'edit' && isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM karyawan WHERE id_karyawan = :id");
    $stmt->execute([':id' => $_GET['id']]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$editData) $mode = 'tambah';
}

$totalKaryawan = count($karyawan);
$totalAktif    = count(array_filter($karyawan, fn($k) => $k['is_active']));
$totalManajer  = count(array_filter($karyawan, fn($k) => $k['jabatan'] === 'Manajer'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Karyawan — TokoRoti</title>
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
            --gray-700: #374151;
            --gray-800: #2C2F3C;
            --white:    #FFFFFF;
            --green-50: #ECFDF5;
            --green-500:#10B981;
            --green-600:#059669;
            --green-700:#065F46;
            --red-50:   #FEF2F2;
            --red-500:  #EF4444;
            --red-600:  #DC2626;
            --red-700:  #7F1D1D;
            --amber-50: #FFFBEB;
            --amber-600:#D97706;
            --purple-50:#F5F3FF;
            --purple-500:#8B5CF6;
            --purple-600:#7C3AED;
            --sidebar-w: 220px;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,.06);
            --shadow-md: 0 4px 16px rgba(0,0,0,.08);
            --font: 'Plus Jakarta Sans', sans-serif;
        }

        body { font-family: var(--font); background: #F0F4FB; color: var(--gray-800); min-height: 100vh; display: flex; }

        /* ── SIDEBAR ── */
        .sidebar { width: var(--sidebar-w); background: var(--blue-500); min-height: 100vh; display: flex; flex-direction: column; flex-shrink: 0; position: sticky; top: 0; height: 100vh; }
        .sidebar-brand { padding: 24px 20px 20px; border-bottom: 1px solid rgba(255,255,255,.12); }
        .sidebar-brand-name { font-size: 20px; font-weight: 800; color: var(--white); letter-spacing: -.3px; }
        .sidebar-brand-role { font-size: 11px; font-weight: 500; color: rgba(255,255,255,.55); margin-top: 2px; text-transform: uppercase; letter-spacing: .5px; }
        .sidebar-nav { flex: 1; padding: 12px; display: flex; flex-direction: column; gap: 2px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 11px 12px; border-radius: var(--radius-md); color: rgba(255,255,255,.75); text-decoration: none; font-size: 14px; font-weight: 600; transition: all .15s; }
        .nav-item:hover { background: rgba(255,255,255,.12); color: var(--white); }
        .nav-item.active { background: rgba(255,255,255,.2); color: var(--white); }
        .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; opacity: .85; }
        .nav-item.active svg { opacity: 1; }
        .sidebar-bottom { padding: 12px; border-top: 1px solid rgba(255,255,255,.1); }
        .nav-item.logout { color: rgba(255,255,255,.6); }
        .nav-item.logout:hover { background: rgba(255,255,255,.1); color: var(--white); }

        /* ── MAIN ── */
        .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }

        .topbar { background: var(--white); border-bottom: 1px solid var(--gray-100); padding: 0 28px; height: 60px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
        .topbar-title { font-size: 20px; font-weight: 800; }
        .btn-tambah { display: flex; align-items: center; gap: 8px; padding: 10px 18px; background: var(--blue-500); color: var(--white); border-radius: var(--radius-md); text-decoration: none; font-size: 13px; font-weight: 700; box-shadow: 0 3px 10px rgba(31,114,211,.3); transition: background .15s; }
        .btn-tambah:hover { background: var(--blue-600); }
        .btn-tambah svg { width: 16px; height: 16px; }
        .btn-tambah.active-mode { background: var(--blue-600); box-shadow: 0 3px 14px rgba(21,89,168,.4); }

        /* CONTENT */
        .content { flex: 1; overflow-y: auto; padding: 24px 28px; display: flex; flex-direction: column; gap: 20px; }
        .content::-webkit-scrollbar { width: 4px; }
        .content::-webkit-scrollbar-thumb { background: var(--gray-200); border-radius: 4px; }

        /* TOAST */
        .toast { padding: 12px 18px; border-radius: var(--radius-md); font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .toast.sukses { background: #D1FAE5; color: var(--green-700); border: 1px solid #6EE7B7; }
        .toast.hapus  { background: var(--red-50); color: var(--red-700); border: 1px solid #FCA5A5; }
        .toast svg { width: 16px; height: 16px; flex-shrink: 0; }

        /* STAT CARDS */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .stat-card { background: var(--white); border-radius: var(--radius-lg); border: 1px solid var(--gray-100); padding: 16px 20px; display: flex; align-items: center; gap: 14px; }
        .stat-icon { width: 42px; height: 42px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .stat-icon svg { width: 20px; height: 20px; }
        .stat-card.blue .stat-icon   { background: var(--blue-50);   color: var(--blue-500); }
        .stat-card.green .stat-icon  { background: var(--green-50);  color: var(--green-500); }
        .stat-card.purple .stat-icon { background: var(--purple-50); color: var(--purple-500); }
        .stat-label { font-size: 12px; font-weight: 600; color: var(--gray-400); text-transform: uppercase; letter-spacing: .4px; }
        .stat-val   { font-size: 24px; font-weight: 800; color: var(--gray-800); line-height: 1.2; }

        /* SPLIT LAYOUT */
        .split-layout { display: grid; grid-template-columns: 1fr 340px; gap: 16px; align-items: start; }

        /* TABLE CARD */
        .table-card { background: var(--white); border-radius: var(--radius-lg); border: 1px solid var(--gray-100); overflow: hidden; }
        .table-card-header { padding: 16px 20px; border-bottom: 1px solid var(--gray-100); display: flex; align-items: center; justify-content: space-between; }
        .table-card-title { font-size: 14px; font-weight: 700; }
        .table-count { font-size: 12px; font-weight: 600; color: var(--gray-400); }

        .kar-table { width: 100%; border-collapse: collapse; }
        .kar-table thead tr { background: var(--gray-50); }
        .kar-table th { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--gray-400); padding: 11px 16px; text-align: left; }
        .kar-table th.right { text-align: right; }
        .kar-table tbody tr { border-bottom: 1px solid var(--gray-100); transition: background .12s; }
        .kar-table tbody tr:last-child { border-bottom: none; }
        .kar-table tbody tr:hover { background: var(--gray-50); }
        .kar-table tbody tr.editing { background: var(--blue-50); }
        .kar-table td { padding: 12px 16px; vertical-align: middle; }
        .kar-table td.right { text-align: right; }

        /* Avatar inisial */
        .kar-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 800; color: var(--white);
            flex-shrink: 0;
        }
        .avatar-blue   { background: var(--blue-500); }
        .avatar-purple { background: var(--purple-500); }
        .avatar-green  { background: var(--green-500); }

        .kar-info-wrap { display: flex; align-items: center; gap: 10px; }
        .kar-id   { font-size: 11px; font-weight: 700; color: var(--blue-500); font-family: monospace; }
        .kar-nama { font-size: 13px; font-weight: 700; color: var(--gray-800); }
        .kar-user { font-size: 11px; color: var(--gray-400); margin-top: 1px; }

        .jabatan-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 100px; font-size: 12px; font-weight: 700; }
        .jabatan-manajer { background: var(--purple-50); color: var(--purple-600); }
        .jabatan-kasir   { background: var(--blue-50);   color: var(--blue-600); }

        .status-badge { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 600; }
        .status-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
        .status-aktif    .status-dot { background: var(--green-500); }
        .status-nonaktif .status-dot { background: var(--gray-400); }
        .status-aktif    { color: var(--green-600); }
        .status-nonaktif { color: var(--gray-400); }

        .aksi-wrap { display: flex; align-items: center; gap: 6px; justify-content: flex-end; }
        .btn-edit-row {
            display: flex; align-items: center; gap: 4px;
            padding: 6px 11px; border-radius: var(--radius-sm);
            background: var(--blue-50); color: var(--blue-600);
            text-decoration: none; font-size: 12px; font-weight: 700;
            transition: all .12s;
        }
        .btn-edit-row:hover, .btn-edit-row.active { background: var(--blue-500); color: var(--white); }
        .btn-edit-row svg { width: 12px; height: 12px; }
        .btn-hapus-row {
            display: flex; align-items: center; gap: 4px;
            padding: 6px 11px; border-radius: var(--radius-sm);
            background: var(--red-50); color: var(--red-600);
            text-decoration: none; font-size: 12px; font-weight: 700;
            transition: all .12s;
        }
        .btn-hapus-row:hover { background: var(--red-500); color: var(--white); }
        .btn-hapus-row svg { width: 12px; height: 12px; }
        .self-badge { font-size: 10px; font-weight: 600; padding: 2px 7px; background: var(--gray-100); color: var(--gray-400); border-radius: 100px; }

        /* FORM CARD (kanan) */
        .form-card { background: var(--white); border-radius: var(--radius-lg); border: 1px solid var(--gray-100); overflow: hidden; position: sticky; top: 0; }
        .form-card-header { padding: 16px 20px; border-bottom: 1px solid var(--gray-100); display: flex; align-items: center; gap: 10px; }
        .form-card-icon { width: 32px; height: 32px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; }
        .form-card-icon.tambah { background: var(--blue-50); color: var(--blue-500); }
        .form-card-icon.edit   { background: var(--amber-50); color: var(--amber-600); }
        .form-card-icon svg { width: 16px; height: 16px; }
        .form-card-title-text { font-size: 14px; font-weight: 700; }
        .form-card-body { padding: 18px 20px; display: flex; flex-direction: column; gap: 13px; }

        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-label { font-size: 12px; font-weight: 700; color: var(--gray-600); }
        .form-control {
            width: 100%; padding: 9px 12px;
            border: 1.5px solid var(--gray-200); border-radius: var(--radius-md);
            font-family: var(--font); font-size: 13px; color: var(--gray-800);
            outline: none; background: var(--gray-50);
            transition: border-color .15s, box-shadow .15s;
        }
        .form-control:focus { border-color: var(--blue-400); box-shadow: 0 0 0 3px rgba(59,142,232,.12); background: var(--white); }
        .form-control:disabled { opacity: .5; cursor: not-allowed; background: var(--gray-100); }
        .form-control.select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%239094A4' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 10px center; padding-right: 32px; cursor: pointer;
        }
        .form-hint { font-size: 11px; color: var(--gray-400); }

        .form-card-footer { padding: 14px 20px; border-top: 1px solid var(--gray-100); display: flex; gap: 8px; }
        .btn-submit { flex: 1; padding: 10px; background: var(--blue-500); color: var(--white); border: none; border-radius: var(--radius-md); font-family: var(--font); font-size: 13px; font-weight: 700; cursor: pointer; transition: background .15s; box-shadow: 0 3px 10px rgba(31,114,211,.25); }
        .btn-submit:hover { background: var(--blue-600); }
        .btn-batal { padding: 10px 14px; background: none; color: var(--gray-600); border: 1.5px solid var(--gray-200); border-radius: var(--radius-md); font-family: var(--font); font-size: 13px; font-weight: 600; text-decoration: none; text-align: center; transition: all .15s; }
        .btn-batal:hover { border-color: var(--gray-400); color: var(--gray-800); }

        /* Edit mode highlight */
        .form-card.edit-mode { border-color: var(--amber-600); }
        .form-card.edit-mode .form-card-header { background: var(--amber-50); border-bottom-color: #FDE68A; }
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
        <div class="topbar-title">Manajemen Karyawan</div>
        <a href="karyawan.php?mode=tambah" class="btn-tambah <?= $mode === 'tambah' ? 'active-mode' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Tambah Karyawan
        </a>
    </div>

    <div class="content">

        <!-- TOAST -->
        <?php if ($pesan === 'tambah'): ?>
    <div class="toast sukses">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        Karyawan baru berhasil ditambahkan!
    </div>
<?php elseif ($pesan === 'update'): ?>
    <div class="toast sukses">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        Data karyawan berhasil diperbarui!
    </div>
<?php elseif ($pesan === 'hapus'): ?>
    <div class="toast hapus">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
        Karyawan berhasil dihapus.
    </div>
<?php elseif ($pesan === 'gagal_username'): ?>
    <div class="toast hapus">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        Username sudah digunakan, silakan pilih yang lain!
    </div>
<?php elseif ($pesan === 'gagal'): ?>
    <div class="toast hapus">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
        </svg>
        Gagal menyimpan data (ID mungkin duplikat).
    </div>
<?php endif; ?>

        <!-- STAT CARDS -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Total Karyawan</div>
                    <div class="stat-val"><?= $totalKaryawan ?></div>
                </div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Karyawan Aktif</div>
                    <div class="stat-val"><?= $totalAktif ?></div>
                </div>
            </div>
            <div class="stat-card purple">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 10-16 0"/><path d="M12 12v9"/><path d="M8 16h8"/></svg>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Manajer</div>
                    <div class="stat-val"><?= $totalManajer ?></div>
                </div>
            </div>
        </div>

        <!-- SPLIT LAYOUT: Tabel + Form -->
        <div class="split-layout">

            <!-- TABEL KARYAWAN -->
            <div class="table-card">
                <div class="table-card-header">
                    <div class="table-card-title">Daftar Karyawan</div>
                    <div class="table-count"><?= $totalKaryawan ?> karyawan</div>
                </div>
                <table class="kar-table">
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>Jabatan</th>
                            <th>Status</th>
                            <th class="right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $avatarColors = ['avatar-blue', 'avatar-purple', 'avatar-green'];
                        $ci = 0;
                        foreach ($karyawan as $k):
                            $isSelf = $k['id_karyawan'] === $_SESSION['id_karyawan'];
                            $isEditing = ($mode === 'edit' && isset($editData) && $editData['id_karyawan'] === $k['id_karyawan']);
                            $avatarClass = $k['jabatan'] === 'Manajer' ? 'avatar-purple' : $avatarColors[$ci % count($avatarColors)];
                            $inisial = strtoupper(substr($k['nama_karyawan'], 0, 2));
                            $ci++;
                        ?>
                        <tr class="<?= $isEditing ? 'editing' : '' ?>">
                            <td>
                                <div class="kar-info-wrap">
                                    <div class="kar-avatar <?= $avatarClass ?>"><?= $inisial ?></div>
                                    <div>
                                        <div class="kar-id"><?= htmlspecialchars($k['id_karyawan']) ?></div>
                                        <div class="kar-nama"><?= htmlspecialchars($k['nama_karyawan']) ?></div>
                                        <div class="kar-user">@<?= htmlspecialchars($k['username']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="jabatan-badge <?= $k['jabatan'] === 'Manajer' ? 'jabatan-manajer' : 'jabatan-kasir' ?>">
                                    <?= htmlspecialchars($k['jabatan']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge <?= $k['is_active'] ? 'status-aktif' : 'status-nonaktif' ?>">
                                    <span class="status-dot"></span>
                                    <?= $k['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </td>
                            <td class="right">
                                <div class="aksi-wrap">
                                    <?php if ($isSelf): ?>
                                        <span class="self-badge">Anda</span>
                                    <?php endif; ?>
                                    <a href="karyawan.php?mode=edit&id=<?= $k['id_karyawan'] ?>" class="btn-edit-row <?= $isEditing ? 'active' : '' ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit
                                    </a>
                                    <?php if (!$isSelf): ?>
                                    <a href="../process/karyawan_delete.php?id=<?= $k['id_karyawan'] ?>"
                                       class="btn-hapus-row"
                                       onclick="return confirm('Yakin hapus karyawan <?= htmlspecialchars($k['nama_karyawan'], ENT_QUOTES) ?>?')">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                                        Hapus
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- FORM KANAN -->
            <div class="form-card <?= $mode === 'edit' ? 'edit-mode' : '' ?>">
                <div class="form-card-header">
                    <div class="form-card-icon <?= $mode ?>">
                        <?php if ($mode === 'edit'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        <?php else: ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                        <?php endif; ?>
                    </div>
                    <div class="form-card-title-text">
                        <?= $mode === 'edit' ? 'Edit: ' . htmlspecialchars($editData['nama_karyawan']) : 'Tambah Karyawan Baru' ?>
                    </div>
                </div>

                <form action="<?= $mode === 'edit' ? '../process/karyawan_update.php' : '../process/karyawan_insert.php' ?>" method="POST">
                    <?php if ($mode === 'edit'): ?>
                    <input type="hidden" name="id_karyawan" value="<?= htmlspecialchars($editData['id_karyawan']) ?>">
                    <?php endif; ?>

                    <div class="form-card-body">

                        <div class="form-group">
                            <label class="form-label">ID Karyawan</label>
                            <?php if ($mode === 'edit'): ?>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($editData['id_karyawan']) ?>" disabled>
                            <?php else: ?>
                            <input type="text" name="id_karyawan" class="form-control" placeholder="Contoh: K004" required>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama_karyawan" class="form-control"
                                   placeholder="Nama Karyawan"
                                   value="<?= $mode === 'edit' ? htmlspecialchars($editData['nama_karyawan']) : '' ?>"
                                   required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Jabatan</label>
                            <select name="jabatan" class="form-control select" required>
                                <option value="Kasir"   <?= ($mode === 'edit' && $editData['jabatan'] === 'Kasir')   ? 'selected' : '' ?>>Kasir</option>
                                <option value="Manajer" <?= ($mode === 'edit' && $editData['jabatan'] === 'Manajer') ? 'selected' : '' ?>>Manajer</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control"
                                   placeholder="Untuk login"
                                   value="<?= $mode === 'edit' ? htmlspecialchars($editData['username']) : '' ?>"
                                   required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Password<?= $mode === 'edit' ? ' Baru' : '' ?></label>
                            <input type="password" name="password" class="form-control"
                                   placeholder="<?= $mode === 'edit' ? 'Kosongkan jika tidak diubah' : '••••••••' ?>"
                                   <?= $mode === 'tambah' ? 'required' : '' ?>>
                            <div class="form-hint">Akan di-hash sebelum disimpan</div>
                        </div>

                        <?php if ($mode === 'edit'): ?>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="is_active" class="form-control select">
                                <option value="1" <?= $editData['is_active'] ? 'selected' : '' ?>>Aktif</option>
                                <option value="0" <?= !$editData['is_active'] ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>
                        <?php endif; ?>

                    </div>

                    <div class="form-card-footer">
                        <a href="karyawan.php" class="btn-batal">Batal</a>
                        <button type="submit" class="btn-submit">
                            <?= $mode === 'edit' ? 'Simpan Perubahan' : 'Simpan' ?>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
</body>
</html>