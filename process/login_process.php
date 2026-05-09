<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit;
}

$username = trim($_POST['username']);
$password = $_POST['password'];

// Validasi tidak kosong
if (empty($username) || empty($password)) {
    $_SESSION['login_error'] = "Username dan password wajib diisi.";
    header("Location: ../login.php");
    exit;
}

try {
    // Cek username + is_active
    $stmt = $conn->prepare(
        "SELECT * FROM karyawan WHERE username = :username AND is_active = 1"
    );
    $stmt->execute([':username' => $username]);
    $karyawan = $stmt->fetch(PDO::FETCH_ASSOC);

    // Cek password (MD5)
    if ($karyawan && $karyawan['password'] === md5($password)) {
        // Set session
        $_SESSION['id_karyawan']  = $karyawan['id_karyawan'];
        $_SESSION['nama']         = $karyawan['nama_karyawan'];
        $_SESSION['role']         = $karyawan['jabatan'];

        // Redirect berdasarkan role
        if ($karyawan['jabatan'] === 'Manajer') {
            header("Location: ../public/dashboard.php");
        } else {
            header("Location: ../public/kasir.php");
        }
        exit;
    } else {
        $_SESSION['login_error'] = "Username atau password salah.";
        header("Location: ../login.php");
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['login_error'] = "Terjadi kesalahan sistem.";
    header("Location: ../login.php");
    exit;
}
