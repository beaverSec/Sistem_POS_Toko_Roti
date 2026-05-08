<?php
require_once '../config/database.php';

$id = $_GET['id'];

// Ambil data menu yang akan diedit
$stmt = $conn->prepare("SELECT * FROM menu WHERE id_menu = :id");
$stmt->execute([':id' => $id]);
$menu = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil daftar kategori untuk dropdown
$kategori = $conn->query("SELECT * FROM kategori")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Menu</title>
</head>
<body>
    <h2>Edit Menu</h2>
    <form action="../process/update.php" method="POST">
        <input type="hidden" name="id_menu" value="<?= $menu['id_menu']; ?>">
        <table>
            <tr>
                <td>ID Menu</td>
                <td><input type="text" value="<?= $menu['id_menu']; ?>" disabled></td>
            </tr>
            <tr>
                <td>Nama Menu</td>
                <td><input type="text" name="nama_menu" value="<?= $menu['nama_menu']; ?>" required></td>
            </tr>
            <tr>
                <td>Kategori</td>
                <td>
                    <select name="id_kategori" required>
                        <?php foreach ($kategori as $k): ?>
                            <option value="<?= $k['id_kategori']; ?>"
                                <?= $k['id_kategori'] == $menu['id_kategori'] ? 'selected' : ''; ?>>
                                <?= $k['nama_kategori']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Stok</td>
                <td><input type="number" name="stok" value="<?= $menu['stok']; ?>" min="0" required></td>
            </tr>
            <tr>
                <td>Harga</td>
                <td><input type="number" name="harga" value="<?= $menu['harga']; ?>" min="0" required></td>
            </tr>
        </table>
        <br>
        <button type="submit">Update</button>
        <a href="index.php">Batal</a>
    </form>
</body>
</html>