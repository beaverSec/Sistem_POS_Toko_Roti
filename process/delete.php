<?php
require_once '../includes/auth.php';
requireRole('Manajer');
require_once '../config/database.php';

$id = $_GET['id'] ?? '';
if (!$id) {
    header("Location: ../public/inventori.php");
    exit;
}

try {
    // SOFT DELETE — set is_deleted = 1, bukan DELETE sungguhan
    // Ini agar histori transaksi yang sudah ada tidak rusak
    $stmt = $conn->prepare("UPDATE menu SET is_deleted = 1 WHERE id_menu = :id");
    $stmt->execute([':id' => $id]);
    header("Location: ../public/inventori.php?pesan=hapus");
    exit;
} catch (PDOException $e) {
    echo "Gagal menghapus data: " . $e->getMessage();
}
