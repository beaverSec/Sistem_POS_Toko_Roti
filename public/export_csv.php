<?php
require_once '../includes/auth.php';
requireRole('Manajer');
require_once '../config/database.php';

$dari   = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

// Ambil data sesuai filter
$query = "SELECT id_transaksi, waktu_transaksi, nama_karyawan, subtotal 
          FROM transaksilengkap 
          WHERE DATE(waktu_transaksi) BETWEEN :dari AND :sampai 
          ORDER BY waktu_transaksi DESC";
$stmt = $conn->prepare($query);
$stmt->execute([':dari' => $dari, ':sampai' => $sampai]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Header HTTP untuk download CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Laporan_TokoRoti_'.$dari.'_sd_'.$sampai.'.csv');

$output = fopen('php://output', 'w');

// Judul kolom CSV
fputcsv($output, ['ID Transaksi', 'Waktu', 'Kasir', 'Total Pembayaran']);

// Isi data
foreach ($data as $row) {
    fputcsv($output, [
        $row['id_transaksi'],
        $row['waktu_transaksi'],
        $row['nama_karyawan'],
        $row['subtotal']
    ]);
}

fclose($output);
exit();