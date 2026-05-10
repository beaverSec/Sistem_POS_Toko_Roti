<?php
require_once '../config/database.php';

if (empty($_POST['id_menu']) || empty($_POST['nama_menu']) || $_POST['harga'] <= 0) {
    die("Data tidak valid atau harga tidak boleh 0.");
}

try {
    // Tambahkan kolom gambar pada query update
    $sql = "UPDATE menu 
            SET nama_menu = :nama, 
                id_kategori = :kategori, 
                stok = :stok, 
                harga = :harga,
                gambar = :gambar
            WHERE id_menu = :id";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':nama'     => $_POST['nama_menu'],
        ':kategori' => $_POST['id_kategori'],
        ':stok'     => $_POST['stok'],
        ':harga'    => $_POST['harga'],
        ':gambar'   => $_POST['gambar'],
        ':id'       => $_POST['id_menu']
    ]);

    header("Location: ../public/index.php?status=updated");
    exit();
} catch (PDOException $e) {
    echo "Gagal mengupdate data: " . $e->getMessage();
}