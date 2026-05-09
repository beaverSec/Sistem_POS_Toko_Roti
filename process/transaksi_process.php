<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../public/kasir.php");
    exit;
}

$total_bayar  = (int) $_POST['total_bayar'];
$uang_bayar   = (int) $_POST['uang_bayar'];
$kembalian    = (int) $_POST['kembalian'];
$metode_bayar = $_POST['metode_bayar'];
$items        = json_decode($_POST['items'], true);

if (empty($items)) {
    header("Location: ../public/kasir.php?pesan=gagal");
    exit;
}

try {
    $conn->beginTransaction();

    // Generate ID transaksi otomatis
    $lastId = $conn->query("SELECT id_transaksi FROM transaksi ORDER BY id_transaksi DESC LIMIT 1")
                   ->fetchColumn();
    $nomorBaru = $lastId ? (int) substr($lastId, 2) + 1 : 1;
    $id_transaksi = 'TR' . str_pad($nomorBaru, 4, '0', STR_PAD_LEFT);

    // Simpan ke tabel transaksi
    $stmt = $conn->prepare(
        "INSERT INTO transaksi (id_transaksi, waktu_transaksi, total_bayar, uang_bayar, kembalian, metode_bayar, status, id_karyawan)
         VALUES (:id_transaksi, NOW(), :total_bayar, :uang_bayar, :kembalian, :metode_bayar, 'Selesai', :id_karyawan)"
    );
    $stmt->execute([
        ':id_transaksi' => $id_transaksi,
        ':total_bayar'  => $total_bayar,
        ':uang_bayar'   => $uang_bayar,
        ':kembalian'    => $kembalian,
        ':metode_bayar' => $metode_bayar,
        ':id_karyawan'  => $_SESSION['id_karyawan'],
    ]);

    // Simpan tiap item ke detail_transaksi
    $stmtDetail = $conn->prepare(
        "INSERT INTO detail_transaksi (id_detailTransaksi, id_menu, id_transaksi, jumlah, subtotal)
         VALUES (:id_detail, :id_menu, :id_transaksi, :jumlah, :subtotal)"
    );

    $counter = 1;
    foreach ($items as $id_menu => $item) {
        // Cek stok cukup
        $stok = $conn->prepare("SELECT stok FROM menu WHERE id_menu = :id");
        $stok->execute([':id' => $id_menu]);
        $stokSaat = (int) $stok->fetchColumn();

        if ($stokSaat < $item['qty']) {
            $conn->rollBack();
            header("Location: ../public/kasir.php?pesan=gagal");
            exit;
        }

        $id_detail = 'DT-' . $id_transaksi . '-' . str_pad($counter, 2, '0', STR_PAD_LEFT);
        $subtotal  = $item['harga'] * $item['qty'];

        $stmtDetail->execute([
            ':id_detail'    => $id_detail,
            ':id_menu'      => $id_menu,
            ':id_transaksi' => $id_transaksi,
            ':jumlah'       => $item['qty'],
            ':subtotal'     => $subtotal,
        ]);
        // Stok otomatis berkurang via TRIGGER di database

        $counter++;
    }

    $conn->commit();
    header("Location: ../public/struk.php?id=$id_transaksi");
    exit;

} catch (PDOException $e) {
    $conn->rollBack();
    header("Location: ../public/kasir.php?pesan=gagal");
    exit;
}
