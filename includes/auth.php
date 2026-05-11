<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * CEK LOGIN DASAR
 * Digunakan di setiap halaman admin/kasir agar tidak bisa diakses tanpa login.
 */
if (!isset($_SESSION['id_karyawan'])) {
    // Jika tidak ada session, tendang ke halaman login utama
    header("Location: /TokoRoti/public/index.php"); 
    exit;
}

/**
 * CEK ROLE (Fungsi yang hilang tadi)
 * Digunakan untuk membatasi akses, misal: Kasir dilarang masuk ke Edit Menu.
 */
function requireRole(string $role): void {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        // Jika role tidak sesuai, lempar ke halaman kasir
        header("Location: /TokoRoti/public/kasir.php");
        exit;
    }
}