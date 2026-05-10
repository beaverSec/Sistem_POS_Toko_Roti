<?php
require_once '../includes/auth.php';
requireRole('Manajer'); // Keamanan: Hanya manajer yang bisa akses inventori
require_once '../config/database.php';

// Agar sidebar tahu kita sedang di halaman inventori
$currentPage = 'inventori';

// Ambil data menu yang aktif (belum dihapus)
$query = "SELECT m.*, k.nama_kategori 
          FROM menu m 
          JOIN kategori k ON m.id_kategori = k.id_kategori 
          WHERE m.is_deleted = 0 
          ORDER BY m.id_menu DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Inventori - TokoRoti</title>
    <link rel="stylesheet" href="assets/css/style.css"> <style>
        /* Tambahan style khusus tabel agar rapi */
        .inventory-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            margin-top: 20px;
        }
        .table-custom {
            width: 100%;
            border-collapse: collapse;
        }
        .table-custom th {
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #f0f0f0;
            color: #64748b;
        }
        .table-custom td {
            padding: 16px 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        .badge-stok {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .stok-aman { background: #dcfce7; color: #15803d; }
        .stok-low { background: #fee2e2; color: #b91c1c; }
        .img-menu {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            object-fit: cover;
        }
        .btn-action {
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
        }
        .btn-edit { background: #e0f2fe; color: #0369a1; }
        .btn-hapus { background: #fee2e2; color: #b91c1c; margin-left: 5px; }
    </style>
</head>
<body>

<div style="display: flex;">
    <?php include '../includes/sidebar.php'; ?>

    <div style="flex: 1; background: #f8fafc; padding: 40px; min-height: 100vh;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 style="color: #1e293b; font-size: 24px;">Manajemen Inventori</h1>
            <a href="tambah.php" style="background: #3498db; color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: bold;">
                + Tambah Menu Baru
            </a>
        </div>

        <div class="inventory-card">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama Roti</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menus as $m): ?>
                    <tr>
                        <td>
                            <img src="assets/<?= htmlspecialchars($m['gambar'] ?? 'default.jpeg') ?>" class="img-menu">
                        </td>
                        <td><strong style="color: #334155;"><?= htmlspecialchars($m['nama_menu']) ?></strong></td>
                        <td><?= htmlspecialchars($m['nama_kategori']) ?></td>
                        <td>Rp <?= number_format($m['harga'], 0, ',', '.') ?></td>
                        <td>
                            <?php $isLow = $m['stok'] <= 10; ?>
                            <span class="badge-stok <?= $isLow ? 'stok-low' : 'stok-aman' ?>">
                                <?= $m['stok'] ?> unit <?= $isLow ? '(Low)' : '' ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit.php?id=<?= $m['id_menu'] ?>" class="btn-action btn-edit">Edit</a>
                            <a href="../process/delete.php?id=<?= $m['id_menu'] ?>" 
                               class="btn-action btn-hapus" 
                               onclick="return confirm('Yakin ingin menghapus menu ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>