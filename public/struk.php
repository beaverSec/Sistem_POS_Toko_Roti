<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

$id = $_GET['id'] ?? '';
if (!$id) {
    header("Location: kasir.php");
    exit;
}

// Ambil data transaksi
$stmt = $conn->prepare(
    "SELECT t.*, k.nama_karyawan 
     FROM transaksi t 
     JOIN karyawan k ON t.id_karyawan = k.id_karyawan
     WHERE t.id_transaksi = :id"
);
$stmt->execute([':id' => $id]);
$transaksi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaksi) {
    header("Location: kasir.php");
    exit;
}

// Ambil detail item menggunakan view detailstruk
$stmtDetail = $conn->prepare("SELECT * FROM detailstruk WHERE id_transaksi = :id");
$stmtDetail->execute([':id' => $id]);
$items = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk - <?= htmlspecialchars($transaksi['id_transaksi']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue-50:  #EBF3FD;
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
            --font: 'Plus Jakarta Sans', sans-serif;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font);
            background: #F0F4FB;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }

        .struk-wrap {
            width: 100%;
            max-width: 420px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Bagian Atas: Status Sukses (Hanya tampil di layar) */
        .page-header {
            text-align: center;
        }
        .page-header .sukses-icon {
            width: 56px; height: 56px;
            background: var(--green-50);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 12px;
        }
        .page-header .sukses-icon svg { width: 28px; height: 28px; color: var(--green-600); }
        .page-header h1 { font-size: 18px; font-weight: 800; color: var(--gray-800); }
        .page-header p  { font-size: 13px; color: var(--gray-400); margin-top: 4px; }

        /* Kartu Struk */
        .struk-card {
            background: var(--white);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
        }

        .struk-header {
            background: var(--blue-500);
            padding: 24px;
            text-align: center;
            color: white;
        }
        .struk-header .toko-nama  { font-size: 20px; font-weight: 800; letter-spacing: -.3px; }
        .struk-header .toko-tagline { font-size: 12px; opacity: .75; margin-top: 2px; }
        .struk-header .struk-id {
            display: inline-block;
            margin-top: 10px;
            background: rgba(255,255,255,.2);
            border-radius: 100px;
            padding: 4px 14px;
            font-size: 12px; font-weight: 700;
        }

        .struk-info {
            padding: 18px 24px;
            background: var(--gray-50);
            border-bottom: 1px dashed var(--gray-200);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .info-item { font-size: 12px; }
        .info-label { color: var(--gray-400); font-weight: 600; text-transform: uppercase; letter-spacing: .3px; margin-bottom: 2px; }
        .info-val   { color: var(--gray-800); font-weight: 700; }

        .struk-items { padding: 16px 24px; }
        .item-header {
            display: flex; justify-content: space-between;
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            color: var(--gray-400);
            padding-bottom: 8px;
            border-bottom: 1px solid var(--gray-100);
            margin-bottom: 8px;
        }
        .struk-item-row {
            display: flex; align-items: baseline;
            gap: 8px; padding: 8px 0;
            border-bottom: 1px dashed var(--gray-100);
        }
        .struk-item-row:last-child { border-bottom: none; }
        .item-nama { flex: 1; font-size: 13px; font-weight: 600; color: var(--gray-800); }
        .item-qty  { font-size: 11px; color: var(--gray-400); min-width: 32px; text-align: center; }
        .item-sub  { font-size: 13px; font-weight: 700; color: var(--gray-800); text-align: right; min-width: 80px; }

        .struk-summary {
            padding: 16px 24px;
            background: var(--gray-50);
            border-top: 1px dashed var(--gray-200);
            display: flex; flex-direction: column; gap: 6px;
        }
        .summary-row { display: flex; justify-content: space-between; font-size: 13px; }
        .summary-row span:first-child { color: var(--gray-600); }
        .summary-row.total {
            padding-top: 10px;
            border-top: 1.5px solid var(--gray-200);
            margin-top: 4px;
        }
        .summary-row.total span:first-child { font-size: 14px; font-weight: 700; color: var(--gray-800); }
        .summary-row.total span:last-child  { font-size: 18px; font-weight: 800; color: var(--blue-600); }

        .struk-footer {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: var(--gray-400);
            border-top: 1px solid var(--gray-100);
        }
        .struk-footer strong { display: block; font-size: 13px; color: var(--gray-600); margin-bottom: 4px; }

        /* Tombol Aksi (Hanya tampil di layar) */
        .struk-actions {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 12px;
        }
        .btn-cetak {
            padding: 14px; border-radius: 12px; border: 1.5px solid var(--blue-500);
            background: var(--blue-50); color: var(--blue-600); font-family: var(--font);
            font-size: 14px; font-weight: 700; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: all .2s;
        }
        .btn-cetak:hover { background: var(--blue-500); color: white; }
        .btn-baru {
            padding: 14px; border-radius: 12px; border: none;
            background: var(--blue-500); color: white; font-family: var(--font);
            font-size: 14px; font-weight: 700; cursor: pointer; text-decoration: none;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 4px 12px rgba(31,114,211,0.2);
        }
        .btn-baru:hover { background: var(--blue-600); }
        .btn-cetak svg, .btn-baru svg { width: 18px; height: 18px; }

        /* Pengaturan Cetak */
        @media print {
            body { background: white; padding: 0; justify-content: flex-start; }
            .no-print, .struk-actions, .page-header { display: none !important; }
            .struk-card { box-shadow: none; border-radius: 0; width: 100%; }
            .struk-wrap { max-width: 100%; gap: 0; }
        }
    </style>
</head>
<body>

<div class="struk-wrap">
    <div class="page-header no-print">
        <div class="sukses-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <h1>Transaksi Berhasil!</h1>
        <p>Struk siap dicetak atau disimpan</p>
    </div>

    <div class="struk-card">
        <div class="struk-header">
            <div class="toko-nama">🍞 TokoRoti</div>
            <div class="toko-tagline">Roti & Kue Berkualitas</div>
            <div class="struk-id"><?= htmlspecialchars($transaksi['id_transaksi']) ?></div>
        </div>

        <div class="struk-info">
            <div class="info-item">
                <div class="info-label">Waktu</div>
                <div class="info-val"><?= date('d M Y, H:i', strtotime($transaksi['waktu_transaksi'])) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Kasir</div>
                <div class="info-val"><?= htmlspecialchars($transaksi['nama_karyawan']) ?></div>
            </div>
            <div class="info-item" style="grid-column: span 2;">
                <div class="info-label">Metode Pembayaran</div>
                <div class="info-val"><?= htmlspecialchars($transaksi['metode_bayar']) ?></div>
            </div>
        </div>

        <div class="struk-items">
            <div class="item-header">
                <span>Item</span>
                <span>Qty</span>
                <span>Subtotal</span>
            </div>
            <?php foreach ($items as $item): ?>
            <div class="struk-item-row">
                <span class="item-nama"><?= htmlspecialchars($item['nama_menu']) ?></span>
                <span class="item-qty">×<?= $item['jumlah'] ?></span>
                <span class="item-sub">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="struk-summary">
            <div class="summary-row">
                <span>Sub Total</span>
                <span>Rp <?= number_format($transaksi['total_bayar'], 0, ',', '.') ?></span>
            </div>
            <?php if ($transaksi['metode_bayar'] === 'Cash'): ?>
            <div class="summary-row">
                <span>Uang Bayar</span>
                <span>Rp <?= number_format($transaksi['uang_bayar'], 0, ',', '.') ?></span>
            </div>
            <div class="summary-row">
                <span>Kembalian</span>
                <span>Rp <?= number_format($transaksi['kembalian'], 0, ',', '.') ?></span>
            </div>
            <?php endif; ?>
            <div class="summary-row total">
                <span>Total</span>
                <span>Rp <?= number_format($transaksi['total_bayar'], 0, ',', '.') ?></span>
            </div>
        </div>

        <div class="struk-footer">
            <strong>Terima kasih telah berbelanja!</strong>
            Sampai jumpa kembali 😊
        </div>
    </div>

    <div class="struk-actions no-print">
        <button onclick="window.print()" class="btn-cetak">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"/>
                <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                <rect x="6" y="14" width="12" height="8"/>
            </svg>
            Cetak Struk
        </button>
        <a href="kasir.php" class="btn-baru">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Transaksi Baru
        </a>
    </div>
</div>

<script>
    // Langsung buka dialog print jika parameter print=1 ada di URL
    if (new URLSearchParams(window.location.search).get('print') === '1') {
        window.print();
    }
</script>

</body>
</html>