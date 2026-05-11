<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../public/inventori.php");
    exit;
}

// Validasi input
if ($_POST['stok'] < 0 || $_POST['harga'] <= 0) {
    die("Stok atau harga tidak valid.");
}

// =====================
// HANDLE UPLOAD GAMBAR
// =====================
$namaGambar = null; // default: tidak ada gambar

if (!empty($_FILES['gambar']['name'])) {
    $file     = $_FILES['gambar'];
    $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed  = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ekstensi, $allowed)) {
        die("Format gambar tidak didukung. Gunakan JPG, PNG, atau WEBP.");
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        die("Ukuran gambar maksimal 2MB.");
    }

    // Buat nama file unik supaya tidak tabrakan
    $namaGambar = 'menu_' . time() . '_' . uniqid() . '.' . $ekstensi;
    $tujuan = __DIR__ . '/../public/assets/' . $namaGambar;

    if (!move_uploaded_file($file['tmp_name'], $tujuan)) {
        die("Gagal mengupload gambar. Pastikan folder assets/uploads/ ada dan bisa ditulis.");
    }
}

// =====================
// SIMPAN KE DATABASE
// =====================
try {
    $stmt = $conn->prepare(
        "INSERT INTO menu (id_menu, nama_menu, stok, harga, id_kategori, gambar, is_deleted)
         VALUES (:id_menu, :nama_menu, :stok, :harga, :id_kategori, :gambar, 0)"
    );
    $stmt->execute([
        ':id_menu'     => trim($_POST['id_menu']),
        ':nama_menu'   => trim($_POST['nama_menu']),
        ':stok'        => (int) $_POST['stok'],
        ':harga'       => (int) $_POST['harga'],
        ':id_kategori' => $_POST['id_kategori'],
        ':gambar'      => $namaGambar, // NULL kalau tidak upload
    ]);
    header("Location: ../public/inventori.php?pesan=tambah");
    exit;
} catch (PDOException $e) {
    echo "Gagal menambah data: " . $e->getMessage();
}
