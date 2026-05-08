<?php
require_once '../config/database.php';

$stmt = $conn->query("SELECT m.id_menu, m.nama_menu, m.stok, m.harga, k.nama_kategori
                      FROM menu m
                      JOIN kategori k ON m.id_kategori = k.id_kategori");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Toko Roti - Daftar Menu</title>
</head>
<body>
    <h2>Daftar Menu</h2>

    <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] == 'sukses'): ?>
            <p style="color:green;">Data berhasil ditambahkan!</p>
        <?php elseif ($_GET['status'] == 'updated'): ?>
            <p style="color:green;">Data berhasil diupdate!</p>
        <?php elseif ($_GET['status'] == 'deleted'): ?>
            <p style="color:red;">Data berhasil dihapus!</p>
        <?php endif; ?>
    <?php endif; ?>

    <a href="tambah.php">+ Tambah Menu</a>
    <br><br>

    <table border="1" cellpadding="8">
        <tr>
            <th>ID Menu</th>
            <th>Nama Menu</th>
            <th>Kategori</th>
            <th>Stok</th>
            <th>Harga</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($data as $row): ?>
        <tr>
            <td><?= $row['id_menu']; ?></td>
            <td><?= $row['nama_menu']; ?></td>
            <td><?= $row['nama_kategori']; ?></td>
            <td><?= $row['stok']; ?></td>
            <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
            <td>
                <a href="edit.php?id=<?= $row['id_menu']; ?>">Edit</a> |
                <a href="hapus.php?id=<?= $row['id_menu']; ?>">Hapus</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>