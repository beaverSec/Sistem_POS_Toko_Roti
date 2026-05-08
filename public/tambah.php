<?php
require_once '../config/database.php';

// Ambil daftar kategori untuk dropdown
$kategori = $conn->query("SELECT * FROM kategori")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Menu</title>
</head>
<body>
    <h2>Tambah Menu Baru</h2>
    <form action="../process/insert.php" method="POST">
        <table>
            <tr>
                <td>ID Menu</td>
                <td><input type="text" name="id_menu" required></td>
            </tr>
            <tr>
                <td>Nama Menu</td>
                <td><input type="text" name="nama_menu" required></td>
            </tr>
            <tr>
                <td>Kategori</td>
                <td>
                    <select name="id_kategori" required>
                        <?php foreach ($kategori as $k): ?>
                            <option value="<?= $k['id_kategori']; ?>">
                                <?= $k['nama_kategori']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Stok</td>
                <td><input type="number" name="stok" min="0" required></td>
            </tr>
            <tr>
                <td>Harga</td>
                <td><input type="number" name="harga" min="0" required></td>
            </tr>
        </table>
        <br>
        <button type="submit">Simpan</button>
        <a href="index.php">Batal</a>
    </form>
</body>
</html>