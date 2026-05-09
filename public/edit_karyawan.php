<?php
require_once '../includes/auth.php';
requireRole('Manajer');
require_once '../config/database.php';

$currentPage = 'karyawan';
$id = $_GET['id'] ?? '';

if (!$id) {
    header("Location: karyawan.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM karyawan WHERE id_karyawan = :id");
$stmt->execute([':id' => $id]);
$k = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$k) {
    header("Location: karyawan.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Karyawan - TokoRoti</title>
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main">
        <h2>Edit Karyawan</h2>
        <form action="../process/karyawan_update.php" method="POST">
            <input type="hidden" name="id_karyawan" value="<?= $k['id_karyawan'] ?>">
            <table>
                <tr>
                    <td>ID Karyawan</td>
                    <td><input type="text" value="<?= htmlspecialchars($k['id_karyawan']) ?>" disabled></td>
                </tr>
                <tr>
                    <td>Nama Lengkap</td>
                    <td><input type="text" name="nama_karyawan" value="<?= htmlspecialchars($k['nama_karyawan']) ?>" required></td>
                </tr>
                <tr>
                    <td>Jabatan</td>
                    <td>
                        <select name="jabatan">
                            <option value="Kasir"   <?= $k['jabatan'] === 'Kasir'   ? 'selected' : '' ?>>Kasir</option>
                            <option value="Manajer" <?= $k['jabatan'] === 'Manajer' ? 'selected' : '' ?>>Manajer</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Username</td>
                    <td><input type="text" name="username" value="<?= htmlspecialchars($k['username']) ?>" required></td>
                </tr>
                <tr>
                    <td>Password baru</td>
                    <td>
                        <input type="password" name="password" placeholder="Kosongkan jika tidak diubah">
                    </td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>
                        <select name="is_active">
                            <option value="1" <?= $k['is_active'] ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= !$k['is_active'] ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </td>
                </tr>
            </table>
            <br>
            <button type="submit">Update</button>
            <a href="karyawan.php">Batal</a>
        </form>
    </div>
</div>
</body>
</html>
