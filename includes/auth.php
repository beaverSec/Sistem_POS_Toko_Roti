<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
if (!isset($_SESSION['id_karyawan'])) {
    // Jika tidak ada session, lempar ke halaman login (index.php)
    // Gunakan path absolut agar tidak error saat dipanggil dari subfolder
    header("Location: /TokoRoti/public/index.php"); 
    exit;
}

function requireRole(string $role): void {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        // Jika role tidak sesuai (misal Kasir coba masuk Inventori)
        header("Location: /TokoRoti/public/kasir.php");
        exit;
    }
}