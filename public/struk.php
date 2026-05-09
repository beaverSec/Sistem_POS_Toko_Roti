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

// Ambil detail item dari view detailstruk
$stmtDetail = $conn->prepare("SELECT * FROM detailstruk WHERE id_transaksi = :id");
$stmtDetail->execute([':id' => $id]);
$items = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk - <?= $id ?></title>
    <style>
        .struk { max-width: 400px; margin: 20px auto; font-family: monospace; }
        .struk hr { border: 1px dashed #000; }
        .row { display: flex; justify-content: space-between; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
<div class="struk">
    <h2 style="text-align:center;">🍞 TokoRoti</h2>
    <hr>
    <p>ID Transaksi : <?= htmlspecialchars($transaksi['id_transaksi']) ?></p>
    <p>Waktu        : <?= date('d M Y H:i', strtotime($transaksi['waktu_transaksi'])) ?></p>
    <p>Kasir        : <?= htmlspecialchars($transaksi['nama_karyawan']) ?></p>
    <p>Metode       : <?= htmlspecialchars($transaksi['metode_bayar']) ?></p>
    <hr>
    <?php foreach ($items as $item): ?>
        <div class="row">
            <span><?= htmlspecialchars($item['nama_menu']) ?> x<?= $item['jumlah'] ?></span>
            <span>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
        </div>
    <?php endforeach; ?>
    <hr>
    <div class="row"><span>Total</span><strong>Rp <?= number_format($transaksi['total_bayar'], 0, ',', '.') ?></strong></div>
    <div class="row"><span>Uang Bayar</span><span>Rp <?= number_format($transaksi['uang_bayar'], 0, ',', '.') ?></span></div>
    <div class="row"><span>Kembalian</span><span>Rp <?= number_format($transaksi['kembalian'], 0, ',', '.') ?></span></div>
    <hr>
    <p style="text-align:center;">Terima kasih!</p>

    <div class="no-print" style="text-align:center; margin-top:16px;">
        <button onclick="window.print()">🖨 Cetak Struk</button>
        <a href="kasir.php"><button>Transaksi Baru</button></a>
    </div>
</div>
</body>
</html>
