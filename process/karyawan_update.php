<?php
require_once '../includes/auth.php';
requireRole('Manajer');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../public/karyawan.php");
    exit;
}

try {
    // Kalau password diisi, update sekalian. Kalau kosong, skip password
    if (!empty($_POST['password'])) {
        $stmt = $conn->prepare(
            "UPDATE karyawan SET nama_karyawan=:nama, jabatan=:jabatan,
             username=:username, password=:password, is_active=:aktif
             WHERE id_karyawan=:id"
        );
        $params = [
            ':nama'     => trim($_POST['nama_karyawan']),
            ':jabatan'  => $_POST['jabatan'],
            ':username' => trim($_POST['username']),
            ':password' => md5($_POST['password']),
            ':aktif'    => $_POST['is_active'],
            ':id'       => $_POST['id_karyawan'],
        ];
    } else {
        $stmt = $conn->prepare(
            "UPDATE karyawan SET nama_karyawan=:nama, jabatan=:jabatan,
             username=:username, is_active=:aktif
             WHERE id_karyawan=:id"
        );
        $params = [
            ':nama'     => trim($_POST['nama_karyawan']),
            ':jabatan'  => $_POST['jabatan'],
            ':username' => trim($_POST['username']),
            ':aktif'    => $_POST['is_active'],
            ':id'       => $_POST['id_karyawan'],
        ];
    }

    $stmt->execute($params);
    header("Location: ../public/karyawan.php?pesan=update");
} catch (PDOException $e) {
    echo "Gagal update karyawan: " . $e->getMessage();
}
