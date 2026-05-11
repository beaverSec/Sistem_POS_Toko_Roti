<?php
require_once '../includes/auth.php';
requireRole('Manajer');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../public/karyawan.php");
    exit;
}

// 1. Tangkap input
$id_karyawan   = trim($_POST['id_karyawan']);
$nama_karyawan = trim($_POST['nama_karyawan']);
$jabatan       = $_POST['jabatan'];
$username      = trim($_POST['username']);
$password      = $_POST['password'];

try {
    // 2. CEK USERNAME DUPLIKAT
    $stmtCek = $conn->prepare("SELECT COUNT(*) FROM karyawan WHERE username = :username");
    $stmtCek->execute([':username' => $username]);
    if ($stmtCek->fetchColumn() > 0) {
        // Jika username sudah ada, lempar balik dengan pesan error khusus
        header("Location: ../public/karyawan.php?pesan=gagal_username");
        exit;
    }

    // 3. JIKA AMAN, JALANKAN INSERT
    $stmt = $conn->prepare(
        "INSERT INTO karyawan (id_karyawan, nama_karyawan, jabatan, username, password, is_active)
         VALUES (:id, :nama, :jabatan, :username, :password, 1)"
    );
    $stmt->execute([
        ':id'       => $id_karyawan,
        ':nama'     => $nama_karyawan,
        ':jabatan'  => $jabatan,
        ':username' => $username,
        ':password' => md5($password), // Tetap gunakan md5 sesuai kode awalmu
    ]);
    header("Location: ../public/karyawan.php?pesan=tambah");

} catch (PDOException $e) {
    // Jika ada error database lain (misal ID duplikat)
    header("Location: ../public/karyawan.php?pesan=gagal");
}