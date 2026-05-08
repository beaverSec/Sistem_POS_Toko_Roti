<?php
require_once '../config/database.php';

// Validasi input
if ($_POST['stok'] < 0 || $_POST['harga'] <= 0) {
    die("Stok atau harga tidak valid.");
}

try {
    $stmt = $conn->prepare(
        "INSERT INTO menu (id_menu, nama_menu, stok, harga, id_kategori)
         VALUES (:id_menu, :nama_menu, :stok, :harga, :id_kategori)"
    );
    $stmt->execute([
        ':id_menu'     => $_POST['id_menu'],
        ':nama_menu'   => $_POST['nama_menu'],
        ':stok'        => $_POST['stok'],
        ':harga'       => $_POST['harga'],
        ':id_kategori' => $_POST['id_kategori']
    ]);
    header("Location: ../public/index.php?status=sukses");
} catch (PDOException $e) {
    echo "Gagal menambah data: " . $e->getMessage();
}