<?php
require_once '../config/database.php';

// Validasi sederhana sesuai modul (Poin 4.3)
if (empty($_POST['id_menu']) || empty($_POST['nama_menu']) || $_POST['harga'] <= 0) {
    die("Data tidak valid atau harga tidak boleh 0.");
}

try {
    // 1. Siapkan Query Update menggunakan Prepared Statement (Poin 4.2)
    $sql = "UPDATE menu 
            SET nama_menu = :nama, 
                id_kategori = :kategori, 
                stok = :stok, 
                harga = :harga 
            WHERE id_menu = :id";

    $stmt = $conn->prepare($sql);

    // 2. Eksekusi dengan binding parameter agar aman dari SQL Injection
    $stmt->execute([
        ':nama'     => $_POST['nama_menu'],
        ':kategori' => $_POST['id_kategori'],
        ':stok'     => $_POST['stok'],
        ':harga'    => $_POST['harga'],
        ':id'       => $_POST['id_menu']
    ]);

    // 3. Redirect kembali ke index.php dengan status updated
    header("Location: ../public/index.php?status=updated");
    exit();

} catch (PDOException $e) {
    // Error handling sesuai instruksi modul
    echo "Gagal mengupdate data: " . $e->getMessage();
}
?>