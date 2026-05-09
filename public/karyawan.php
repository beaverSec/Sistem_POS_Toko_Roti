<?php
require_once '../includes/auth.php';
requireRole('Manajer');
require_once '../config/database.php';

$currentPage = 'karyawan';

$karyawan = $conn->query("SELECT * FROM karyawan ORDER BY jabatan DESC, nama_karyawan ASC")
                 ->fetchAll(PDO::FETCH_ASSOC);

$pesan = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Karyawan - TokoRoti</title>
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main">
        <div class="topbar">
            <h2>Manajemen Karyawan</h2>
            <a href="tambah_karyawan.php">+ Tambah Karyawan</a>
        </div>

        <?php if ($pesan === 'tambah'): ?>
            <p style="color:green;">Karyawan berhasil ditambahkan.</p>
        <?php elseif ($pesan === 'update'): ?>
            <p style="color:green;">Data karyawan berhasil diperbarui.</p>
        <?php elseif ($pesan === 'hapus'): ?>
            <p style="color:red;">Karyawan berhasil dihapus.</p>
        <?php endif; ?>

        <table border="1" cellpadding="8">
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Username</th>
                <th>Jabatan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($karyawan as $k): ?>
            <tr>
                <td><?= htmlspecialchars($k['id_karyawan']) ?></td>
                <td><?= htmlspecialchars($k['nama_karyawan']) ?></td>
                <td><?= htmlspecialchars($k['username']) ?></td>
                <td><?= htmlspecialchars($k['jabatan']) ?></td>
                <td><?= $k['is_active'] ? 'Aktif' : 'Nonaktif' ?></td>
                <td>
                    <a href="edit_karyawan.php?id=<?= $k['id_karyawan'] ?>">Edit</a>
                    <?php if ($k['id_karyawan'] !== $_SESSION['id_karyawan']): ?>
                        | <a href="../process/karyawan_delete.php?id=<?= $k['id_karyawan'] ?>"
                             onclick="return confirm('Yakin hapus karyawan ini?')">Hapus</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
</body>
</html>
