<?php
require_once '../config/database.php';

$id = $_GET['id'];

// Ambil nama menu untuk konfirmasi
$stmt = $conn->prepare("SELECT nama_menu FROM menu WHERE id_menu = :id");
$stmt->execute([':id' => $id]);
$menu = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hapus Menu</title>
</head>
<body>
    <h2>Hapus Menu</h2>
    <p>Yakin ingin menghapus menu <strong><?= $menu['nama_menu']; ?></strong>?</p>
    <a href="../process/delete.php?id=<?= $id; ?>">Ya, Hapus</a> |
    <a href="index.php">Batal</a>
</body>
</html>