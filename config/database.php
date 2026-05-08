<?php 
try { 
    $conn = new PDO( 
        "mysql:host=localhost;dbname=tokoroti;charset=utf8", 
        "root", 
        "" 
    ); 
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
} catch (PDOException $e) { 
    die("Koneksi gagal: " . $e->getMessage()); 
} 