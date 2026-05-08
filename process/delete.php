<?php
require_once '../config/database.php';

try {
    // Tangkap ID dari URL (link Hapus di index.php)
    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM menu WHERE id_menu = :id");
    $stmt->execute([':id' => $id]);

    header("Location: ../public/index.php?status=deleted");
    exit();
} catch (PDOException $e) {
    echo "Gagal menghapus data: " . $e->getMessage();
}
?>