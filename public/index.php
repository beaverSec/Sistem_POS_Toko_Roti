<?php
require_once '../includes/auth.php';
requireRole('Manajer'); // Keamanan agar kasir tidak bisa ubah stok
require_once '../config/database.php';

$currentPage = 'inventori'; // Agar menu 'Inventori' di sidebar menyala

// Query ini sudah mendukung fitur GAMBAR yang baru kita buat
$stmt = $conn->query("SELECT m.id_menu, m.nama_menu, m.stok, m.harga, m.gambar, k.nama_kategori
                      FROM menu m
                      JOIN kategori k ON m.id_kategori = k.id_kategori
                      WHERE m.is_deleted = 0
                      ORDER BY k.nama_kategori, m.nama_menu");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>