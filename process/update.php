<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../public/inventori.php");
    exit;
}

if (empty($_POST['id_menu']) || empty($_POST['nama_menu']) || $_POST['harga'] <= 0) {
    die("Data tidak valid atau harga tidak boleh 0.");
}

// =====================
// HANDLE UPLOAD GAMBAR
// =====================
$namaGambar = $_POST['gambar_lama'] ?? null; // pakai gambar lama dulu sebagai default

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

    // Upload gambar baru
    $namaGambar = 'menu_' . time() . '_' . uniqid() . '.' . $ekstensi;
    $tujuan = __DIR__ . '/../public/assets/' . $namaGambar;

    if (!move_uploaded_file($file['tmp_name'], $tujuan)) {
        die("Gagal mengupload gambar. Pastikan folder assets/uploads/ ada dan bisa ditulis.");
    }

    // Hapus gambar lama dari server kalau ada
    if (!empty($_POST['gambar_lama'])) {
        $gambarLamaPath = __DIR__ . '/../public/assets/' . $_POST['gambar_lama'];
        if (file_exists($gambarLamaPath)) {
            unlink($gambarLamaPath);
        }
    }
}

// =====================
// UPDATE DATABASE
// =====================
try {
    $stmt = $conn->prepare(
        "UPDATE menu 
         SET nama_menu   = :nama,
             id_kategori = :kategori,
             stok        = :stok,
             harga       = :harga,
             gambar      = :gambar
         WHERE id_menu = :id"
    );
    $stmt->execute([
        ':nama'     => trim($_POST['nama_menu']),
        ':kategori' => $_POST['id_kategori'],
        ':stok'     => (int) $_POST['stok'],
        ':harga'    => (int) $_POST['harga'],
        ':gambar'   => $namaGambar,
        ':id'       => $_POST['id_menu'],
    ]);
    header("Location: ../public/inventori.php?pesan=edit");
    exit;
} catch (PDOException $e) {
    echo "Gagal mengupdate data: " . $e->getMessage();
}
