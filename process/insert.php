<?php
require_once '../config/database.php';

// Validasi input
if ($_POST['stok'] < 0 || $_POST['harga'] <= 0) {
    die("Stok atau harga tidak valid.");
}

try {
    // Tambahkan kolom gambar di sini
    $stmt = $conn->prepare(
        "INSERT INTO menu (id_menu, nama_menu, stok, harga, id_kategori, gambar, is_deleted)
         VALUES (:id_menu, :nama_menu, :stok, :harga, :id_kategori, :gambar, 0)"
    );
    $stmt->execute([
        ':id_menu'     => $_POST['id_menu'],
        ':nama_menu'   => $_POST['nama_menu'],
        ':stok'        => $_POST['stok'],
        ':harga'       => $_POST['harga'],
        ':id_kategori' => $_POST['id_kategori'],
        ':gambar'      => $_POST['gambar'] // Pastikan di form tambah.php input gambarnya bernama 'gambar'
    ]);
    header("Location: ../public/inventori.php?pesan=tambah");
} catch (PDOException $e) {
    echo "Gagal menambah data: " . $e->getMessage();
}