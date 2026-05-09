<?php
require_once '../includes/auth.php';
requireRole('Manajer');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../public/karyawan.php");
    exit;
}

try {
    $stmt = $conn->prepare(
        "INSERT INTO karyawan (id_karyawan, nama_karyawan, jabatan, username, password, is_active)
         VALUES (:id, :nama, :jabatan, :username, :password, 1)"
    );
    $stmt->execute([
        ':id'       => trim($_POST['id_karyawan']),
        ':nama'     => trim($_POST['nama_karyawan']),
        ':jabatan'  => $_POST['jabatan'],
        ':username' => trim($_POST['username']),
        ':password' => md5($_POST['password']),
    ]);
    header("Location: ../public/karyawan.php?pesan=tambah");
} catch (PDOException $e) {
    echo "Gagal menambah karyawan: " . $e->getMessage();
}
