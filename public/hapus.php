<?php
require_once '../includes/auth.php';
requireRole('Manajer');
require_once '../config/database.php';

$id = $_GET['id'] ?? '';
if (!$id) { header("Location: inventori.php"); exit; }

$stmt = $conn->prepare("SELECT nama_menu, gambar FROM menu WHERE id_menu = :id AND is_deleted = 0");
$stmt->execute([':id' => $id]);
$menu = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$menu) { header("Location: inventori.php"); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Menu — TokoRoti</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --blue-500:#1F72D3; --blue-600:#1559A8;
            --gray-50:#F7F8FA; --gray-100:#EDEEF2; --gray-200:#D8DAE2; --gray-400:#9094A4;
            --gray-600:#5A5E6E; --gray-800:#2C2F3C; --white:#FFFFFF;
            --red-50:#FEF2F2; --red-100:#FEE2E2; --red-500:#EF4444; --red-600:#DC2626;
            --sidebar-w:220px; --radius-md:12px; --radius-lg:16px;
            --font:'Plus Jakarta Sans',sans-serif;
        }
        body { font-family:var(--font); background:#F0F4FB; color:var(--gray-800); min-height:100vh; display:flex; }
        .sidebar { width:var(--sidebar-w); background:var(--blue-500); min-height:100vh; display:flex; flex-direction:column; flex-shrink:0; position:sticky; top:0; height:100vh; }
        .sidebar-brand { padding:24px 20px 20px; border-bottom:1px solid rgba(255,255,255,.12); }
        .sidebar-brand-name { font-size:20px; font-weight:800; color:var(--white); letter-spacing:-.3px; }
        .sidebar-brand-role { font-size:11px; font-weight:500; color:rgba(255,255,255,.55); margin-top:2px; text-transform:uppercase; letter-spacing:.5px; }
        .sidebar-nav { flex:1; padding:12px; display:flex; flex-direction:column; gap:2px; }
        .nav-item { display:flex; align-items:center; gap:12px; padding:11px 12px; border-radius:var(--radius-md); color:rgba(255,255,255,.75); text-decoration:none; font-size:14px; font-weight:600; transition:all .15s; }
        .nav-item:hover { background:rgba(255,255,255,.12); color:var(--white); }
        .nav-item.active { background:rgba(255,255,255,.2); color:var(--white); }
        .nav-item svg { width:18px; height:18px; flex-shrink:0; opacity:.85; }
        .sidebar-bottom { padding:12px; border-top:1px solid rgba(255,255,255,.1); }
        .nav-item.logout { color:rgba(255,255,255,.6); }
        .main { flex:1; display:flex; flex-direction:column; min-width:0; }
        .topbar { background:var(--white); border-bottom:1px solid var(--gray-100); padding:0 28px; height:60px; display:flex; align-items:center; gap:12px; flex-shrink:0; }
        .topbar-back { display:flex; align-items:center; gap:6px; color:var(--gray-400); text-decoration:none; font-size:13px; font-weight:600; transition:color .15s; }
        .topbar-back:hover { color:var(--blue-500); }
        .topbar-back svg { width:16px; height:16px; }
        .topbar-sep { color:var(--gray-200); font-size:18px; }
        .topbar-title { font-size:16px; font-weight:800; }
        .content { flex:1; display:flex; align-items:center; justify-content:center; padding:32px 28px; }

        .confirm-card {
            background:var(--white); border-radius:var(--radius-lg);
            border:1px solid var(--gray-100); padding:32px;
            width:100%; max-width:440px; text-align:center;
        }
        .confirm-icon {
            width:64px; height:64px; border-radius:50%;
            background:var(--red-100); display:flex; align-items:center;
            justify-content:center; margin:0 auto 20px;
        }
        .confirm-icon svg { width:28px; height:28px; color:var(--red-500); }
        .confirm-title { font-size:18px; font-weight:800; margin-bottom:8px; }
        .confirm-desc { font-size:14px; color:var(--gray-600); line-height:1.6; margin-bottom:8px; }
        .confirm-menu-name {
            display:inline-block; font-size:14px; font-weight:700;
            background:var(--red-50); color:var(--red-600);
            padding:6px 14px; border-radius:8px; margin:8px 0 20px;
        }
        .confirm-warning {
            font-size:12px; color:var(--gray-400);
            background:var(--gray-50); border-radius:8px;
            padding:10px 14px; margin-bottom:24px; text-align:left;
        }
        .confirm-warning strong { color:var(--gray-600); }
        .confirm-actions { display:flex; gap:10px; }
        .btn-batal {
            flex:1; padding:12px;
            background:none; color:var(--gray-600);
            border:1.5px solid var(--gray-200); border-radius:var(--radius-md);
            font-family:var(--font); font-size:14px; font-weight:600;
            text-decoration:none; text-align:center; transition:all .15s;
        }
        .btn-batal:hover { border-color:var(--gray-400); color:var(--gray-800); }
        .btn-hapus {
            flex:1; padding:12px;
            background:var(--red-500); color:var(--white);
            border:none; border-radius:var(--radius-md);
            font-family:var(--font); font-size:14px; font-weight:700;
            text-decoration:none; text-align:center;
            cursor:pointer; transition:background .15s;
        }
        .btn-hapus:hover { background:var(--red-600); }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-name">TokoRoti</div>
        <div class="sidebar-brand-role">Manajer</div>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a href="inventori.php" class="nav-item active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
            Inventori
        </a>
        <a href="laporan.php" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Laporan
        </a>
        <a href="karyawan.php" class="nav-item">
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

<div class="main">
    <div class="topbar">
        <a href="inventori.php" class="topbar-back">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            Inventori
        </a>
        <span class="topbar-sep">/</span>
        <div class="topbar-title">Hapus Menu</div>
    </div>

    <div class="content">
        <div class="confirm-card">
            <div class="confirm-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
                </svg>
            </div>
            <div class="confirm-title">Hapus Menu?</div>
            <div class="confirm-desc">Kamu akan menghapus menu berikut dari daftar inventori:</div>
            <div class="confirm-menu-name"><?= htmlspecialchars($menu['nama_menu']) ?></div>
            <div class="confirm-warning">
                <strong>Catatan:</strong> Menu akan disembunyikan dari daftar, namun data histori transaksi yang sudah ada tetap tersimpan dan tidak akan hilang.
            </div>
            <div class="confirm-actions">
                <a href="inventori.php" class="btn-batal">Batal</a>
                <a href="../process/delete.php?id=<?= urlencode($id) ?>" class="btn-hapus">Ya, Hapus</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>