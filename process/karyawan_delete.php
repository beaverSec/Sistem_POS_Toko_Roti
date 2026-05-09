<?php
require_once '../includes/auth.php';
requireRole('Manajer');
require_once '../config/database.php';

$id = $_GET['id'] ?? '';

// Tidak boleh hapus diri sendiri
if ($id === $_SESSION['id_karyawan']) {
    header("Location: ../public/karyawan.php");
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM karyawan WHERE id_karyawan = :id");
    $stmt->execute([':id' => $id]);
    header("Location: ../public/karyawan.php?pesan=hapus");
} catch (PDOException $e) {
    echo "Gagal hapus karyawan: " . $e->getMessage();
}
