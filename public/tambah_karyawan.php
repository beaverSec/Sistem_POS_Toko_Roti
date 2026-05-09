<?php
require_once '../includes/auth.php';
requireRole('Manajer');
$currentPage = 'karyawan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Karyawan - TokoRoti</title>
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main">
        <h2>Tambah Karyawan Baru</h2>
        <form action="../process/karyawan_insert.php" method="POST">
            <table>
                <tr>
                    <td>ID Karyawan</td>
                    <td><input type="text" name="id_karyawan" placeholder="Contoh: K004" required></td>
                </tr>
                <tr>
                    <td>Nama Lengkap</td>
                    <td><input type="text" name="nama_karyawan" required></td>
                </tr>
                <tr>
                    <td>Jabatan</td>
                    <td>
                        <select name="jabatan">
                            <option value="Kasir">Kasir</option>
                            <option value="Manajer">Manajer</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Username</td>
                    <td><input type="text" name="username" required></td>
                </tr>
                <tr>
                    <td>Password</td>
                    <td><input type="password" name="password" required></td>
                </tr>
            </table>
            <br>
            <button type="submit">Simpan</button>
            <a href="karyawan.php">Batal</a>
        </form>
    </div>
</div>
</body>
</html>
