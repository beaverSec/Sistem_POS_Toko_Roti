<?php
require_once '../includes/auth.php';
requireRole('Manajer');
require_once '../config/database.php';

$currentPage = 'inventori';

$kategori = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Menu — TokoRoti</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --blue-50:#EBF3FD; --blue-400:#3B8EE8; --blue-500:#1F72D3; --blue-600:#1559A8;
            --gray-50:#F7F8FA; --gray-100:#EDEEF2; --gray-200:#D8DAE2; --gray-400:#9094A4;
            --gray-600:#5A5E6E; --gray-800:#2C2F3C; --white:#FFFFFF;
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
        .nav-item.active svg { opacity:1; }
        .sidebar-bottom { padding:12px; border-top:1px solid rgba(255,255,255,.1); }
        .nav-item.logout { color:rgba(255,255,255,.6); }
        .nav-item.logout:hover { background:rgba(255,255,255,.1); color:var(--white); }

        .main { flex:1; display:flex; flex-direction:column; min-width:0; }
        .topbar { background:var(--white); border-bottom:1px solid var(--gray-100); padding:0 28px; height:60px; display:flex; align-items:center; gap:12px; flex-shrink:0; }
        .topbar-back { display:flex; align-items:center; gap:6px; color:var(--gray-400); text-decoration:none; font-size:13px; font-weight:600; transition:color .15s; }
        .topbar-back:hover { color:var(--blue-500); }
        .topbar-back svg { width:16px; height:16px; }
        .topbar-sep { color:var(--gray-200); font-size:18px; }
        .topbar-title { font-size:16px; font-weight:800; }

        .content { flex:1; overflow-y:auto; padding:32px 28px; display:flex; justify-content:center; }
        .form-card { background:var(--white); border-radius:var(--radius-lg); border:1px solid var(--gray-100); padding:28px 32px; width:100%; max-width:560px; height:fit-content; }
        .form-card-title { font-size:16px; font-weight:800; margin-bottom:6px; }
        .form-card-sub { font-size:13px; color:var(--gray-400); margin-bottom:24px; }
        .form-divider { border:none; border-top:1px solid var(--gray-100); margin:20px 0; }

        .form-group { margin-bottom:16px; }
        .form-label { display:block; font-size:12px; font-weight:700; color:var(--gray-600); text-transform:uppercase; letter-spacing:.4px; margin-bottom:6px; }
        .form-control {
            width:100%; padding:10px 14px;
            border:1.5px solid var(--gray-200); border-radius:var(--radius-md);
            font-family:var(--font); font-size:14px; color:var(--gray-800);
            outline:none; background:var(--gray-50);
            transition:border-color .15s, box-shadow .15s;
        }
        .form-control:focus { border-color:var(--blue-400); box-shadow:0 0 0 3px rgba(59,142,232,.12); background:var(--white); }
        .form-control.select {
            appearance:none;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%239094A4' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat:no-repeat; background-position:right 12px center;
            padding-right:36px; cursor:pointer;
        }
        .form-hint { font-size:11px; color:var(--gray-400); margin-top:4px; }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }

        .form-actions { display:flex; gap:10px; margin-top:24px; }
        .btn-submit {
            flex:1; padding:12px;
            background:var(--blue-500); color:var(--white);
            border:none; border-radius:var(--radius-md);
            font-family:var(--font); font-size:14px; font-weight:700;
            cursor:pointer; transition:background .15s;
            box-shadow:0 3px 10px rgba(31,114,211,.3);
        }
        .btn-submit:hover { background:var(--blue-600); }
        .btn-batal {
            padding:12px 20px;
            background:none; color:var(--gray-600);
            border:1.5px solid var(--gray-200); border-radius:var(--radius-md);
            font-family:var(--font); font-size:14px; font-weight:600;
            text-decoration:none; text-align:center;
            transition:all .15s;
        }
        .btn-batal:hover { border-color:var(--gray-400); color:var(--gray-800); }

        /* Upload area */
        .upload-area {
            border:2px dashed var(--gray-200); border-radius:var(--radius-md);
            padding:24px; text-align:center;
            background:var(--gray-50); cursor:pointer;
            transition:border-color .15s, background .15s;
        }
        .upload-area:hover { border-color:var(--blue-400); background:var(--blue-50); }
        .upload-area svg { width:28px; height:28px; color:var(--gray-400); margin-bottom:8px; }
        .upload-area p { font-size:13px; color:var(--gray-400); font-weight:500; }
        .upload-area input[type="file"] {
            position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%;
        }
        .upload-wrap { position:relative; }
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
        <div class="topbar-title">Tambah Menu Baru</div>
    </div>

    <div class="content">
        <div class="form-card">
            <div class="form-card-title">Tambah Menu Baru</div>
            <div class="form-card-sub">Isi detail menu yang ingin ditambahkan ke daftar produk</div>

            <form action="../process/insert.php" method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label class="form-label" for="id_menu">ID Menu</label>
                    <input type="text" id="id_menu" name="id_menu" class="form-control"
                           placeholder="Contoh: MN08" required>
                    <div class="form-hint">Gunakan format MN + nomor urut, misal MN08</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="nama_menu">Nama Menu</label>
                    <input type="text" id="nama_menu" name="nama_menu" class="form-control"
                           placeholder="Contoh: Croissant Keju" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="id_kategori">Kategori</label>
                    <select id="id_kategori" name="id_kategori" class="form-control select" required>
                        <option value="" disabled selected>Pilih kategori...</option>
                        <?php foreach ($kategori as $k): ?>
                        <option value="<?= $k['id_kategori'] ?>">
                            <?= htmlspecialchars($k['nama_kategori']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="stok">Stok Awal</label>
                        <input type="number" id="stok" name="stok" class="form-control"
                               placeholder="0" min="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="harga">Harga (Rp)</label>
                        <input type="number" id="harga" name="harga" class="form-control"
                               placeholder="0" min="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Foto Menu</label>
                    <div class="upload-wrap">
                        <div class="upload-area">
                            <input type="file" name="gambar" accept="image/*">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            <p>Klik untuk upload foto menu</p>
                        </div>
                    </div>
                    <div class="form-hint">Format JPG/PNG, maks. 2MB. Opsional — jika kosong akan pakai gambar default.</div>
                </div>

                <div class="form-actions">
                    <a href="inventori.php" class="btn-batal">Batal</a>
                    <button type="submit" class="btn-submit">
                        Simpan Menu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
