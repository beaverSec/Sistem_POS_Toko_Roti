<?php
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kalau belum login, redirect ke login
if (!isset($_SESSION['id_karyawan'])) {
    header("Location: ../login.php");
    exit;
}

// Fungsi cek role - panggil requireRole('Manajer') di halaman khusus admin
function requireRole(string $role): void {
    if ($_SESSION['role'] !== $role) {
        header("Location: ../public/kasir.php");
        exit;
    }
}
