<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

$menu = $conn->query(
    "SELECT m.id_menu, m.nama_menu, m.stok, m.harga, m.gambar, k.nama_kategori, k.id_kategori, k.icon_kategori
     FROM menu m
     JOIN kategori k ON m.id_kategori = k.id_kategori
     WHERE m.is_deleted = 0 AND m.stok > 0
     ORDER BY k.nama_kategori, m.nama_menu"
)->fetchAll(PDO::FETCH_ASSOC);

$kategori = $conn->query(
    "SELECT DISTINCT k.id_kategori, k.nama_kategori, k.icon_kategori
     FROM kategori k
     JOIN menu m ON m.id_kategori = k.id_kategori
     WHERE m.is_deleted = 0 AND m.stok > 0
     ORDER BY k.nama_kategori"
)->fetchAll(PDO::FETCH_ASSOC);

$pesan = $_GET['pesan'] ?? '';

// Tangkap parameter sukses dari transaksi_process.php
$sukses_id = ($_GET['sukses'] ?? '') === '1' ? ($_GET['id'] ?? '') : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir — TokoRoti</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            --gray-50:  #F7F8FA;
            --gray-100: #EDEEF2;
            --gray-200: #D8DAE2;
            --gray-400: #9094A4;
            --gray-600: #5A5E6E;
            --gray-800: #2C2F3C;
            --white:    #FFFFFF;
            --red-500:  #E24B4A;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --shadow-md: 0 4px 12px rgba(0,0,0,.08), 0 2px 4px rgba(0,0,0,.06);
            --font: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            font-family: var(--font);
            background: #F0F4FB;
            color: var(--gray-800);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* TOPBAR */
        .topbar {
            background: var(--blue-500);
            color: var(--white);
            height: 56px;
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 12px;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(21,89,168,.25);
        }
        .topbar-logo { font-size: 18px; font-weight: 700; letter-spacing: -.3px; flex: 1; }
        .topbar-logo span { opacity: .65; font-weight: 400; }
        .topbar-user {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; font-weight: 500;
            background: rgba(255,255,255,.15);
            padding: 6px 12px; border-radius: 100px;
        }
        .topbar-avatar {
            width: 26px; height: 26px; border-radius: 50%;
            background: rgba(255,255,255,.3);
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700;
        }
        .topbar-logout {
            display: flex; align-items: center; gap: 6px;
            color: var(--white); text-decoration: none;
            font-size: 13px; font-weight: 600;
            background: rgba(255,255,255,.18);
            padding: 6px 14px; border-radius: 100px;
            transition: background .15s;
        }
        .topbar-logout:hover { background: rgba(255,255,255,.28); }
        .topbar-logout svg { width: 15px; height: 15px; }

        /* TOAST */
        .toast {
            position: fixed; top: 68px; left: 50%; transform: translateX(-50%);
            padding: 10px 20px; border-radius: var(--radius-md);
            font-size: 13px; font-weight: 600; z-index: 999;
            animation: fadeSlide .3s ease;
        }
        .toast.sukses { background: #D1FAE5; color: #065F46; border: 1px solid #6EE7B7; }
        .toast.gagal  { background: #FEE2E2; color: #7F1D1D; border: 1px solid #FCA5A5; }
        @keyframes fadeSlide { from { opacity:0; transform:translateX(-50%) translateY(-8px); } to { opacity:1; transform:translateX(-50%) translateY(0); } }

        /* MAIN LAYOUT */
        .main {
            display: grid;
            grid-template-columns: 1fr 360px;
            flex: 1;
            overflow: hidden;
        }

        /* LEFT MENU PANEL */
        .menu-panel {
            display: flex; flex-direction: column;
            overflow: hidden; padding: 20px; gap: 14px;
        }
        .panel-header {
            display: flex; align-items: center;
            justify-content: space-between; flex-shrink: 0;
        }
        .panel-title { font-size: 15px; font-weight: 700; }
        .search-wrap { position: relative; flex: 1; max-width: 260px; }
        .search-icon {
            position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
            color: var(--gray-400); pointer-events: none;
        }
        .search-wrap input {
            width: 100%; padding: 8px 12px 8px 34px;
            border: 1.5px solid var(--gray-200); border-radius: var(--radius-md);
            background: var(--white); font-family: var(--font);
            font-size: 13px; color: var(--gray-800); outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .search-wrap input:focus { border-color: var(--blue-400); box-shadow: 0 0 0 3px rgba(59,142,232,.15); }
        .search-wrap input::placeholder { color: var(--gray-400); }

        /* Kategori chips */
        .kategori-bar {
            display: flex; gap: 8px; flex-wrap: nowrap;
            overflow-x: auto; flex-shrink: 0;
            scrollbar-width: none;
        }
        .kategori-bar::-webkit-scrollbar { display: none; }
        .kat-chip {
            display: flex; align-items: center; gap: 6px;
            padding: 7px 14px; border-radius: 100px;
            border: 1.5px solid var(--gray-200); background: var(--white);
            font-size: 12px; font-weight: 600; color: var(--gray-600);
            cursor: pointer; white-space: nowrap;
            transition: all .15s; user-select: none; flex-shrink: 0;
        }
        .kat-chip:hover { border-color: var(--blue-400); color: var(--blue-500); }
        .kat-chip.active {
            background: var(--blue-500); border-color: var(--blue-500);
            color: var(--white); box-shadow: 0 2px 8px rgba(31,114,211,.3);
        }

        /* Menu grid */
        .menu-grid-wrap { flex: 1; overflow-y: auto; padding-right: 4px; }
        .menu-grid-wrap::-webkit-scrollbar { width: 4px; }
        .menu-grid-wrap::-webkit-scrollbar-thumb { background: var(--gray-200); border-radius: 4px; }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(148px, 1fr));
            gap: 12px;
        }
        .menu-card {
            background: var(--white); border-radius: var(--radius-lg);
            border: 1.5px solid var(--gray-100); overflow: hidden;
            cursor: pointer; position: relative;
            transition: transform .15s, box-shadow .15s, border-color .15s;
        }
        .menu-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); border-color: var(--blue-200); }
        .menu-card:active { transform: translateY(0); }
        .menu-card-img { width: 100%; aspect-ratio: 4/3; object-fit: cover; background: var(--gray-100); display: block; }
        .menu-card-img-placeholder {
            width: 100%; aspect-ratio: 4/3;
            background: linear-gradient(135deg, var(--blue-50), var(--gray-100));
            display: flex; align-items: center; justify-content: center; font-size: 32px;
        }
        .menu-card-body { padding: 10px 12px 12px; }
        .menu-card-nama { font-size: 13px; font-weight: 700; color: var(--gray-800); margin-bottom: 2px; line-height: 1.3; }
        .menu-card-kat { font-size: 11px; color: var(--gray-400); margin-bottom: 6px; }
        .menu-card-footer { display: flex; align-items: center; justify-content: space-between; }
        .menu-card-harga { font-size: 13px; font-weight: 700; color: var(--blue-500); }
        .menu-card-stok { font-size: 10px; font-weight: 600; padding: 2px 7px; border-radius: 100px; background: var(--blue-50); color: var(--blue-600); }
        .menu-card-stok.low { background: #FEF3C7; color: #92400E; }
        .menu-card-add {
            position: absolute; bottom: 10px; right: 10px;
            width: 26px; height: 26px; background: var(--blue-500);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            color: white; box-shadow: 0 2px 6px rgba(31,114,211,.35);
            opacity: 0; transition: opacity .15s;
        }
        .menu-card:hover .menu-card-add { opacity: 1; }
        .menu-card-add svg { width: 14px; height: 14px; }
        .no-menu { text-align: center; padding: 48px 16px; color: var(--gray-400); font-size: 14px; }

        /* RIGHT KERANJANG */
        .keranjang-panel {
            background: var(--white); display: flex;
            flex-direction: column; border-left: 1px solid var(--gray-100); overflow: hidden;
        }
        .keranjang-header { padding: 20px 20px 14px; border-bottom: 1px solid var(--gray-100); flex-shrink: 0; }
        .keranjang-title-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 2px; }
        .keranjang-title { font-size: 15px; font-weight: 700; }
        .keranjang-badge {
            background: var(--blue-500); color: white;
            font-size: 11px; font-weight: 700;
            padding: 2px 9px; border-radius: 100px; display: none;
        }
        .keranjang-sub { font-size: 12px; color: var(--gray-400); }
        .keranjang-body { flex: 1; overflow-y: auto; padding: 12px 20px; }
        .keranjang-body::-webkit-scrollbar { width: 3px; }
        .keranjang-body::-webkit-scrollbar-thumb { background: var(--gray-200); border-radius: 3px; }
        .keranjang-empty {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            height: 100%; gap: 12px; color: var(--gray-400);
        }
        .keranjang-empty svg { width: 48px; height: 48px; opacity: .35; }
        .keranjang-empty p { font-size: 13px; }

        /* Items */
        .item-keranjang {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 0; border-bottom: 1px solid var(--gray-100);
            animation: slideIn .2s ease;
        }
        @keyframes slideIn { from { opacity:0; transform:translateX(8px); } to { opacity:1; } }
        .item-keranjang:last-child { border-bottom: none; }
        .item-info { flex: 1; min-width: 0; }
        .item-nama { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .item-harga { font-size: 12px; color: var(--gray-400); margin-top: 1px; }
        .item-qty-ctrl { display: flex; align-items: center; gap: 6px; }
        .qty-btn {
            width: 26px; height: 26px; border-radius: 50%;
            border: 1.5px solid var(--gray-200); background: var(--white);
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            color: var(--gray-600); font-size: 15px; font-weight: 700; line-height: 1;
            transition: all .12s; flex-shrink: 0;
        }
        .qty-btn:hover { border-color: var(--blue-400); color: var(--blue-500); background: var(--blue-50); }
        .qty-btn.minus:hover { border-color: var(--red-500); color: var(--red-500); background: #FEE2E2; }
        .qty-num { font-size: 13px; font-weight: 700; min-width: 20px; text-align: center; }
        .item-subtotal { font-size: 13px; font-weight: 700; color: var(--blue-600); text-align: right; min-width: 72px; }
        .item-hapus { background: none; border: none; cursor: pointer; color: var(--gray-400); padding: 4px; border-radius: var(--radius-sm); transition: color .12s; }
        .item-hapus:hover { color: var(--red-500); }
        .item-hapus svg { width: 14px; height: 14px; display: block; }

        /* Footer transaksi */
        .transaksi-footer { border-top: 1px solid var(--gray-100); padding: 16px 20px 20px; flex-shrink: 0; }
        .summary-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .summary-label { font-size: 13px; color: var(--gray-600); }
        .summary-val { font-size: 13px; font-weight: 600; }
        .summary-total { font-size: 15px; font-weight: 700; }
        .summary-total-val { font-size: 17px; font-weight: 800; color: var(--blue-600); }
        .divider { border: none; border-top: 1px solid var(--gray-100); margin: 10px 0; }
        .input-group { margin-bottom: 10px; }
        .input-label { display: block; font-size: 12px; font-weight: 600; color: var(--gray-600); margin-bottom: 5px; }
        .input-field {
            width: 100%; padding: 9px 12px;
            border: 1.5px solid var(--gray-200); border-radius: var(--radius-md);
            font-family: var(--font); font-size: 13px; color: var(--gray-800);
            outline: none; background: var(--gray-50);
            transition: border-color .15s, box-shadow .15s;
        }
        .input-field:focus { border-color: var(--blue-400); box-shadow: 0 0 0 3px rgba(59,142,232,.12); background: var(--white); }
        .kembalian-row {
            display: flex; justify-content: space-between; align-items: center;
            background: var(--blue-50); border-radius: var(--radius-md);
            padding: 8px 12px; margin: 10px 0;
        }
        .kembalian-label { font-size: 12px; font-weight: 600; color: var(--blue-600); }
        .kembalian-val { font-size: 14px; font-weight: 800; color: var(--blue-600); }
        .kembalian-row.minus { background: #FEE2E2; }
        .kembalian-row.minus .kembalian-label,
        .kembalian-row.minus .kembalian-val { color: var(--red-500); }
        .metode-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; margin-bottom: 14px; }
        .metode-btn {
            padding: 7px 4px; border: 1.5px solid var(--gray-200); border-radius: var(--radius-md);
            background: var(--white); font-family: var(--font);
            font-size: 12px; font-weight: 600; color: var(--gray-600);
            cursor: pointer; text-align: center; transition: all .15s;
        }
        .metode-btn:hover { border-color: var(--blue-400); color: var(--blue-500); }
        .metode-btn.active { background: var(--blue-50); border-color: var(--blue-500); color: var(--blue-600); }
        .metode-icon { font-size: 16px; display: block; margin-bottom: 2px; }
        .btn-proses {
            width: 100%; padding: 13px; background: var(--blue-500); color: var(--white);
            border: none; border-radius: var(--radius-md);
            font-family: var(--font); font-size: 14px; font-weight: 700;
            cursor: pointer; transition: background .15s, transform .1s, box-shadow .15s;
            box-shadow: 0 3px 10px rgba(31,114,211,.35);
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-proses:hover { background: var(--blue-600); box-shadow: 0 4px 14px rgba(31,114,211,.45); }
        .btn-proses:active { transform: scale(.98); }
        .btn-proses svg { width: 17px; height: 17px; }
        .btn-reset {
            width: 100%; padding: 9px; background: none;
            border: 1.5px solid var(--gray-200); border-radius: var(--radius-md);
            font-family: var(--font); font-size: 13px; font-weight: 600;
            color: var(--gray-600); cursor: pointer; margin-top: 8px; transition: all .15s;
        }
        .btn-reset:hover { border-color: var(--red-500); color: var(--red-500); background: #FEF2F2; }

        #form-transaksi { display: none; }
    </style>
</head>
<body>

<div class="topbar">
    <div class="topbar-logo">TokoRoti <span>/ Kasir</span></div>
    <div class="topbar-user">
        <div class="topbar-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 2)) ?></div>
        <?= htmlspecialchars($_SESSION['nama']) ?>
    </div>
    <a href="../process/logout.php" class="topbar-logout">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
        </svg>
        Logout
    </a>
</div>

<?php if ($pesan === 'sukses'): ?>
<div class="toast sukses">✓ Transaksi berhasil disimpan!</div>
<?php elseif ($pesan === 'gagal'): ?>
<div class="toast gagal">✕ Transaksi gagal. Cek kembali stok dan input.</div>
<?php endif; ?>

<div class="main">

    <!-- LEFT: MENU -->
    <div class="menu-panel">
        <div class="panel-header">
            <div class="panel-title">Menu</div>
            <div class="search-wrap">
                <svg class="search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text" id="search-menu" placeholder="Cari menu..." oninput="filterMenu()">
            </div>
        </div>

        <div class="kategori-bar">
            <div class="kat-chip active" data-kat="semua" onclick="filterKategori('semua', this)">
                🍽️ Semua
            </div>
            <?php foreach ($kategori as $k): ?>
            <div class="kat-chip" data-kat="<?= $k['id_kategori'] ?>" onclick="filterKategori('<?= $k['id_kategori'] ?>', this)">
                <?= $k['icon_kategori'] ?? '🏷️' ?> <?= htmlspecialchars($k['nama_kategori']) ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="menu-grid-wrap">
            <div class="menu-grid" id="menu-grid">
                <?php foreach ($menu as $m): ?>
                <div class="menu-card"
                     data-id="<?= $m['id_menu'] ?>"
                     data-nama="<?= htmlspecialchars($m['nama_menu'], ENT_QUOTES) ?>"
                     data-harga="<?= $m['harga'] ?>"
                     data-stok="<?= $m['stok'] ?>"
                     data-kategori="<?= $m['id_kategori'] ?>"
                     onclick="tambahKeKeranjang('<?= $m['id_menu'] ?>', '<?= addslashes($m['nama_menu']) ?>', <?= $m['harga'] ?>, <?= $m['stok'] ?>)">

                    <?php if (!empty($m['gambar'])): ?>
                    <img class="menu-card-img" src="assets/<?= htmlspecialchars($m['gambar'] ?? 'default.png') ?>" alt="<?= htmlspecialchars($m['nama_menu']) ?>">
                    <?php else: ?>
                    <div class="menu-card-img-placeholder">🍞</div>
                    <?php endif; ?>

                    <div class="menu-card-body">
                        <div class="menu-card-nama"><?= htmlspecialchars($m['nama_menu']) ?></div>
                        <div class="menu-card-kat"><?= htmlspecialchars($m['nama_kategori']) ?></div>
                        <div class="menu-card-footer">
                            <div class="menu-card-harga">Rp <?= number_format($m['harga'], 0, ',', '.') ?></div>
                            <div class="menu-card-stok <?= $m['stok'] <= 5 ? 'low' : '' ?>">Stok <?= $m['stok'] ?></div>
                        </div>
                    </div>
                    <div class="menu-card-add" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="no-menu" id="no-menu" style="display:none;">Tidak ada menu ditemukan.</div>
        </div>
    </div>

    <!-- RIGHT: KERANJANG -->
    <div class="keranjang-panel">
        <div class="keranjang-header">
            <div class="keranjang-title-row">
                <div class="keranjang-title">Detail Transaksi</div>
                <div class="keranjang-badge" id="keranjang-badge">0</div>
            </div>
            <div class="keranjang-sub" id="keranjang-sub">Belum ada item dipilih</div>
        </div>

        <div class="keranjang-body" id="keranjang-body">
            <div class="keranjang-empty" id="keranjang-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>
                </svg>
                <p>Keranjang masih kosong</p>
            </div>
            <div id="isi-keranjang"></div>
        </div>

        <div class="transaksi-footer" id="transaksi-footer" style="display:none;">
            <div class="summary-row">
                <span class="summary-label">Sub Total</span>
                <span class="summary-val">Rp <span id="subtotal-display">0</span></span>
            </div>
            <div class="summary-row">
                <span class="summary-total">Total</span>
                <span class="summary-total-val">Rp <span id="total-display">0</span></span>
            </div>
            <hr class="divider">

            <div class="input-group" id="wrap-uang-bayar">
                <label class="input-label" for="uang-bayar">Uang Bayar</label>
                <input type="number" id="uang-bayar" class="input-field" placeholder="Masukkan nominal..." min="0" oninput="hitungKembalian()">
            </div>

            <div class="kembalian-row" id="kembalian-row">
                <span class="kembalian-label">Kembalian</span>
                <span class="kembalian-val" id="kembalian-display">—</span>
            </div>

            <div class="input-group" style="margin-bottom:6px;">
                <label class="input-label">Metode Pembayaran</label>
                <div class="metode-grid">
                    <button class="metode-btn active" onclick="setMetode('Cash', this)"><span class="metode-icon">💵</span>Cash</button>
                    <button class="metode-btn" onclick="setMetode('QRIS', this)"><span class="metode-icon">📱</span>QRIS</button>
                    <button class="metode-btn" onclick="setMetode('Transfer', this)"><span class="metode-icon">🏦</span>Transfer</button>
                </div>
            </div>

            <button class="btn-proses" onclick="prosesTransaksi()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Proses Transaksi
            </button>
            <button class="btn-reset" onclick="resetKeranjang()">↺ Reset Keranjang</button>
        </div>
    </div>
</div>

<form id="form-transaksi" action="../process/transaksi_process.php" method="POST">
    <input type="hidden" name="total_bayar"  id="input-total">
    <input type="hidden" name="uang_bayar"   id="input-uang">
    <input type="hidden" name="kembalian"    id="input-kembalian">
    <input type="hidden" name="metode_bayar" id="input-metode" value="Cash">
    <input type="hidden" name="items"        id="input-items">
</form>

<script>
let keranjang = {};
let stokTersedia = {};
let metodeBayar = 'Cash';

function tambahKeKeranjang(id, nama, harga, stok) {
    if (!stokTersedia[id]) stokTersedia[id] = stok;
    if (keranjang[id]) {
        if (keranjang[id].qty >= stokTersedia[id]) { showToast('Stok tidak mencukupi!', 'gagal'); return; }
        keranjang[id].qty++;
    } else {
        keranjang[id] = { nama, harga, qty: 1 };
    }
    renderKeranjang();
}

function ubahQty(id, delta) {
    if (!keranjang[id]) return;
    keranjang[id].qty += delta;
    if (keranjang[id].qty <= 0) delete keranjang[id];
    renderKeranjang();
}

function renderKeranjang() {
    const isi    = document.getElementById('isi-keranjang');
    const empty  = document.getElementById('keranjang-empty');
    const footer = document.getElementById('transaksi-footer');
    const badge  = document.getElementById('keranjang-badge');
    const sub    = document.getElementById('keranjang-sub');

    isi.innerHTML = '';
    let total = 0, totalQty = 0;
    const keys = Object.keys(keranjang);

    if (keys.length === 0) {
        empty.style.display = 'flex'; footer.style.display = 'none';
        badge.style.display = 'none'; sub.textContent = 'Belum ada item dipilih';
        return;
    }

    empty.style.display = 'none'; footer.style.display = 'block';

    keys.forEach(id => {
        const item = keranjang[id];
        const subtotal = item.harga * item.qty;
        total += subtotal; totalQty += item.qty;
        const row = document.createElement('div');
        row.className = 'item-keranjang';
        row.innerHTML = `
            <div class="item-info">
                <div class="item-nama">${item.nama}</div>
                <div class="item-harga">Rp ${item.harga.toLocaleString('id-ID')}</div>
            </div>
            <div class="item-qty-ctrl">
                <button class="qty-btn minus" onclick="ubahQty('${id}', -1)">−</button>
                <span class="qty-num">${item.qty}</span>
                <button class="qty-btn" onclick="ubahQty('${id}', 1)">+</button>
            </div>
            <div class="item-subtotal">Rp ${subtotal.toLocaleString('id-ID')}</div>
            <button class="item-hapus" onclick="ubahQty('${id}', -${item.qty})" title="Hapus">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                    <path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
                </svg>
            </button>`;
        isi.appendChild(row);
    });

    badge.textContent = totalQty; badge.style.display = 'inline-block';
    sub.textContent = `${keys.length} jenis item · ${totalQty} pcs`;
    const fmt = n => n.toLocaleString('id-ID');
    document.getElementById('subtotal-display').textContent = fmt(total);
    document.getElementById('total-display').textContent = fmt(total);
    hitungKembalian();
}

function hitungKembalian() {
    const total = parseInt((document.getElementById('total-display').textContent || '0').replace(/\./g, '')) || 0;
    const uang  = parseInt(document.getElementById('uang-bayar').value) || 0;
    const kembalian = uang - total;
    const rowEl = document.getElementById('kembalian-row');
    const valEl = document.getElementById('kembalian-display');
    if (metodeBayar !== 'Cash') { valEl.textContent = '—'; rowEl.classList.remove('minus'); return; }
    if (!uang) { valEl.textContent = '—'; rowEl.classList.remove('minus'); return; }
    rowEl.classList.toggle('minus', kembalian < 0);
    valEl.textContent = 'Rp ' + Math.abs(kembalian).toLocaleString('id-ID');
}

function setMetode(val, btn) {
    metodeBayar = val;
    document.getElementById('input-metode').value = val;
    document.querySelectorAll('.metode-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const showCash = val === 'Cash';
    document.getElementById('wrap-uang-bayar').style.display = showCash ? '' : 'none';
    document.getElementById('kembalian-row').style.display   = showCash ? '' : 'none';
}

function prosesTransaksi() {
    const total = parseInt((document.getElementById('total-display').textContent || '0').replace(/\./g, '')) || 0;
    const uang  = parseInt(document.getElementById('uang-bayar').value) || 0;
    if (!Object.keys(keranjang).length) { showToast('Keranjang kosong!', 'gagal'); return; }
    if (metodeBayar === 'Cash' && uang < total) { showToast('Uang bayar kurang!', 'gagal'); return; }
    document.getElementById('input-total').value     = total;
    document.getElementById('input-uang').value      = metodeBayar === 'Cash' ? uang : total;
    document.getElementById('input-kembalian').value = metodeBayar === 'Cash' ? uang - total : 0;
    document.getElementById('input-metode').value    = metodeBayar;
    document.getElementById('input-items').value     = JSON.stringify(keranjang);
    document.getElementById('form-transaksi').submit();
}

function resetKeranjang() {
    keranjang = {}; stokTersedia = {};
    document.getElementById('uang-bayar').value = '';
    renderKeranjang();
}

/* Filter */
let activeKat = 'semua';
function filterKategori(id, el) {
    activeKat = id;
    document.querySelectorAll('.kat-chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    applyFilter();
}
function filterMenu() { applyFilter(); }
function applyFilter() {
    const q = document.getElementById('search-menu').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.menu-card');
    let visible = 0;
    cards.forEach(card => {
        const matchKat = activeKat === 'semua' || card.dataset.kategori === activeKat;
        const matchQ   = !q || card.dataset.nama.toLowerCase().includes(q);
        card.style.display = (matchKat && matchQ) ? '' : 'none';
        if (matchKat && matchQ) visible++;
    });
    document.getElementById('no-menu').style.display = visible === 0 ? 'block' : 'none';
}

/* Toast */
function showToast(msg, type) {
    const el = document.createElement('div');
    el.className = 'toast ' + type;
    el.textContent = (type === 'sukses' ? '✓ ' : '✕ ') + msg;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 2800);
}

const phpToast = document.querySelector('.toast');
if (phpToast) setTimeout(() => phpToast.remove(), 3000);
</script>
</body>
</html>
